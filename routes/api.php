<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
	Route::post('/register', [AuthController::class, 'register']);
	Route::post('/login', [AuthController::class, 'login']);
	Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
	Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
	Route::apiResource('companies', CompanyController::class);
});

Route::middleware('auth:sanctum')->group(function () {
	Route::apiResource('clients', ClientController::class);
});

Route::middleware('auth:sanctum')->group(function () {
	Route::apiResource('categories', CategoryController::class);
});

Route::middleware('auth:sanctum')->group(function () {
	Route::apiResource('products', ProductController::class);
});

Route::middleware('auth:sanctum')->group(function () {
	Route::apiResource('sales', SaleController::class);

	Route::post('sales/{sale}/send-sunat', [SaleController::class, 'sendSunat'])
		->name('sales.sendSunat');
});
