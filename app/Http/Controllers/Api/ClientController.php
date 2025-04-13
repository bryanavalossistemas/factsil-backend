<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends BaseApiController
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$company = $request->user()->company;

		$query = Client::where('company_id', $company->id);

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
			'tipo_doc' => 'required|string|in:1,6,4',
			'num_doc' => [
				'required',
				'string',
				'max:15',
				Rule::unique('clients', 'num_doc')->where(
					fn($query) =>
					$query->where('company_id', $company->id)
				),
			],
			'rzn_social' => 'required|string|max:255',
		]);

		$client = $company->clients()->create($data)->fresh();

		return response()->json($client, 201);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Client $client)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Client $client)
	{
		$company = $request->user()->company;

		$data = $request->validate([
			'tipo_doc' => 'required|string|in:1,6,4',
			'num_doc' => [
				'required',
				'string',
				'max:15',
				Rule::unique('clients', 'num_doc')
					->where(fn($query) => $query->where('company_id', $company->id))
					->ignore($client->id), // 👈 Excluye al cliente actual
			],
			'rzn_social' => 'required|string|max:255',
		]);

		$client->update($data);

		$client = $client->refresh();

		return response()->json($client, 201);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Client $client, Request $request)
	{
		$company = $request->user()->company;

		if ($client->company_id !== $company->id) {
			return $this->error('No tienes permiso para eliminar este cliente', [], 403);
		}

		$client->delete();

		return $this->success([], 'Cliente eliminado correctamente');
	}
}
