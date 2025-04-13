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
		Schema::create('clients', function (Blueprint $table) {
			$table->id();
			$table->string('tipo_doc', 2); // 1 = DNI, 6 = RUC, 7 = CE, 4 = PASAPORTE, 0 = OTRO Catalogo 06 sunat
			$table->string('num_doc');
			$table->string('rzn_social');
			$table->foreignId('company_id')->constrained()->onDelete('cascade');
			$table->timestamps();

			$table->unique(['company_id', 'num_doc']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('clients');
	}
};
