<script setup>
import { ref } from "vue"
import { useRouter } from "vue-router"
import { useAuthStore } from "@/stores/auth"

const router   = useRouter()
const auth     = useAuthStore()
const id       = ref("")
const password = ref("")
const error    = ref("")
const loading  = ref(false)

const login = async () => {
	if (!id.value || !password.value) {
		error.value = "아이디와 비밀번호를 입력하세요."
		return
	}
	error.value  = ""
	loading.value = true
	try {
		const res = await api.post("/auth/login", { id: id.value, password: password.value })
		auth.setExpireAt(res.data.expire_at)
		auth.initialized = true
		router.push("/")
	} catch (err) {
		console.error("Login error:", err)
		error.value = err.response?.data?.message ?? "로그인에 실패하였습니다."
	} finally {
		loading.value = false
	}
}
</script>

<template>
	<div class="min-h-screen flex items-center justify-center bg-base-300">
		<div class="card w-96 bg-base-100 shadow-2xl">
			<form class="card-body gap-4" @submit.prevent="login">
				<h2 class="text-2xl font-bold text-center mb-2">연차 관리 시스템</h2>

				<div class="form-control">
					<label class="label">
						<span class="label-text">아이디</span>
					</label>
					<input
						v-model="id"
						type="text"
						class="input input-bordered w-full"
						placeholder="아이디를 입력하세요"
						autocomplete="username"
						autofocus
					/>
				</div>

				<div class="form-control">
					<label class="label">
						<span class="label-text">비밀번호</span>
					</label>
					<input
						v-model="password"
						type="password"
						class="input input-bordered w-full"
						placeholder="비밀번호를 입력하세요"
						autocomplete="current-password"
					/>
				</div>

				<p v-if="error" class="text-error text-sm text-center -mt-1">{{ error }}</p>

				<button
					type="submit"
					class="btn btn-primary w-full mt-2"
					:disabled="loading"
				>
					<span v-if="loading" class="loading loading-spinner loading-sm"></span>
					로그인
				</button>
			</form>
		</div>
	</div>
</template>
