<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member', function (Blueprint $table) {
            $table->bigIncrements('mb_id')->comment('사원 pk');
            $table->string('mb_affiliation', 10)->default('')->comment('소속 : 영업(스타,정보) / 관리(일반,주말)');
            $table->string('mb_department',  10)->default('')->comment('부서 : 영업, 관리');
            $table->string('mb_name',        50)->default('')->comment('사원명');
            $table->date('mb_hire_date')->nullable()->comment('입사일');
            $table->date('mb_retire_date')->nullable()->comment('퇴사일');
            $table->timestamps();

            $table->comment('사원');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member');
    }
};
