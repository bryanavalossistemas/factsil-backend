<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends BaseApiController
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$company = $request->user()->company;

		$query = Category::where('company_id', $company->id);

		if ($request->filled('from') && $request->filled('to')) {
			$query->whereBetween('created_at', [
				$request->input('from'),
				$request->input('to'),
			]);
		}

		$categories = $query->get();

		return response()->json($categories);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		$company = $request->user()->company;

		$data = $request->validate([
			'name' => 'required|string',
			'image' => 'nullable|image',
		]);

		$data['company_id'] = $company->id;

		if ($request->hasFile('image')) {
			$imagePath = $request->file('image')->store("companies/{$company->ruc}/images/categories", 'public');
			$data['image_path'] = $imagePath;
		}

		$category = $company->categories()->create($data)->fresh();

		return response()->json($category, 201);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Category $category)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Category $category)
	{
		$company = $request->user()->company;

		if ($category->company_id !== $company->id) {
			return $this->error('No tienes permiso para actualizar esta categoría', [], 403);
		}

		$data = $request->validate([
			'name' => 'required|string',
			'image' => 'nullable|image',
			'image_path' => 'string',
		]);

		if ($request->hasFile('image')) {
			if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
				Storage::disk('public')->delete($category->image_path);
			}

			$imagePath = $request->file('image')->store("companies/{$company->ruc}/images/categories", 'public');
			$data['image_path'] = $imagePath;
		} else {

			if ($data['image_path'] === 'null') {
				if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
					Storage::disk('public')->delete($category->image_path);
				}
				$data['image_path'] = null;
			}
		}

		$category->update($data);

		$category = $category->refresh();

		return response()->json($category, 200);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Category $category, Request $request)
	{
		$company = $request->user()->company;

		if ($category->company_id !== $company->id) {
			return $this->error('No tienes permiso para eliminar esta categoría', [], 403);
		}

		if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
			Storage::disk('public')->delete($category->image_path);
		}

		$category->delete();

		return $this->success([], 'Categoría eliminada correctamente');
	}
}
