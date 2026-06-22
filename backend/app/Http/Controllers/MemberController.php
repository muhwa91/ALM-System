<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Services\LogService;

class MemberController extends Controller
{
    /**
     * 사원 목록 조회
     */
    public function getMember(Request $request)
    {
        try {
            $status    = $request->status;
            $order_by  = $request->orderBy;
            $direction = $request->direction;

            $order_by_map = [
                'hire_date' => 'mb_hire_date',
            ];

            $member = Member::getMemberList($status, $order_by_map[$order_by], $direction, true);

            $data = $member->map(function ($m) {
                return [
                    'id'          => $m->mb_id,
                    'affiliation' => $m->mb_affiliation,
                    'dept'        => $m->mb_department,
                    'name'        => $m->mb_name,
                    'hire_date'   => $m->mb_hire_date,
                    'retire_date' => $m->mb_retire_date ?? "",
                ];
            });

            return response()->json([
                'member'     => $data,
                'pagination' => [
                    'current_page' => $member->currentPage(),
                    'last_page'    => $member->lastPage(),
                    'per_page'     => $member->perPage(),
                    'total'        => $member->total(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '사원 목록 조회 실패',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 단일 사원 조회
     */
    public function getOneMember(Request $request)
    {
        try {
            $id     = $request->id;
            $member = Member::findOrFail($id);

            $data = [
                'id'          => $member->mb_id,
                'affiliation' => $member->mb_affiliation,
                'dept'        => $member->mb_department,
                'name'        => $member->mb_name,
                'hire_date'   => $member->mb_hire_date,
                'retire_date' => $member->mb_retire_date ?? "",
            ];

            return response()->json(['result' => 'success', 'member' => $data], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['result' => 'error', 'message' => '해당 사원을 찾을 수 없습니다.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => '사원 조회 실패', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 단일 사원 수정
     */
    public function updateOneMember(Request $request)
    {
        try {
            $id     = $request->id;
            $member = Member::findOrFail($id);

            $member->mb_affiliation = $request->input('affiliation');
            $member->mb_department  = $request->input('dept');
            $member->mb_name        = $request->input('name');
            $member->mb_hire_date   = $request->input('hire_date');
            $member->mb_retire_date = $request->input('retire_date') ?: null;
            $member->save();

            return response()->json([
                'result'  => 'success',
                'message' => "{$member->mb_name} 사원 정보가 수정되었습니다.",
                'member'  => [
                    'id'          => $member->mb_id,
                    'affiliation' => $member->mb_affiliation,
                    'dept'        => $member->mb_department,
                    'name'        => $member->mb_name,
                    'hire_date'   => $member->mb_hire_date,
                    'retire_date' => $member->mb_retire_date ?? "",
                ],
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['result' => 'error', 'message' => '해당 사원을 찾을 수 없습니다.'], 404);
        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'message' => '사원 수정 실패', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 퇴사일 업데이트
     */
    public function updateRetire(Request $request)
    {
        $data = $request->input('retire_data', []);

        try {
            $result = Member::updateRetireDate($data);

            if ($result['register_count'] + $result['update_count'] + $result['delete_count'] === 0) {
                return response()->json(['result' => 'error', 'message' => '변경된 내역이 없습니다.'], 200);
            }

            foreach ($data as $row) {
                if (!empty($row['retire_date'])) {
                    $m = Member::find($row['id']);
                    if ($m) {
                        LogService::log(
                            LogService::TYPE_MEMBER_RETIRE,
                            $m->mb_name,
                            "퇴사사원 등록: {$m->mb_name} / 퇴사일: {$row['retire_date']}",
                            null,
                            "퇴사일: {$row['retire_date']}"
                        );
                    }
                }
            }

            return response()->json([
                'result'  => 'success',
                'message' =>
                    "퇴사일 변경 완료\n".
                    "신규 퇴사일 등록: {$result['register_count']}건\n".
                    "기존 퇴사일 변경: {$result['update_count']}건\n".
                    "퇴사일 삭제: {$result['delete_count']}건",
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => '퇴사일 수정 실패: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 사원 추가
     */
    public function addMember(Request $request)
    {
        $member_validate = $request->validate([
            'mb_affiliation' => 'required|in:스타,정보,일반,주말',
            'mb_department'  => 'required|in:영업,관리',
            'mb_name'        => 'required|string|max:50',
            'mb_hire_date'   => 'required|date',
            'mb_retire_date' => 'nullable|date',
        ]);

        try {
            $data = [
                'affiliation' => $member_validate['mb_affiliation'],
                'dept'        => $member_validate['mb_department'],
                'name'        => $member_validate['mb_name'],
                'hire_date'   => $member_validate['mb_hire_date'],
                'retire_date' => $member_validate['mb_retire_date'] ?? null,
            ];

            $member = Member::add($data);

            if ($member) {
                LogService::log(
                    LogService::TYPE_MEMBER_REGISTER,
                    $data['name'],
                    "사원 등록: {$data['name']} / 입사일: {$data['hire_date']}",
                    null,
                    "입사일: {$data['hire_date']}"
                );
                return response()->json(['message' => '사원이 추가되었습니다.', 'data' => $member], 201);
            }

            return response()->json(['message' => '사원 생성에 실패했습니다.'], 500);

        } catch (\Exception $e) {
            return response()->json(['message' => '서버 오류: ' . $e->getMessage()], 500);
        }
    }
}
