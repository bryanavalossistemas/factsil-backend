<?php

namespace App\Services;

use DateTime;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Report\HtmlReport;
use Greenter\Report\Resolver\DefaultTemplateResolver;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Storage;

class SunatService
{
	public function getSee($company)
	{
		$see = new See();
		$see->setCertificate(Storage::get($company->cert_path));
		$see->setService($company->production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
		$see->setClaveSOL($company->ruc, $company->sol_user, $company->sol_pass);

		return $see;
	}

	public function getInvoice($sale)
	{
		// Venta
		return (new Invoice())
			// Cabecera 
			->setUblVersion($sale['ublVersion'] ?? '2.1') // UBL 2.1 - Version UBL
			->setTipoDoc($sale['tipo_doc'] ?? null) // Factura (01), Boleta (03) - Catalog. 01 
			->setTipoOperacion($sale['tipo_operacion'] ?? null) // Venta interna - Catalog. 51
			->setSerie($sale['serie'] ?? null) // F001 - Serie del comprobante
			->setCorrelativo($sale['correlativo'] ?? null) // Numero de factura autoincremental, No debe repetirse
			->setFechaEmision(new DateTime($sale['created_at'] ?? null)) // Zona horaria: Lima, etc
			->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
			->setTipoMoneda($sale['tipo_moneda'] ?? null) // PEN, USD, etc - Catalog. 02
			->setCompany($this->getCompany($sale['company'])) // Datos de la empresa
			->setClient($this->getClient($sale['client'])) // Datos del cliente

			// Mto Oper
			->setMtoOperGravadas($sale['mto_oper_gravadas']) // Monto total gravado, sin Impuestos
			->setMtoOperExoneradas($sale['mto_oper_exoneradas'])

			// Impuestos
			->setMtoIGV($sale['mto_igv']) // Monto total IGV // Impuesto
			->setTotalImpuestos($sale['total_impuestos']) // Suma de impuestos

			// Totales
			->setValorVenta($sale['valor_venta']) // Monto total venta, sin impuestos
			->setSubTotal($sale['sub_total']) // Monto total venta, con impuestos
			->setMtoImpVenta($sale['mto_imp_venta']) // Monto total venta, con impuestos, y que estas cobrando al cliente

			// Productos
			->setDetails($this->getDetails($sale['saleDetails'])) // Detalles de la venta

			// Leyendas
			->setLegends($this->getLegends($sale['legends'])); // Leyendas de la venta
	}

	public function getCompany($company)
	{
		return (new Company())
			->setRuc($company['ruc'] ?? null) // RUC de la compañia
			->setRazonSocial($company['razon_social'] ?? null) // Razon social de la compañia
			->setNombreComercial($company['nombre_comercial'] ?? null) // Nombre comercial de la compañia
			->setAddress($this->getAddress($company['address'])); // Datos de la direccion de la compania
	}

	public function getClient($client)
	{
		return (new Client())
			->setTipoDoc($client['tipo_doc'] ?? null) //Tipo de documento de identidad: RUC, DNI, Carnet de extranjeria - Catalog. 06
			->setNumDoc($client['num_doc'] ?? null) // Numero de documento del cliente
			->setRznSocial($client['rzn_social'] ?? null); // Razón social o nombre del cliente;
	}

	public function getAddress($address)
	{
		return (new Address())
			->setUbigueo($address['ubigueo'] ?? null) // Codigo de distrito de la compañia
			->setDepartamento($address['departamento'] ?? null) // Departamento de la compañia
			->setProvincia($address['provincia'] ?? null) // Provincia de la compañia
			->setDistrito($address['distrito'] ?? null) // Distrito de la compañia
			->setUrbanizacion($address['urbanizacion'] ?? null) // Urbanización de la compañia
			->setDireccion($address['direccion'] ?? null) // Dirección de la compañia
			->setCodLocal($address['cod_local'] ?? null); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.
	}

	public function getDetails($details)
	{
		$green_details = [];

		foreach ($details as $detail) {
			$green_details[] = (new SaleDetail())
				->setTipAfeIgv($detail['tip_afe_igv'] ?? null) // Gravado Op. Onerosa, Exonerado Op. Onerosa - Catalog. 07
				->setCodProducto($detail['cod_producto'] ?? null) // Código del producto o servicio - Catalog. 11
				->setUnidad($detail['unidad'] ?? null) // Unidad - Catalog. 03
				->setDescripcion($detail['descripcion'] ?? null) // Descripción del producto o servicio
				->setMtoValorUnitario($detail['mto_valor_unitario'] ?? null) // Valor del producto sin impuestos
				->setCantidad($detail['cantidad'] ?? null)
				->setMtoValorVenta($detail['mto_valor_venta'] ?? null) // Monto total de la venta sin impuestos
				->setMtoBaseIgv($detail['mto_base_igv'] ?? null) // Monto base para el cálculo del IGV
				->setPorcentajeIgv($detail['porcentaje_igv'] ?? null) // Porcentaje del IGV (18% o 10% para restaurantes) 
				->setIgv($detail['igv'] ?? null) // Monto del IGV
				->setTotalImpuestos($detail['total_impuestos'] ?? null) // Suma de impuestos en el detalle, puede haber impuest a la bolsa platica
				->setMtoPrecioUnitario($detail['mto_precio_unitario'] ?? null); // Precio unitario de venta, con impuestos
		}

		return $green_details;
	}

	public function getLegends($legends)
	{
		$green_legends = [];

		foreach ($legends as $legend) {
			$green_legends[] = (new Legend())
				->setCode($legend['code'] ?? null) // Monto en letras - Catalog. 52
				->setValue($legend['value'] ?? null); // Valor de la leyenda
		}

		return $green_legends;
	}

	public function sunatResponse($result)
	{
		$response['success'] = $result->isSuccess();

		if (!$response['success']) {
			$response['error'] = [
				'code' => $result->getError()->getCode(),
				'message' => $result->getError()->getMessage(),
			];

			return $response;
		}

		$cdr = $result->getCdrResponse();

		$response['cdrResponse'] = [
			'code' => (int)$cdr->getCode(),
			'description' => $cdr->getDescription(),
			'notes' => $cdr->getNotes(),
		];

		return $response;
	}

	public function getHtmlReport($invoice, $company, $hash)
	{
		$report = new HtmlReport();

		$resolver = new DefaultTemplateResolver();
		$report->setTemplate($resolver->getTemplate($invoice));

		$params = [
			'system' => [
				'logo' => Storage::disk('public')->get($company->logo_path), // Logo de Empresa
				'hash' => $hash, // Valor Resumen 
			],
			'user' => [
				'header'     => 'Telf: <b>(01) 123375</b>', // Texto que se ubica debajo de la dirección de empresa
				'extras'     => [
					// Leyendas adicionales
					['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'],
					['name' => 'VENDEDOR', 'value' => 'GITHUB SELLER'],
				],
				'footer' => '<p>Nro Resolucion: <b>3232323</b></p>'
			]
		];

		return $report->render($invoice, $params);
	}
}
