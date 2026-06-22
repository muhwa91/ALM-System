<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('annual_leave_use', function (Blueprint $table) {
			$table->string('alu_status', 20)->default('pending')->after('alu_reason')->comment('상태: pending/approved/rejected');
		});
	}

	public function down(): void
	{
		Schema::table('annual_leave_use', function (Blueprint $table) {
			$table->dropColumn('alu_status');
		});
	}
};
