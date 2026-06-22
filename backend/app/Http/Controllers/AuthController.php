<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
	const TOKEN_COOKIE  = "auth_token";
	const TOKEN_MINUTES = 180; // 3시간

	/**
	 * 로그인
	 */
	public function login(Request $request)
	{
		$id = $request->input("id");
		$pw = $request->input("password");

		if ($id !== config("site.login_id") || $pw !== config("site.login_password")) {
			return response()->json(["message" => "아이디 또는 비밀번호가 올바르지 않습니다."], 401);
		}

		return $this->issueToken();
	}

	/**
	 * 로그아웃
	 */
	public function logout(Request $request)
	{
		$token = $request->cookie(self::TOKEN_COOKIE);
		if ($token) {
			DB::table("auth_tokens")->where("token", $token)->delete();
		}

		return response()->json(["message" => "로그아웃 되었습니다."])
			->withCookie(cookie()->forget(self::TOKEN_COOKIE));
	}

	/**
	 * 토큰 갱신
	 */
	public function refresh(Request $request)
	{
		$old = $request->cookie(self::TOKEN_COOKIE);
		if ($old) {
			DB::table("auth_tokens")->where("token", $old)->delete();
		}

		return $this->issueToken();
	}

	/**
	 * 인증 상태 확인
	 */
	public function check(Request $request)
	{
		$token = $request->cookie(self::TOKEN_COOKIE);
		if (!$token) {
			return response()->json(["authenticated" => false], 401);
		}

		$row = DB::table("auth_tokens")
			->where("token", $token)
			->where("expire_at", ">", now()->timestamp)
			->first();

		if (!$row) {
			return response()->json(["authenticated" => false], 401);
		}

		return response()->json([
			"authenticated" => true,
			"expire_at"     => $row->expire_at,
		]);
	}

	/**
	 * 토큰 발급 공통 처리
	 */
	private function issueToken(): \Illuminate\Http\JsonResponse
	{
		// 만료된 토큰 정리
		DB::table("auth_tokens")->where("expire_at", "<=", now()->timestamp)->delete();

		$token    = Str::uuid()->toString();
		$expireAt = now()->addMinutes(self::TOKEN_MINUTES)->timestamp;

		DB::table("auth_tokens")->insert([
			"token"      => $token,
			"expire_at"  => $expireAt,
			"created_at" => now(),
			"updated_at" => now(),
		]);

		$cookie = cookie(
			self::TOKEN_COOKIE,
			$token,
			self::TOKEN_MINUTES,
			"/",
			null,
			false, // secure: 배포 시 true
			true,  // httpOnly
			false,
			"Lax"
		);

		return response()->json([
			"message"   => "인증 성공",
			"expire_at" => $expireAt,
		])->withCookie($cookie);
	}
}
