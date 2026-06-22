<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\AnnualLeave;
use App\Models\AnnualLeaveUse;
use App\Services\LogService;
use Carbon\Carbon;

class AnnualLeaveController extends Controller
{
	/**
	 * 연차 계산 (재직 사원 전체)
	 */
	public function updateAnnualLeave(Request $request)
	{
		try {
			$members      = Member::whereNull('mb_retire_date')->get();
			$successCount = 0;
			$failList     = [];

			foreach ($members as $m) {
				try {
					AnnualLeave::calculate($m);
					$successCount++;
				} catch (\Exception $e) {
					$failList[] = $e->getMessage();
				}
			}

			$result = $this->list($request);

			// 실패 사원이 있어도 성공 결과와 함께 경고 반환
			if (!empty($failList)) {
				$body                = json_decode($result->getContent(), true);
				$body['warning']     = "일부 사원 계산 실패";
				$body['fail_list']   = $failList;
				return response()->json($body, 200);
			}

			return $result;

		} catch (\Exception $e) {
			return response()->json([
				'message' => '연차 계산 실패',
				'error'   => $e->getMessage(),
			], 500);
		}
	}

	/**
	 * 연차 목록 조회
	 */
	public function list(Request $request)
	{
		try {
			$perPage = $request->get('per_page', 10);

			$annuals = AnnualLeave::join('member', 'annual_leave.mb_id', '=', 'member.mb_id')
				->select(
					'member.mb_id as id',
					'member.mb_affiliation',
					'member.mb_department',
					'member.mb_name',
					'annual_leave.al_total_day as total_leave',
					'annual_leave.al_used_day as used_leave',
					'annual_leave.al_remain_day as remain_leave'
				)
				->whereNull('member.mb_retire_date')
				->orderBy('member.mb_hire_date', 'desc')
				->paginate($perPage);

			$data = $annuals->map(function ($row) {
				return [
					'id'           => $row->id,
					'affiliation'  => $row->mb_affiliation,
					'dept'         => $row->mb_department,
					'name'         => $row->mb_name,
					'total_leave'  => $row->total_leave,
					'used_leave'   => $row->used_leave,
					'remain_leave' => $row->remain_leave,
				];
			});

			return response()->json([
				'data'       => $data,
				'message'    => '연차 계산이 완료되었습니다.',
				'pagination' => [
					'current_page' => $annuals->currentPage(),
					'last_page'    => $annuals->lastPage(),
					'per_page'     => $annuals->perPage(),
					'total'        => $annuals->total(),
				],
			], 200);

		} catch (\Exception $e) {
			return response()->json([
				'message' => '연차 목록 조회 실패',
				'error'   => $e->getMessage(),
			], 500);
		}
	}

	/**
	 * 연차 등록 가능 사원 이름 목록
	 */
	public function getMemberAnnual()
	{
		try {
			$members = Member::whereNull('mb_retire_date')
				->select('mb_id as id', 'mb_name as name')
				->orderBy('mb_hire_date', 'desc')
				->get();

			return response()->json(['member' => $members], 200);
		} catch (\Exception $e) {
			return response()->json([
				'message' => '사원 목록 조회 실패',
				'error'   => $e->getMessage(),
			], 500);
		}
	}

	/**
	 * 단일 사원 연차 현황 조회
	 */
	public function getOneMemberAnnual(Request $request)
	{
		try {
			$id     = $request->id;
			$member = Member::findOrFail($id);
			$leave  = AnnualLeave::where('mb_id', $member->mb_id)->first();

			// 연차 현황 없으면 자동 계산 생성
			if (!$leave) {
				$leave = AnnualLeave::calculate($member);
			}

			$data = [
				'id'           => $member->mb_id,
				'affiliation'  => $member->mb_affiliation,
				'dept'         => $member->mb_department,
				'name'         => $member->mb_name,
				'hire_date'    => $member->mb_hire_date,
				'retire_date'  => $member->mb_retire_date ?? '',
				'total_leave'  => $leave->al_total_day,
				'used_leave'   => $leave->al_used_day,
				'remain_leave' => $leave->al_remain_day,
			];

			return response()->json(['result' => 'success', 'member' => $data], 200);

		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return response()->json(['result' => 'error', 'message' => '해당 사원을 찾을 수 없습니다.'], 404);
		} catch (\Exception $e) {
			return response()->json(['message' => '사원 조회 실패', 'error' => $e->getMessage()], 500);
		}
	}

	/**
	 * 연차 사용 이벤트 조회 (대시보드 캘린더용)
	 */
	public function getEvents(Request $request)
	{
		try {
			$year  = $request->get('year',  now()->year);
			$month = $request->get('month', now()->month);

			$startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
			$endDate   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

			$events = AnnualLeaveUse::join('member', 'annual_leave_use.mb_id', '=', 'member.mb_id')
				->select(
					'annual_leave_use.alu_id as id',
					'annual_leave_use.alu_date as date',
					'annual_leave_use.alu_reason as reason',
					'member.mb_name as name',
					'annual_leave_use.alu_status as status'
				)
				->whereBetween('annual_leave_use.alu_date', [$startDate, $endDate])
				->where('annual_leave_use.alu_status', AnnualLeaveUse::STATUS_APPROVED)
				->orderBy('annual_leave_use.alu_date')
				->get();

			return response()->json(['events' => $events], 200);

		} catch (\Exception $e) {
			return response()->json([
				'message' => '이벤트 조회 실패',
				'error'   => $e->getMessage(),
			], 500);
		}
	}

	/**
	 * 연차 삭제 (annual_leave 레코드 + 관련 신청 내역 전체 삭제)
	 */
	public function destroy($id)
	{
		try {
			$leave  = AnnualLeave::findOrFail($id);
			$member = Member::find($leave->mb_id);

			AnnualLeaveUse::where('mb_id', $leave->mb_id)->delete();
			$leave->delete();

			return response()->json([
				'result'  => 'success',
				'message' => ($member ? $member->mb_name . ' ' : '') . '연차 데이터가 삭제되었습니다.',
			], 200);

		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			return response()->json(['result' => 'error', 'message' => '해당 연차 데이터를 찾을 수 없습니다.'], 404);
		} catch (\Exception $e) {
			return response()->json(['result' => 'error', 'message' => '삭제 실패: ' . $e->getMessage()], 500);
		}
	}
}
