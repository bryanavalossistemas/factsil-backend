<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		$user = User::create([
			'email' => 'bryanavalossistemas@gmail.com',
			'name' => 'Bryan Avalos',
			'password' => bcrypt('12345678')
		]);

		$company = $user->company()->create([
			'ruc' => '20600007522',
			'razon_social' => 'Representaciones Nataly S.A.C',
			'nombre_comercial' => 'Representaciones Nataly',
			'logo_path' => 'companies/20600007522/images/logo/3Pf2qs4OTcMZGNJs80MGExD6m4xnGGgYbMfvyJ1Y.png',
			'cert_path' => 'companies/20600007522/certs/pem/m1ERyzfjGOl84Xc31tBFi9nHkYBbwX38UDwDV9DP.txt',
			'sol_user' => 'MODDATOS',
			'sol_pass' => 'MODDATOS',
		]);

		$company->address()->create([
			'direccion' => 'Av. Los Angeles 123',
			'departamento' => 'Lima',
			'provincia' => 'Lima',
			'distrito' => 'San Juan de Lurigancho',
			'ubigueo' => '150101',
			'urbanizacion' => 'Urbanizacion Los Angeles',
			"cod_local" => "0000",
		]);

		$company->clients()->createMany([
			[
				'tipo_doc' => '6',
				'num_doc' => '20567891234',
				'rzn_social' => 'Inversiones Andina E.I.R.L.',
			],
			[
				'tipo_doc' => '6',
				'num_doc' => '20458976321',
				'rzn_social' => 'Soluciones Integrales del Perú S.A.C.',
			],
			[
				'tipo_doc' => '6',
				'num_doc' => '20123456789',
				'rzn_social' => 'Comercializadora El Sol S.A.',
			],
			[
				'tipo_doc' => '6',
				'num_doc' => '20345678901',
				'rzn_social' => 'Importaciones Lima Norte E.I.R.L.',
			],
			[
				'tipo_doc' => '1', // DNI
				'num_doc' => '45896321',
				'rzn_social' => 'Juan Carlos Pérez Flores',
			],
			[
				'tipo_doc' => '1',
				'num_doc' => '76543210',
				'rzn_social' => 'Ana María Rodríguez Luna',
			],
			[
				'tipo_doc' => '1',
				'num_doc' => '84936257',
				'rzn_social' => 'Carlos Daniel Muñoz Ríos',
			],
			[
				'tipo_doc' => '6',
				'num_doc' => '20987654321',
				'rzn_social' => 'Distribuidora Santa Rosa S.A.C.',
			],
		]);

		$company->products()->create([
			'name' => 'Harina Anita x 50 Kg',
			'mto_precio_unitario' => 165.50,
			'cod_producto' => 'HAR-1',
			'unidad' => 'KGM',
			'image_path' => 'companies/20600007522/images/products/dve3VCLX1NMaKA60XzGCnasaFqGhzO4jkfC7jFhZ.webp'
		]);

		$company->products()->create([
			'name' => 'Arroz Faraon x 50 Kg',
			'mto_precio_unitario' => 215.50,
			'cod_producto' => 'ARR-1',
			'unidad' => 'KGM',
			'image_path' => 'companies/20600007522/images/products/y72GtKGzMQt6kr1vle1Z1Uhf5hgLhjQRMwDW9nRb.webp'
		]);

		$company->products()->create([
			'name' => 'Pecana x 50 Gramos',
			'mto_precio_unitario' => 24.50,
			'cod_producto' => 'FRU-1',
			'unidad' => 'GRM',
			'image_path' => 'companies/20600007522/images/products/NvTYZSID71LweruVc4Zx9YcUkPugWncckrrM9mPO.webp'
		]);

		$token = $user->createToken('auth_token')->plainTextToken;

		echo "\n🔐 Token: $token\n";
	}
}
