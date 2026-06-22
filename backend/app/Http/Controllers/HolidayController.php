<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HolidayController extends Controller
{
	public function getHolidays(Request $request)
	{
		$year  = (int)$request->get('year',  now()->year);
		$month = (int)$request->get('month', now()->month);

		try {
			$response = Http::timeout(10)
				->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/KR");

			if (!$response->successful()) {
				return response()->json(['holidays' => []]);
			}

			$holidays = collect($response->json())
				->filter(fn($h) => (int)explode('-', $h['date'])[1] === $month)
				->map(fn($h) => [
					'date' => $h['date'],
					'name' => $h['localName'],
				])
				->values();

			return response()->json(['holidays' => $holidays]);

		} catch (\Exception $e) {
			return response()->json(['holidays' => []]);
		}
	}
}
