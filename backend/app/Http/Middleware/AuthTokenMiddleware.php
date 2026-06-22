<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthTokenMiddleware
{
	public function handle(Request $request, Closure $next)
	{
		$token = $request->cookie("auth_token");

		if (!$token) {
			return response()->json(["message" => "인증이 필요합니다."], 401);
		}

		$row = DB::table("auth_tokens")
			->where("token", $token)
			->where("expire_at", ">", now()->timestamp)
			->first();

		if (!$row) {
			return response()->json(["message" => "인증이 만료되었습니다."], 401);
		}

		return $next($request);
	}
}
