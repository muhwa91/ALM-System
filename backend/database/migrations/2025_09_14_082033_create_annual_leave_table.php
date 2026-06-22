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
        Schema::create('annual_leave', function (Blueprint $table) {
            $table->bigIncrements('al_id')->comment('연차 pk');
            $table->unsignedBigInteger('mb_id')->comment('사원 pk');
            $table->unsignedInteger('al_total_day')->default(0)->comment('총 연차');
            $table->unsignedInteger('al_used_day')->default(0)->comment('사용 연차');
            $table->unsignedInteger('al_remain_day')->default(0)->comment('잔여 연차');
            $table->timestamps();

            $table->comment('연차');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annual_leave');
    }
};
