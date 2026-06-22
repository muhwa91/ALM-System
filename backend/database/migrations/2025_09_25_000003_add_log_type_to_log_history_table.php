<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		// log_type은 create_log_history_table 마이그레이션에 포함됨
	}

	public function down(): void
	{
		//
	}
};
