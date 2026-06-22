<script setup>
import { ref, computed, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import MessageModal from "@/components/MessageModal.vue"

const route  = useRoute()
const router = useRouter()

const loading = ref(false)
const member  = ref({
	id: "",
	affiliation: "",
	dept: "",
	name: "",
	hire_date: "",
	total_leave: 0,
	used_leave: 0,
	remain_leave: 0,
})
const annualUseList = ref([])

const showMessageModal = ref(false)
const message          = ref("")
const messageType      = ref("")

const openMessageModal = (msg, type = "info") => {
	message.value      = msg
	messageType.value  = type
	showMessageModal.value = true
}

// 연차 계산 설명 텍스트
const leaveCalcInfo = computed(() => {
	if (!member.value.hire_date) return []

	const today    = new Date()
	const hire     = new Date(member.value.hire_date)

	// 연수 계산 (백엔드 로직 동일하게)
	let months = 0
	let check  = new Date(hire)
	while (true) {
		check = new Date(check.getFullYear(), check.getMonth() + 1, check.getDate())
		if (check > today) break
		months++
	}
	const years = Math.floor(months / 12)

	if (years >= 1) {
		return [
			{ label: "입사일",    value: member.value.hire_date },
			{ label: "근속 기간", value: `${years}년 ${months % 12}개월` },
			{ label: "계산 기준", value: "1년 이상 재직 시 법정 연차 15일 부여 (근로기준법 제60조)" },
			{ label: "부여 연차", value: `${member.value.total_leave}일` },
		]
	} else {
		const given = Math.min(months, 11)
		return [
			{ label: "입사일",    value: member.value.hire_date },
			{ label: "근속 기간", value: `${months}개월` },
			{ label: "계산 기준", value: "1년 미만 재직 시 매 1개월 만근마다 1일 부여 (최대 11일)" },
			{ label: "부여 연차", value: `${given}일 (${months}개월 × 1일)` },
		]
	}
})

const fetchMemberAnnual = async (id) => {
	try {
		loading.value = true
		const res = await api.post("/annual/info", { id })
		if (res.status === 200) {
			member.value = res.data.member
		}
	} catch (err) {
		openMessageModal("연차 현황 불러오기 실패", "error")
	} finally {
		loading.value = false
	}
}

const fetchAnnualUseList = async (id) => {
	try {
		const res = await api.get("/annual/use", { params: { mb_id: id } })
		if (res.status === 200) {
			annualUseList.value = res.data.data
		}
	} catch (err) {
		console.error(err)
	}
}

onMounted(() => {
	const id = Number(route.params.id)
	fetchMemberAnnual(id)
	fetchAnnualUseList(id)
})
</script>

<template>
	<div class="max-w-4xl mx-auto p-10">
		<div class="flex items-center mb-10 gap-4">
			<button class="btn btn-ghost btn-sm" @click="router.back()">◀ 뒤로</button>
			<h2 class="text-2xl font-bold text-center flex-1">연차 상세</h2>
		</div>

		<!-- 사원명 고정 표시 -->
		<div class="form-control mb-8">
			<label class="label">
				<span class="label-text font-medium mb-2">사원명</span>
			</label>
			<p class="input input-bordered w-full bg-base-100 cursor-default flex items-center">
				<template v-if="loading">
					<div class="skeleton h-[20px] w-3/4 bg-gray-700 animate-pulse rounded"></div>
				</template>
				<template v-else>{{ member.name }}</template>
			</p>
		</div>

		<div class="space-y-10">
			<!-- 사원 정보 -->
			<div class="grid grid-cols-2 gap-4">
				<div class="form-control">
					<label class="label"><span class="label-text font-medium mb-2">소속</span></label>
					<p v-if="loading" class="input input-bordered w-full bg-base-100 cursor-default">
						<div class="skeleton h-[20px] w-3/4 mx-auto bg-gray-700 animate-pulse rounded"></div>
					</p>
					<p v-else class="input input-bordered w-full bg-base-100 cursor-default">
						{{ member.affiliation }}
					</p>
				</div>
				<div class="form-control">
					<label class="label"><span class="label-text font-medium mb-2">부서</span></label>
					<p v-if="loading" class="input input-bordered w-full bg-base-100 cursor-default">
						<div class="skeleton h-[20px] w-3/4 mx-auto bg-gray-700 animate-pulse rounded"></div>
					</p>
					<p v-else class="input input-bordered w-full bg-base-100 cursor-default">
						{{ member.dept }}
					</p>
				</div>
				<div class="form-control">
					<label class="label"><span class="label-text font-medium mb-2">입사일</span></label>
					<p v-if="loading" class="input input-bordered w-full bg-base-100 cursor-default">
						<div class="skeleton h-[20px] w-3/4 mx-auto bg-gray-700 animate-pulse rounded"></div>
					</p>
					<p v-else class="input input-bordered w-full bg-base-100 cursor-default">{{ member.hire_date }}</p>
				</div>
			</div>

			<!-- 연차 현황 -->
			<div class="grid grid-cols-3 gap-4">
				<div class="stat bg-base-200 rounded-lg shadow p-4 text-center">
					<div class="stat-title">총 연차</div>
					<div class="stat-value">
						<template v-if="loading"><span class="loading loading-spinner loading-lg text-primary"></span></template>
						<template v-else>{{ member.total_leave }}</template>
					</div>
				</div>
				<div class="stat bg-base-200 rounded-lg shadow p-4 text-center">
					<div class="stat-title">사용 연차</div>
					<div class="stat-value">
						<template v-if="loading"><span class="loading loading-spinner loading-lg text-primary"></span></template>
						<template v-else>{{ member.used_leave }}</template>
					</div>
				</div>
				<div class="stat bg-base-200 rounded-lg shadow p-4 text-center">
					<div class="stat-title">잔여 연차</div>
					<div class="stat-value">
						<template v-if="loading"><span class="loading loading-spinner loading-lg text-primary"></span></template>
						<template v-else>{{ member.remain_leave }}</template>
					</div>
				</div>
			</div>

			<!-- 연차 계산 안내 (연차 신청 영역 대체) -->
			<div class="bg-base-200 p-6 rounded-lg shadow space-y-3">
				<h3 class="text-lg font-semibold mb-4">연차 계산 기준</h3>
				<template v-if="loading">
					<div v-for="i in 4" :key="i" class="skeleton h-[20px] w-2/3 bg-gray-700 animate-pulse rounded"></div>
				</template>
				<template v-else>
					<div
						v-for="item in leaveCalcInfo"
						:key="item.label"
						class="flex gap-4 text-sm"
					>
						<span class="text-gray-400 w-24 shrink-0">{{ item.label }}</span>
						<span>{{ item.value }}</span>
					</div>
				</template>
			</div>

			<!-- 연차 사용 내역 -->
			<div class="bg-base-200 p-6 rounded-lg shadow">
				<h3 class="text-lg font-semibold mb-4">연차 사용 내역</h3>
				<div class="overflow-x-auto">
					<table class="min-w-full text-sm border-collapse">
						<thead>
							<tr class="h-12">
								<th class="border border-gray-700 w-36">날짜</th>
								<th class="border border-gray-700">사유</th>
							</tr>
						</thead>
						<tbody>
							<template v-if="annualUseList.length > 0">
								<tr v-for="item in annualUseList" :key="item.id" class="h-12 text-center">
									<td class="border border-gray-700">{{ item.date }}</td>
									<td class="border border-gray-700 text-left px-3">{{ item.reason }}</td>
								</tr>
							</template>
							<tr v-else>
								<td colspan="2" class="text-center text-gray-400 py-6 border border-gray-700">
									연차 사용 내역이 없습니다.
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<MessageModal
		:show="showMessageModal"
		:message="message"
		:messageType="messageType"
		@close="showMessageModal = false"
	/>
</template>
