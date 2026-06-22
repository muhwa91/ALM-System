<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogHistory;

class LogHistoryController extends Controller
{
	public function getLog(Request $request)
	{
		try {
			$page      = $request->page       ?? 1;
			$perPage   = $request->perPage    ?? 10;
			$type      = $request->type       ?? null;
			$name      = $request->name       ?? null;
			$startDate = $request->start_date ?? null;
			$endDate   = $request->end_date   ?? null;

			$validTypes = ['연차 등록', '연차 수정', '연차 취소', '사원 등록', '퇴사사원 등록'];
			if ($type && !in_array($type, $validTypes)) {
				$type = null;
			}

			$log = LogHistory::getLogList((int)$perPage, (int)$page, $type, $name, $startDate, $endDate);

			return response()->json($log, 200);

		} catch (\Exception $e) {
			return response()->json([
				'message' => '로그 조회 실패',
				'error'   => $e->getMessage(),
			], 500);
		}
	}
}
