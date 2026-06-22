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
        Schema::create('log_history', function (Blueprint $table) {
            $table->bigIncrements('log_id')->comment('로그 pk');
            $table->string('log_mb_name', 50)->default('')->comment('사원명');
            $table->string('log_type', 30)->default('')->comment('로그 구분');
            $table->date('log_ref_date')->nullable()->comment('연차 관련 일자');
            $table->text('log_reason')->nullable()->comment('사유/내용');
            $table->text('log_content')->default('')->comment('로그 전체 내용 (내부용)');
            $table->dateTime('log_date')->comment('로그 생성일시');

            $table->comment('로그');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_history');
    }
};
