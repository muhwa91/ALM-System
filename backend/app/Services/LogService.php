<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LogService
{
	const TYPE_ANNUAL_REGISTER = "연차 등록";
	const TYPE_ANNUAL_UPDATE   = "연차 수정";
	const TYPE_ANNUAL_CANCEL   = "연차 취소";
	const TYPE_MEMBER_REGISTER = "사원 등록";
	const TYPE_MEMBER_RETIRE   = "퇴사사원 등록";

	/**
	 * @param string      $type     로그 구분
	 * @param string      $mbName   사원명
	 * @param string      $content  전체 로그 내용 (내부용)
	 * @param string|null $refDate  연차 관련 일자 (연차 로그에만 사용)
	 * @param string|null $reason   사유/내용 (표시용)
	 */
	public static function log(string $type, string $mbName, string $content, ?string $refDate = null, ?string $reason = null): void
	{
		DB::table('log_history')->insert([
			'log_mb_name'  => $mbName,
			'log_type'     => $type,
			'log_ref_date' => $refDate,
			'log_reason'   => $reason,
			'log_content'  => $content,
			'log_date'     => now(),
		]);
	}
}
