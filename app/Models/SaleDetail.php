<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
	protected $fillable = [
		'tip_afe_igv',
		'cod_producto',
		'unidad',
		'descripcion',
		'cantidad',
		'mto_valor_unitario',
		'mto_valor_venta',
		'mto_base_igv',
		'porcentaje_igv',
		'igv',
		'total_impuestos',
		'mto_precio_unitario',
		'sale_id',
		'product_id',
	];

	public function sale()
	{
		return $this->belongsTo(Sale::class);
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}
}
