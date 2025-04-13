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
		Schema::create('companies', function (Blueprint $table) {
			$table->id();

			// METADATA
			$table->string('ruc', 11)->unique();
			$table->string('razon_social');
			$table->string('nombre_comercial')->nullable();
			$table->string('logo_path')->nullable();

			// SUNAT
			$table->string('sol_user')->nullable();
			$table->string('sol_pass')->nullable();
			$table->string('cert_path')->nullable();
			$table->string('client_id')->nullable();
			$table->string('client_secret')->nullable();
			$table->string('serie_base')->default('1');
			$table->boolean('production')->default(false);

			$table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('companies');
	}
};
