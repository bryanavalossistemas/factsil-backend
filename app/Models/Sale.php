<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
	protected $fillable = [
		'serie',
		'tipo_doc',
		'tipo_operacion',
		'serie',
		'correlativo',
		'fecha_emision',
		'tipo_moneda',
		'mto_oper_gravadas',
		'mto_oper_exoneradas',
		'mto_igv',
		'total_impuestos',
		'valor_venta',
		'sub_total',
		'mto_imp_venta',
		'estado_sunat',
		'xml_path',
		'cdr_path',
		'hash_cpe',
		'company_id',
		'client_id',
	];

	public function saleDetails()
	{
		return $this->hasMany(SaleDetail::class);
	}

	public function client()
	{
		return $this->belongsTo(Client::class);
	}

	public function company()
	{
		return $this->belongsTo(Company::class);
	}

	public function legends()
	{
		return $this->hasMany(Legend::class);
	}
}
