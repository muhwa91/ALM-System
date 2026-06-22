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
        Schema::create('annual_leave_use', function (Blueprint $table) {
            $table->bigIncrements('alu_id')->comment('연차 사용 내역 pk');
            $table->unsignedBigInteger('mb_id')->comment('사원 pk');
            $table->unsignedBigInteger('al_id')->comment('연차 pk');
            $table->date('alu_date')->comment('연차 사용일');
            $table->string('alu_reason', 255)->nullable()->comment('연차 사유');
            $table->timestamps();

            $table->comment('연차 사용일');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annual_leave_use');
    }
};
