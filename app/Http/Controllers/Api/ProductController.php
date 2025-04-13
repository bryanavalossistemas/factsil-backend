<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends BaseApiController
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$company = $request->user()->company;

		$query = Product::where('company_id', $company->id);

		if ($request->filled('from') && $request->filled('to')) {
			$query->whereBetween('created_at', [
				$request->input('from'),
				$request->input('to'),
			]);
		}

		$products = $query->get();

		return response()->json($products);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		$company = $request->user()->company;

		$data = $request->validate([
			'name' => 'required|string',
			'mto_precio_unitario' => 'required|numeric|min:0',
			'cod_producto' => 'string',
			'unidad' => 'nullable|string|min:2|max:3',
			'tip_afe_igv' => 'nullable|string|size:2',
			'category_id' => [
				'string',
				Rule::when($request->category_id !== 'null', [
					Rule::exists('categories', 'id')->where(
						fn($q) => $q->where('company_id', $company->id)
					),
				]),
			],
			'image' => 'nullable|image',
		]);

		$data['company_id'] = $company->id;

		if ($request->hasFile('image')) {
			$imagePath = $request->file('image')->store("companies/{$company->ruc}/images/products", 'public');
			$data['image_path'] = $imagePath;
		}

		if ($data['cod_producto'] === 'null') {
			$data['cod_producto'] = null;
		}

		if ($data['category_id'] === 'null') {
			$data['category_id'] = null;
		}

		$product = $company->products()->create($data)->fresh();

		return response()->json($product, 201);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Product $product)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Product $product)
	{
		$company = $request->user()->company;

		if ($product->company_id !== $company->id) {
			return $this->error('No tienes permiso para actualizar este producto', [], 403);
		}

		$data = $request->validate([
			'name' => 'required|string',
			'mto_precio_unitario' => 'required|numeric|min:0',
			'cod_producto' => 'string',
			'unidad' => 'string|size:3',
			'tip_afe_igv' => 'string|size:2',
			'category_id' => [
				'string',
				Rule::when($request->category_id !== 'null', [
					Rule::exists('categories', 'id')->where(
						fn($q) => $q->where('company_id', $company->id)
					),
				]),
			],
			'image' => 'nullable|image',
			'image_path' => 'string',
		]);

		$data['company_id'] = $company->id;

		if ($request->hasFile('image')) {
			if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
				Storage::disk('public')->delete($product->image_path);
			}

			$imagePath = $request->file('image')->store("companies/{$company->ruc}/images/products", 'public');
			$data['image_path'] = $imagePath;
		} else {
			if ($data['image_path'] === 'null') {
				if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
					Storage::disk('public')->delete($product->image_path);
				}
				$data['image_path'] = null;
			}
		}

		if ($data['cod_producto'] === 'null') {
			$data['cod_producto'] = null;
		}

		if ($data['category_id'] === 'null') {
			$data['category_id'] = null;
		}

		$product->update($data);

		$product = $product->refresh();

		return response()->json($product, 201);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Product $product, Request $request)
	{
		$company = $request->user()->company;

		if ($product->company_id !== $company->id) {
			return $this->error('No tienes permiso para eliminar este producto', [], 403);
		}

		if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
			Storage::disk('public')->delete($product->image_path);
		}

		$product->delete();

		return $this->success([], 'Producto eliminado correctamente');
	}
}
