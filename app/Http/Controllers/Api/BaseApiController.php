<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
	protected function success(array $data = [], string $message = 'Operación exitosa', int $status = 200)
	{
		return response()->json([
			'status' => 'success',
			'message' => $message,
			'data' => $data
		], $status);
	}

	protected function created(array $data = [], string $message = 'Recurso creado correctamente')
	{
		return $this->success($data, $message, 201);
	}

	protected function noContent()
	{
		return response()->noContent();
	}

	protected function error(string $message = 'Error inesperado', array $errors = [], int $status = 400)
	{
		return response()->json([
			'status' => 'error',
			'message' => $message,
			'errors' => $errors
		], $status);
	}

	protected function notFound(string $message = 'Recurso no encontrado')
	{
		return $this->error($message, [], 404);
	}

	protected function unauthorized(string $message = 'No autorizado')
	{
		return $this->error($message, [], 401);
	}

	protected function forbidden(string $message = 'Acceso prohibido')
	{
		return $this->error($message, [], 403);
	}

	protected function validationError(array $errors, string $message = 'Datos inválidos')
	{
		return $this->error($message, $errors, 422);
	}
}
