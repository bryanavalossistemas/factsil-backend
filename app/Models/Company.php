<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
	protected $fillable = [
		'ruc',
		'razon_social',
		'nombre_comercial',
		'serie_base',
		'logo_path',
		'cert_path',
		'sol_user',
		'sol_pass',
		'client_id',
		'client_secret',
		'user_id',
	];

	public function address()
	{
		return $this->hasOne(Address::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function clients()
	{
		return $this->hasMany(Client::class);
	}

	public function products()
	{
		return $this->hasMany(Product::class);
	}

	public function categories()
	{
		return $this->hasMany(Category::class);
	}

	public function sales()
	{
		return $this->hasMany(Sale::class);
	}
}
