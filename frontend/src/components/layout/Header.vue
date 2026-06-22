<script setup>
import { useRouter } from "vue-router"
import { useAuthStore } from "@/stores/auth"

const router = useRouter()
const auth   = useAuthStore()

const colors = [
	"text-sky-300",
	"text-pink-300",
	"text-lime-300",
	"text-violet-300",
	"text-amber-300",
	"text-emerald-300",
	"text-cyan-300",
	"text-rose-300",
	"text-indigo-300",
	"text-fuchsia-300",
]

const title = "연차 관리 시스템"

const refreshToken = async () => {
	try {
		const res = await api.post("/auth/refresh")
		auth.setExpireAt(res.data.expire_at)
	} catch (err) {
		console.error("토큰 갱신 실패", err)
	}
}

const logout = async () => {
	try {
		await api.post("/auth/logout")
	} catch {}
	auth.clear()
	router.push("/login")
}
</script>

<template>
	<header class="w-full h-16 flex items-center px-6 shadow-md overflow-hidden">
		<div class="scroll-wrapper">
			<div class="scroll-inner">
				<h1
					v-for="(color, idx) in colors"
					:key="idx"
					class="scroll-item"
					:class="color"
				>
					<RouterLink to="/">{{ title }}</RouterLink>
				</h1>
			</div>
		</div>

		<div class="ml-auto flex items-center gap-3 shrink-0 text-sm">
			<div class="flex items-center gap-1 5">
				<span class="text-gray-400 text-xs">유효시간</span>
				<span
					class="font-mono font-bold tabular-nums ml-1"
					:class="auth.remaining < 300 ? 'text-error' : 'text-success'"
				>
					{{ auth.formattedRemaining }}
				</span>
			</div>
			<button class="btn btn-xs btn-outline btn-info" @click="refreshToken">
				갱신
			</button>
			<button class="btn btn-xs btn-ghost text-error" @click="logout">
				로그아웃
			</button>
		</div>
	</header>
</template>

<style scoped>
@import "@/assets/scss/layout/header.scss";
</style>
