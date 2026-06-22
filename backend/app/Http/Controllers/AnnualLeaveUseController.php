<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnnualLeave;
use App\Models\AnnualLeaveUse;
use App\Models\Member;
use App\Services\LogService;
use Carbon\Carbon;

class AnnualLeaveUseController extends Controller
{
	/**
	 * 연차 신청 등록
	 */
	public function store(Request $request)
	{
		try {
			$mbId   = $request->mb_id;
			$date   = $request->date;
			$reason = $request->reason ?? "";

			if (!$mbId || !$date) {
				return response()->json(["message" => "필수 항목이 없습니다."], 422);
			}

			$member = Member::findOrFail($mbId);

			$leave = AnnualLeave::where("mb_id", $mbId)->first();
			if (!$leave) {
				return response()->json(["message" => "연차 현황이 없습니다. 연차 현황 메뉴에서 연차 계산을 먼저 실행해주세요."], 422);
			}

			if ($leave->al_remain_day < 1) {
				return response()->json(["message" => "잔여 연차가 부족합니다."], 422);
			}

			$exists = AnnualLeaveUse::where("mb_id", $mbId)
				->where("alu_date", $date)
				->where("alu_status", AnnualLeaveUse::STATUS_APPROVED)
				->exists();
			if ($exists) {
				return response()->json(["message" => "해당 날짜에 이미 연차가 등록되어 있습니다."], 422);
			}

			// 등록 즉시 연차 차감
			$leave->al_used_day   += 1;
			$leave->al_remain_day -= 1;
			$leave->save();

			AnnualLeaveUse::create([
				"mb_id"      => $mbId,
				"al_id"      => $leave->al_id,
				"alu_date"   => $date,
				"alu_reason" => $reason,
				"alu_status" => AnnualLeaveUse::STATUS_APPROVED,
			]);

			LogService::log(
				LogService::TYPE_ANNUAL_REGISTER,
				$member->mb_name,
				"연차 등록: {$date}" . ($reason ? " / 사유: {$reason}" : ""),
				$date,
				$reason ?: null
			);

			return response()->json(["message" => "연차가 등록되었습니다."], 200);

		} catch (\Exception $e) {
			return response()->json(["message" => "연차 신청 실패: " . $e->getMessage()], 500);
		}
	}

	/**
	 * 연차 사용 내역 조회 (사원별)
	 */
	public function list(Request $request)
	{
		try {
			$mbId = $request->mb_id;

			$list = AnnualLeaveUse::where("mb_id", $mbId)
				->orderBy("alu_date", "desc")
				->get()
				->map(function ($row) {
					return [
						"id"     => $row->alu_id,
						"date"   => $row->alu_date,
						"reason" => $row->alu_reason,
						"status" => $row->alu_status,
					];
				});

			return response()->json(["data" => $list], 200);

		} catch (\Exception $e) {
			return response()->json(["message" => "조회 실패", "error" => $e->getMessage()], 500);
		}
	}

	/**
	 * 연차 신청 전체 목록 조회 (연차 목록 메뉴용)
	 */
	public function listAll(Request $request)
	{
		try {
			$perPage   = $request->get('per_page', 15);
			$status    = $request->get('status', null);
			$name      = $request->get('name', null);
			$startDate = $request->get('start_date', null);
			$endDate   = $request->get('end_date', null);

			$query = AnnualLeaveUse::join('member', 'annual_leave_use.mb_id', '=', 'member.mb_id')
				->select(
					'annual_leave_use.alu_id as id',
					'member.mb_name as name',
					'annual_leave_use.alu_date as date',
					'annual_leave_use.alu_reason as reason',
					'annual_leave_use.alu_status as status'
				)
				->orderBy('annual_leave_use.alu_date', 'desc');

			if ($status) {
				$query->where('annual_leave_use.alu_status', $status);
			}
			if ($name) {
				$query->where('member.mb_name', 'like', "%{$name}%");
			}
			if ($startDate) {
				$query->where('annual_leave_use.alu_date', '>=', $startDate);
			}
			if ($endDate) {
				$query->where('annual_leave_use.alu_date', '<=', $endDate);
			}

			$list = $query->paginate($perPage);

			return response()->json([
				'data'       => $list->items(),
				'pagination' => [
					'current_page' => $list->currentPage(),
					'last_page'    => $list->lastPage(),
					'per_page'     => $list->perPage(),
					'total'        => $list->total(),
				],
			], 200);

		} catch (\Exception $e) {
			return response()->json(["message" => "조회 실패", "error" => $e->getMessage()], 500);
		}
	}

	/**
	 * 연차 날짜/사유 수정 (대시보드용)
	 */
	public function update(Request $request, $id)
	{
		try {
			$use  = AnnualLeaveUse::findOrFail($id);
			$date = $request->date;

			if (!$date) {
				return response()->json(["message" => "날짜는 필수입니다."], 422);
			}

			// 같은 사원의 다른 날짜 중복 확인
			$exists = AnnualLeaveUse::where("mb_id", $use->mb_id)
				->where("alu_date", $date)
				->where("alu_id", "!=", $id)
				->whereIn("alu_status", [AnnualLeaveUse::STATUS_PENDING, AnnualLeaveUse::STATUS_APPROVED])
				->exists();
			if ($exists) {
				return response()->json(["message" => "해당 날짜에 이미 연차가 신청되어 있습니다."], 422);
			}

			$oldDate         = $use->alu_date;
			$use->alu_date   = $date;
			$use->alu_reason = $request->reason ?? $use->alu_reason;
			$use->save();

			$member  = Member::findOrFail($use->mb_id);
			$logContent = $oldDate !== $date
				? "연차 수정: {$oldDate} → {$date}"
				: "연차 수정: {$date} (사유 변경)";
			LogService::log(LogService::TYPE_ANNUAL_UPDATE, $member->mb_name, $logContent, $date, $request->reason ?? null);

			return response()->json(["message" => "연차가 변경되었습니다."], 200);

		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return response()->json(["message" => "해당 신청을 찾을 수 없습니다."], 404);
		} catch (\Exception $e) {
			return response()->json(["message" => "변경 실패: " . $e->getMessage()], 500);
		}
	}

	/**
	 * 승인 / 반려
	 */
	public function updateStatus(Request $request, $id)
	{
		try {
			$status = $request->status;

			if ($status !== AnnualLeaveUse::STATUS_APPROVED) {
				return response()->json(["message" => "잘못된 상태값입니다."], 422);
			}

			$use = AnnualLeaveUse::findOrFail($id);

			if ($use->alu_status !== AnnualLeaveUse::STATUS_PENDING) {
				return response()->json(["message" => "대기 중인 신청만 승인할 수 있습니다."], 422);
			}

			$leave = AnnualLeave::findOrFail($use->al_id);

			if ($leave->al_remain_day < 1) {
				return response()->json(["message" => "잔여 연차가 부족합니다."], 422);
			}
			$leave->al_used_day   += 1;
			$leave->al_remain_day -= 1;
			$leave->save();

			$use->alu_status = AnnualLeaveUse::STATUS_APPROVED;
			$use->save();

			return response()->json(["message" => "승인되었습니다."], 200);

		} catch (\Exception $e) {
			return response()->json(["message" => "상태 변경 실패", "error" => $e->getMessage()], 500);
		}
	}

	/**
	 * 연차 신청 삭제
	 * - 대기(pending): 그냥 삭제
	 * - 승인(approved): 연차 차감 복구 후 삭제
	 */
	public function delete($id)
	{
		try {
			$use = AnnualLeaveUse::findOrFail($id);

			$allowedStatuses = [AnnualLeaveUse::STATUS_PENDING, AnnualLeaveUse::STATUS_APPROVED];
			if (!in_array($use->alu_status, $allowedStatuses)) {
				return response()->json(["message" => "삭제할 수 없는 상태입니다."], 422);
			}

			// 승인 건은 연차 복구
			if ($use->alu_status === AnnualLeaveUse::STATUS_APPROVED) {
				$leave = AnnualLeave::where('mb_id', $use->mb_id)->first();
				if ($leave) {
					$leave->al_used_day   = max(0, $leave->al_used_day - 1);
					$leave->al_remain_day = $leave->al_total_day - $leave->al_used_day;
					$leave->save();
				}
			}

			$member = Member::findOrFail($use->mb_id);
			$date   = $use->alu_date;
			$use->delete();

			LogService::log(LogService::TYPE_ANNUAL_CANCEL, $member->mb_name, "연차 취소: {$date}", $date);

			return response()->json(["message" => "연차 신청이 삭제되었습니다."], 200);

		} catch (\Exception $e) {
			return response()->json(["message" => "삭제 실패", "error" => $e->getMessage()], 500);
		}
	}
}
