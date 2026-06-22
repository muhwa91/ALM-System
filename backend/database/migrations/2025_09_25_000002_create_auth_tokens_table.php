<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('auth_tokens', function (Blueprint $table) {
			$table->string('token', 100)->primary();
			$table->bigInteger('expire_at')->comment('만료 UNIX timestamp');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('auth_tokens');
	}
};
