<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AnnualLeave extends Model
{
	protected $table = 'annual_leave';
	protected $primaryKey = 'al_id';
	protected $fillable = [
		'mb_id', 'al_total_day', 'al_used_day', 'al_remain_day'
	];

	/**
	 * 사원 연차 계산
	 *
	 * @throws \RuntimeException 계산 불가 시
	 */
	public static function calculate($member)
	{
		// 입사일 없으면 계산 불가
		if (empty($member->mb_hire_date)) {
			throw new \RuntimeException("{$member->mb_name}: 입사일이 없어 계산할 수 없습니다.");
		}

		$today     = Carbon::today();
		$hireDate  = Carbon::parse($member->mb_hire_date)->startOfDay();

		// 입사일이 오늘 이후인 경우
		if ($hireDate->gt($today)) {
			$totalLeave = 0;
		} elseif ($hireDate->diffInYears($today) >= 1) {
			// 1년 이상: 15일 (근로기준법 기준)
			$totalLeave = 15;
		} else {
			// 1년 미만: 입사 후 매 1개월 기념일마다 1일 (최대 11일)
			$months    = 0;
			$checkDate = $hireDate->copy();

			// addMonth()를 명시적으로 재할당해 mutable/immutable 모두 대응
			while (true) {
				$checkDate = $checkDate->copy()->addMonth();
				if ($checkDate->gt($today)) {
					break;
				}
				$months++;
			}

			$totalLeave = min($months, 11);
		}

		$leave = self::where('mb_id', $member->mb_id)->first();

		if ($leave) {
			$leave->al_total_day  = $totalLeave;
			$leave->al_remain_day = max(0, $totalLeave - $leave->al_used_day);
			$leave->save();
		} else {
			$leave = self::create([
				'mb_id'         => $member->mb_id,
				'al_total_day'  => $totalLeave,
				'al_used_day'   => 0,
				'al_remain_day' => $totalLeave,
			]);
		}

		return $leave;
	}
}
