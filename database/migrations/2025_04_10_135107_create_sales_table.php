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
		Schema::create('sales', function (Blueprint $table) {
			$table->id();

			// Cabecera de la venta
			$table->char('tipo_doc', 2); // Factura (01), Boleta (03), Nota de Credito (07), Nota de Debito (08) - Catalog. 01
			$table->char('tipo_operacion', 4)->default('0101'); // Venta Interna - Catalog. 51
			$table->string('serie', 4); // Serie del comprobante (F001, B001, etc)
			$table->bigInteger('correlativo'); // Correlativo del comprobante (autoincremental, no debe repetirse)
			$table->string('tipo_moneda', 3)->default('PEN'); // Tipo de moneda (PEN, USD, etc) - Catalog. 02

			// Monto de Operaciones
			$table->decimal('mto_oper_gravadas', 10, 2); // Total, cuyo productos tengan tipo de afectacion 10 (gravadas)
			$table->decimal('mto_oper_exoneradas', 10, 2); // Total, cuyo productos tengan tipo de afectacion 20 (exoneradas)

			// Impuestos
			$table->decimal('mto_igv', 10, 2); // Monto total del IGV (gravadas + exoneradas)
			$table->decimal('total_impuestos', 10, 2); // Monto total de impuestos (igv + otros impuestos)

			// Totales
			$table->decimal('valor_venta', 10, 2); // Valor total de la venta (gravadas + exoneradas) sin impuestos
			$table->decimal('sub_total', 10, 2); // Valor total de la venta (gravadas + exoneradas) + impuestos
			$table->decimal('mto_imp_venta', 10, 2); // Monto total de la venta (gravadas + exoneradas) + impuestos

			// SUNAT
			$table->string('estado_sunat')->default('pendiente');
			$table->string('hash_cpe')->nullable();
			$table->string('xml_path')->nullable();
			$table->string('cdr_path')->nullable();

			$table->foreignId('company_id')->constrained()->onDelete('cascade');
			$table->foreignId('client_id')->constrained()->onDelete('cascade');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('sales');
	}
};
