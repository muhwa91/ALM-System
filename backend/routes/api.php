<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AnnualLeaveController;
use App\Http\Controllers\AnnualLeaveUseController;
use App\Http\Controllers\LogHistoryController;

/**
 * 인증
 */
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
    Route::get('/check', 'check');
});

/**
 * 공휴일 (인증 불필요)
 */
Route::get('/holidays', [HolidayController::class, 'getHolidays']);

/**
 * 인증 필요 라우트
 */
Route::middleware('auth.token')->group(function () {

    /**
     * 사원관리
     */
    Route::prefix('member')->controller(MemberController::class)->group(function () {
        Route::get('/', 'getMember');
        Route::post('/update-retire', 'updateRetire');
        Route::post('/add', 'addMember');
        Route::get('info', 'getOneMember');
        Route::put('info', 'updateOneMember');
    });

    /**
     * 연차관리
     */
    Route::prefix('annual')->controller(AnnualLeaveController::class)->group(function () {
        Route::get('/list', 'list');
        Route::post('/calculate', 'updateAnnualLeave');
        Route::get('/member-list', 'getMemberAnnual');
        Route::post('/info', 'getOneMemberAnnual');
        Route::get('/events', 'getEvents');
        Route::delete('/{id}', 'destroy');
    });

    /**
     * 연차 사용 (신청/승인/반려/삭제)
     */
    Route::prefix('annual/use')->controller(AnnualLeaveUseController::class)->group(function () {
        Route::post('/', 'store');
        Route::get('/', 'list');
        Route::get('/all', 'listAll');
        Route::put('/{id}', 'update');
        Route::put('/{id}/status', 'updateStatus');
        Route::delete('/{id}', 'delete');
    });

    /**
     * 로그관리
     */
    Route::get('/log', [LogHistoryController::class, 'getLog']);
});
