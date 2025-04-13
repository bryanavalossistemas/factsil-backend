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
		Schema::create('products', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->decimal('mto_precio_unitario', 10, 2);
			$table->string('cod_producto')->nullable();
			$table->char('unidad', 3)->default('NIU');
			$table->integer('stock')->default(0);
			$table->char('tip_afe_igv', 2)->default('10');
			$table->string('image_path')->nullable();

			$table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
			$table->foreignId('company_id')->constrained()->onDelete('cascade');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('products');
	}
};
