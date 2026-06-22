<script setup>
import { ref, computed, watch } from "vue"
import { useRoute } from "vue-router"
import { useMemberNavigationStore } from "@/stores/memberNavigation"

const route = useRoute()
const nav = useMemberNavigationStore()

const isOpen = ref({
	member: true,
	annual: false,
})
const activeTab = ref("member") // 기본 탭: 사원관리

const memberRoutes = [
    "/member",
    "/member/info"
]

// 상위메뉴 활성화 여부
const isMemberActive = computed(() => {
    return activeTab.value === "member" && isOpen.value
})

// 라우트 이동 시 탭 전환
watch(
	() => route.path,
	(newPath) => {
		if (memberRoutes.includes(newPath)) {
			activeTab.value = "member"
			isOpen.value.member = true
			isOpen.value.annual = false
		} else if (newPath.startsWith("/annual")) {
			activeTab.value = "annual"
			isOpen.value.annual = true
			isOpen.value.member = false
		} else if (newPath === "/log") {
			activeTab.value = "log"
			isOpen.value.member = false
			isOpen.value.annual = false
		} else {
			activeTab.value = null
			isOpen.value.member = false
			isOpen.value.annual = false
		}
	},
	{ immediate: true }
)

watch(
	() => route.query.status,
	(newStatus) => {
		if (newStatus) {
			nav.status = newStatus
		}
	},
	{ immediate: true }
)

const isActive = (status) => {
	return nav.status === status
}

const toggleMenu = (tab) => {
	// 같은 탭 클릭 시 닫기
	if (activeTab.value === tab) {
		activeTab.value = null
		isOpen.value[tab] = false
		nav.status = null
	} else {
		// 모든 메뉴 닫고 선택한 메뉴만 열기
		for (const key in isOpen.value) {
			isOpen.value[key] = false
		}
		activeTab.value = tab
		isOpen.value[tab] = true
		if (tab !== "member") {
			nav.status = null
		}
	}
}
</script>

<template>
    <aside class="sidebar w-64 min-h-screen p-4">
        <ul class="menu rounded-box w-full">
            <li>
                <button class="menu-link flex items-center justify-between mb-5" :class="{ active: isMemberActive }" @click="toggleMenu('member')">
                    <div class="flex items-center gap-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        <span>사원관리</span>
                    </div>
                    <img 
                        v-if="!isOpen.member" 
                        src="@/assets/icon/right_theme.svg" 
                        alt="메뉴 아이콘" 
                        class="w-6 h-6" 
                    />
                    <img 
                        v-else 
                        src="@/assets/icon/right.svg" 
                        alt="메뉴 아이콘" 
                        class="w-6 h-6" 
                        :class="{ 'rotate-90': isOpen.member }" 
                    />
                </button>
                <transition name="slide">
                    <ul v-show="isOpen.member" class="pl-4 space-y-1 mb-5">
                        <li class="mb-3">
                            <RouterLink :to="{ path: '/member', query: { status: 'all', page: 1 } }" class="menu-link" :class="{ active: isActive('all') }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                전체 사원
                            </RouterLink>
                        </li>
                        <li class="mb-3">
                            <RouterLink :to="{ path: '/member', query: { status: 'employed', page: 1 } }" class="menu-link" :class="{ active: isActive('employed') }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                재직 사원
                            </RouterLink>
                        </li>
                        <li class="mb-3">
                            <RouterLink :to="{ path: '/member', query: { status: 'retired', page: 1 } }" class="menu-link" :class="{ active: isActive('retired') }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                </svg>
                                퇴사 사원
                            </RouterLink>
                        </li>
                    </ul>
                </transition>
            </li>
            <li>
                <button class="menu-link flex items-center justify-between mb-5" :class="{ active: activeTab === 'annual' }" @click="toggleMenu('annual')">
                    <div class="flex items-center gap-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                        </svg>
                        <span>연차관리</span>
                    </div>
                    <img 
                        v-if="!isOpen.annual" 
                        src="@/assets/icon/right_theme.svg" 
                        alt="메뉴 아이콘" 
                        class="w-6 h-6" 
                    />
                    <img 
                        v-else 
                        src="@/assets/icon/right.svg" 
                        alt="메뉴 아이콘" 
                        class="w-6 h-6" 
                        :class="{ 'rotate-90': isOpen.annual }" 
                    />
                </button>
                <transition name="slide">
                    <ul v-show="isOpen.annual" class="pl-4 space-y-1 mb-5">
                        <li class="mb-3">
                            <RouterLink to="/annual/list" class="menu-link" :class="{ active: route.path === '/annual/list' }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                                </svg>
                                연차 현황
                            </RouterLink>
                        </li>
                        <li class="mb-3">
                            <RouterLink to="/annual/add" class="menu-link" :class="{ active: route.path === '/annual/add' }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                연차 등록
                            </RouterLink>
                        </li>
                        <li class="mb-3">
                            <RouterLink to="/annual/use-list" class="menu-link" :class="{ active: route.path === '/annual/use-list' }">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                                연차 목록
                            </RouterLink>
                        </li>
                    </ul>
                </transition>
            </li>
            <li class="mb-5">
                <RouterLink to="/log" class="menu-link" :class="{ active: activeTab === 'log' }" @click="toggleMenu('log')">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                    </svg>
                    로그 관리
                </RouterLink>
            </li>
        </ul>
    </aside>
</template>

<style scoped>
@import "@/assets/scss/layout/sidebar.scss";
</style>
