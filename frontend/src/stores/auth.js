import { defineStore } from "pinia"
import { ref, computed } from "vue"

export const useAuthStore = defineStore("auth", () => {
	const expireAt    = ref(Number(sessionStorage.getItem("expire_at")) || null)
	const initialized = ref(false)
	const remaining   = ref(0)
	let timer = null

	const isLoggedIn = computed(() => !!expireAt.value && remaining.value > 0)

	const formattedRemaining = computed(() => {
		const s = remaining.value
		if (s <= 0) return "00:00:00"
		const h   = Math.floor(s / 3600)
		const m   = Math.floor((s % 3600) / 60)
		const sec = s % 60
		return [h, m, sec].map(v => String(v).padStart(2, "0")).join(":")
	})

	const tick = () => {
		if (!expireAt.value) { remaining.value = 0; return }
		const diff = Math.floor((expireAt.value * 1000 - Date.now()) / 1000)
		remaining.value = Math.max(0, diff)
		if (remaining.value === 0) stopTimer()
	}

	const startTimer = () => {
		stopTimer()
		tick()
		timer = setInterval(tick, 1000)
	}

	const stopTimer = () => {
		if (timer) { clearInterval(timer); timer = null }
	}

	const setExpireAt = (timestamp) => {
		expireAt.value = timestamp
		sessionStorage.setItem("expire_at", String(timestamp))
		startTimer()
	}

	const clear = () => {
		expireAt.value    = null
		remaining.value   = 0
		initialized.value = false
		sessionStorage.removeItem("expire_at")
		stopTimer()
	}

	// 앱 새로고침 시 sessionStorage 값으로 타이머 복원
	if (expireAt.value) startTimer()

	return { expireAt, initialized, remaining, isLoggedIn, formattedRemaining, setExpireAt, clear }
})
