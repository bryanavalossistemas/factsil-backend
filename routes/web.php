<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
	return view('welcome');
});

Route::get('prueba', function () {
	return Storage::disk('public')->get("logo/loguito.png");
});
