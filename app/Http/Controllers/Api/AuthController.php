<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
	public function register(Request $request)
	{
		$data = $request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|string|min:6|confirmed',
		]);

		$data['password'] = Hash::make($data['password']);

		$user = User::create($data);

		$token = $user->createToken('auth_token')->plainTextToken;

		return $this->created([
			'user' => $user,
			'token' => $token,
			'token_type' => 'Bearer',
		], 'Usuario registrado correctamente');
	}

	public function login(Request $request)
	{
		$credentials = $request->validate([
			'email' => ['required', 'email'],
			'password' => ['required'],
		]);

		$user = User::where('email', $credentials['email'])->first();

		if (!$user || !Hash::check($credentials['password'], $user->password)) {
			return $this->unauthorized('Credenciales incorrectas');
		}

		$token = $user->createToken('auth_token')->plainTextToken;

		return $this->success([
			'user' => $user,
			'token' => $token,
			'token_type' => 'Bearer',
		], 'Inicio de sesión exitoso');
	}

	public function logout(Request $request)
	{
		$request->user()->currentAccessToken()->delete();

		return $this->success([], 'Sesión cerrada correctamente');
	}

	public function me(Request $request)
	{
		return $this->success([
			'user' => $request->user()
		], 'Usuario autenticado');
	}
}
