<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CompanyController extends BaseApiController
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		$data = $request->validate([
			'ruc' => 'required|string|size:11|unique:companies,ruc',
			'razon_social' => 'required|string|max:255',
			'nombre_comercial' => 'required|string|max:255',

			'logo' => 'nullable|image',
			'sol_user' => 'required|string',
			'sol_pass' => 'required|string',
			'cert' => 'required|file|mimes:pem,txt',
			'client_id' => 'nullable|integer',
			'client_secret' => 'nullable|string',

			'address' => 'required|array',
			'address.ubigueo' => 'nullable|string|max:6',
			'address.departamento' => 'nullable|string|max:100',
			'address.provincia' => 'nullable|string|max:100',
			'address.distrito' => 'nullable|string|max:100',
			'address.urbanizacion' => 'nullable|string|max:100',
			'address.direccion' => 'nullable|string|max:255',
			'address.cod_local' => 'nullable|string|max:4',
		]);

		$companyData = Arr::except($data, ['address']);
		$companyData['user_id'] = $request->user()->id;

		$certPath = $request->file('cert')->store("companies/{$companyData['ruc']}/certs/pem", 'private');
		$companyData['cert_path'] = $certPath;

		if ($request->hasFile('logo')) {
			$logoPath = $request->file('logo')->store("companies/{$companyData['ruc']}/images/logo", 'public');
			$companyData['logo_path'] = $logoPath;
		}

		$company = Company::create($companyData);

		$addressData = $data['address'];
		$company->address()->create($addressData);

		return $this->created(['company' => $company], 'Empresa registrada correctamente');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Company $company)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Company $company)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Company $company)
	{
		//
	}
}
