import { createRouter, createWebHistory } from "vue-router"
import { useAuthStore } from "@/stores/auth"

const router = createRouter({
	history: createWebHistory(import.meta.env.BASE_URL),
	routes: [
		{
			path: "/login",
			name: "login",
			component: () => import("../views/LoginView.vue"),
		},
		{
			path: "/",
			name: "home",
			component: () => import("../views/HomeView.vue"),
		},
		// 사원 관리
		{
			path: "/member",
			name: "member",
			component: () => import("../views/Member/MemberView.vue"),
		},
{
			path: "/member/info",
			name: "memberInfo",
			component: () => import("../views/Member/MemberInfoView.vue"),
		},
		// 연차 관리
		{
			path: "/annual/list",
			name: "annualList",
			component: () => import("../views/Annual/AnnualListView.vue"),
		},
		{
			path: "/annual/add",
			name: "annualAdd",
			component: () => import("../views/Annual/AnnualAddView.vue"),
		},
		{
			path: "/annual/detail/:id",
			name: "annualDetail",
			component: () => import("../views/Annual/AnnualDetailView.vue"),
		},
		{
			path: "/annual/use-list",
			name: "annualUseList",
			component: () => import("../views/Annual/AnnualUseListView.vue"),
		},
		// 로그 관리
		{
			path: "/log",
			name: "log",
			component: () => import("../views/Log/LogView.vue"),
		},
	],
})

router.beforeEach(async (to, from, next) => {
	if (to.name === "login") {
		next()
		return
	}

	const auth = useAuthStore()

	// 최초 진입 시 서버에서 인증 상태 확인
	if (!auth.initialized) {
		try {
			const res = await api.get("/auth/check")
			auth.setExpireAt(res.data.expire_at)
		} catch {
			auth.clear()
		}
		auth.initialized = true
	}

	if (!auth.isLoggedIn) {
		next({ name: "login" })
	} else {
		next()
	}
})

export default router
