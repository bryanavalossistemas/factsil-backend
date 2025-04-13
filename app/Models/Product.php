<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
	protected $fillable = [
		'company_id',
		'cod_producto',
		'name',
		'unidad',
		'tip_afe_igv',
		'mto_precio_unitario',
		'image_path',
		'category_id',
	];

	public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function category()
	{
		return $this->belongsTo(Category::class);
	}
}
