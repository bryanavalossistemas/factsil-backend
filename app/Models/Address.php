<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
	protected $fillable = [
		'company_id',
		'ubigueo',
		'departamento',
		'provincia',
		'distrito',
		'urbanizacion',
		'direccion',
		'cod_local',
	];

	public function company()
	{
		return $this->belongsTo(Company::class);
	}
}
