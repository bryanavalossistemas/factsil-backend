<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
	protected $fillable = [
		'tipo_doc',
		'num_doc',
		'rzn_social',
	];

	public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function sales()
	{
		return $this->hasMany(Sale::class);
	}
}
