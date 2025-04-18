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
		Schema::create('addresses', function (Blueprint $table) {
			$table->id();
			$table->string('ubigueo');
			$table->string('departamento');
			$table->string('provincia');
			$table->string('distrito');
			$table->string('urbanizacion')->nullable();
			$table->string('direccion');
			$table->string('cod_local')->nullable();
			$table->foreignId('company_id')->constrained()->onDelete('cascade');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('addresses');
	}
};
