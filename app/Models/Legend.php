<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legend extends Model
{
	protected $fillable = [
		'code',
		'value',
	];

	public function sale()
	{
		return $this->belongsTo(Sale::class);
	}
}
