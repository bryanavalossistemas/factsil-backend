<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Sale;
use App\Services\SunatService;
use Greenter\Model\Response\BillResult;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Luecano\NumeroALetras\NumeroALetras;

class SaleController extends BaseApiController
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$company = $request->user()->company;

		$query = Sale::with(['client', 'saleDetails.product', 'company.address']) // relaciones
			->where('company_id', $company->id);

		if ($request->filled('from') && $request->filled('to')) {
			$query->whereBetween('created_at', [
				$request->input('from'),
				$request->input('to'),
			]);
		}

		if (request(('filters'))) {
			$filters = request('filters');

			foreach ($filters as $field => $conditions) {
				foreach ($conditions as $operator => $value) {
					$query->where($field, $operator, $value);
				}
			}
		}

		$sales = $query->get();

		return response()->json($sales);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		$company = $request->user()->company;

		$data = $request->validate([
			'tipo_doc' => 'required|string|in:01,03,07,08',
			'client_id' => [
				'required',
				'integer',
				Rule::exists('clients', 'id')->where(
					fn($q) =>
					$q->where('company_id', $company->id)
				),
			],
			'sale_details' => 'required|array|min:1',
			'sale_details.*.product_id' => [
				'required',
				'integer',
				Rule::exists('products', 'id')->where(
					fn($q) =>
					$q->where('company_id', $company->id)
				),
			],
			'sale_details.*.descripcion' => 'required|string|max:255',
			'sale_details.*.cantidad' => 'required|integer|min:1',
			'sale_details.*.mto_precio_unitario' => 'required|numeric|min:0',
		]);

		return DB::transaction(function () use ($data, $company) {
			$data['company_id'] = $company->id;

			$data['tipo_operacion'] = '0101';

			$tipo_doc = $data['tipo_doc'];

			$serieNum = str_pad($company->serie_base, 3, '0', STR_PAD_LEFT);

			if ($tipo_doc === '01') {
				$data['serie'] = 'F' . $serieNum;
			} else if ($tipo_doc === '03') {
				$data['serie'] = 'B' . $serieNum;
			}

			$lastCorrelativo = Sale::where('company_id', $company->id)
				->where('serie', $data['serie'])
				->where('tipo_doc', $tipo_doc)
				->max('correlativo');

			$data['correlativo'] = $lastCorrelativo ? $lastCorrelativo + 1 : 1;

			$this->setTotales($data);
			$this->setLegends($data);

			$sale = $company->sales()->create($data);
			foreach ($data['sale_details'] as $detail) {
				$sale->saleDetails()->create($detail);
			}

			foreach ($data['legends'] as $legend) {
				$sale->legends()->create($legend);
			}

			$sale = Sale::with(['saleDetails', 'client', 'company.address', 'legends'])->find($sale->id);

			$sunat = new SunatService();

			$see = $sunat->getSee($company);

			$invoice = $sunat->getInvoice($sale);

			$xmlFirmado = $see->getXmlSigned($invoice);

			$hash = (new XmlUtils())->getHashSign($xmlFirmado);

			$xmlFilename = "{$sale->serie}-{$sale->correlativo}.xml";
			$xmlPath = "companies/{$company->ruc}/xmls/{$xmlFilename}";
			Storage::put($xmlPath, $xmlFirmado);

			$sale->update([
				'xml_path' => $xmlPath,
				'hash_cpe' => $hash
			]);

			return response()->json($sale, 201);
		});
	}

	public function sendSunat(Sale $sale, Request $request)
	{
		$company = $request->user()->company;

		if ($sale->company_id !== $company->id) {
			return $this->error('No tiene permiso para enviar esta venta a sunat', [], 403);
		}

		// Cargar XML firmado
		if (!$sale->xml_path || !Storage::exists($sale->xml_path)) {
			return $this->error('No se encontró el XML firmado de esta venta', [], 404);
		}

		$xml = Storage::get($sale->xml_path);

		$sunat = new SunatService();

		$see = $sunat->getSee($company);

		$result = $see->sendXmlFile($xml);

		if ($result->isSuccess()) {
			$serie = $sale->serie;
			$ruc = $company->ruc;
			$cdrFilename = "{$serie}-{$sale->correlativo}.zip";
			$cdrPath = "companies/{$ruc}/cdr/{$cdrFilename}";

			$cdrZip = ($result instanceof BillResult) ? $result->getCdrZip() : null;
			Storage::put($cdrPath, $cdrZip);

			$sale->update([
				'estado_sunat' => 'aceptado',
				'cdr_path' => $cdrPath,
			]);
		} else {
			$sale->update([
				'estado_sunat' => 'rechazado',
			]);
		}

		return response()->json($sunat->sunatResponse($result), 200);
	}

	public function setTotales(&$data)
	{
		$details = collect($data['sale_details']);

		$productIds = $details->pluck('product_id');
		$products = Product::whereIn('id', $productIds)->get()->keyBy('id');

		$newDetails = [];
		foreach ($details as $detail) {
			$product = $products[$detail['product_id']];

			$tip_afe_igv = $product->tip_afe_igv;
			$cod_producto = $product->cod_producto;
			$unidad = $product->unidad;
			$descripcion = $detail['descripcion'];

			$mto_valor_unitario = $detail['mto_precio_unitario'] / (1 + (config('impuestos.igv') / 100));
			$cantidad = $detail['cantidad'];
			$mto_valor_venta = $mto_valor_unitario * $cantidad;
			$mto_base_igv = $mto_valor_venta;
			$porcentaje_igv = config('impuestos.igv');
			$igv = $mto_base_igv * ($porcentaje_igv / 100);
			$total_impuestos = $igv;
			$mto_precio_unitario = $detail['mto_precio_unitario'];

			$product_id = $product->id;

			$newDetails[] = [
				'tip_afe_igv' => $tip_afe_igv,
				'cod_producto' => $cod_producto,
				'unidad' => $unidad,
				'descripcion' => $descripcion,

				'mto_valor_unitario' => $mto_valor_unitario,
				'cantidad' => $cantidad,
				'mto_valor_venta' => $mto_valor_venta,
				'mto_base_igv' => $mto_base_igv,
				'porcentaje_igv' => $porcentaje_igv,
				'igv' => $igv,
				'total_impuestos' => $total_impuestos,
				'mto_precio_unitario' => $mto_precio_unitario,

				'product_id' => $product_id,
			];
		}

		$data['sale_details'] = $newDetails;

		$details = collect($data['sale_details']);

		// CALCULANDO LOS TOTALES
		$data['mto_oper_gravadas'] = $details->where('tip_afe_igv', '10')->sum('mto_valor_venta');
		$data['mto_oper_exoneradas'] = $details->where('tip_afe_igv', '20')->sum('mto_valor_venta');

		$data['mto_igv'] = $details->whereIn('tip_afe_igv', ['10', '20', '30', '40'])->sum('igv');
		$data['total_impuestos'] = $data['mto_igv'];

		$data['valor_venta'] = $data['mto_oper_gravadas'] + $data['mto_oper_exoneradas'];
		$data['sub_total'] = $data['valor_venta'] + $data['total_impuestos'];

		$data['mto_imp_venta'] = $data['sub_total'];
	}

	public function setLegends(&$data)
	{
		$formatter = new NumeroALetras();

		$data['legends'] = [
			[
				'code' => '1000',
				'value' => 'SON ' . $formatter->toInvoice($data['mto_imp_venta'], 2, 'SOLES'),
			]
		];
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Sale $sale)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Sale $sale)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Sale $sale)
	{
		//
	}
}
