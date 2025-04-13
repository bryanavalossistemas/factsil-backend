<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('sale_details', function (Blueprint $table) {
			$table->id();

			$table->string('tip_afe_igv', 2)->default('10'); // Gravado Op. Onerosa, Exonerado Op. Onerosa - Catalog. 07
			$table->string('cod_producto')->nullable(); // Código del producto - Catalog. 11
			$table->string('unidad', 3)->default('NIU'); // Unidad - Catalog. 03
			$table->string('descripcion'); // Descripción del producto
			$table->decimal('mto_valor_unitario', 10, 2); // Valor unitario del producto sin impuestos
			$table->integer('cantidad'); // Cantidad de unidades del producto
			$table->decimal('mto_valor_venta', 10, 2);  // Monto total de la venta sin impuestos (mto_valor_unitario * cantidad)
			$table->decimal('mto_base_igv', 10, 2); // Monto base del IGV (mto_valor_venta)
			$table->decimal('porcentaje_igv', 10, 2)->default(config('impuestos.igv')); // Porcentaje del IGV (18.00)
			$table->decimal('igv', 10, 2); // Monto del IGV (mto_base_igv * (porcentaje_igv / 100))
			$table->decimal('total_impuestos', 10, 2); // Monto total de impuestos (igv + otros impuestos)
			$table->decimal('mto_precio_unitario', 10, 2); // Precio unitario del producto con impuestos

			$table->foreignId('sale_id')->constrained()->onDelete('cascade');
			$table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('sale_details');
	}
};
