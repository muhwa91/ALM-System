<script setup>
import { ref, onMounted } from "vue"

const items      = ref([])
const loading    = ref(false)
const typeFilter  = ref("")
const nameFilter  = ref("")
const startDate   = ref("")
const endDate     = ref("")
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

const getLog = async (page = 1) => {
	try {
		loading.value = true
		const params = { page }
		if (typeFilter.value)  params.type       = typeFilter.value
		if (nameFilter.value)  params.name       = nameFilter.value
		if (startDate.value)   params.start_date = startDate.value
		if (endDate.value)     params.end_date   = endDate.value

		const response = await api.get("/log", { params })

		if (response.status === 200) {
			items.value      = response.data.log
			pagination.value = response.data.pagination
		}
	} catch (err) {
		console.error(err)
	} finally {
		loading.value = false
	}
}

const goPage = (page) => {
	const safePage = Math.max(1, Math.min(page, pagination.value.last_page))
	getLog(safePage)
}

const onSearch = () => getLog(1)

const onReset = () => {
	typeFilter.value = ""
	nameFilter.value = ""
	startDate.value  = ""
	endDate.value    = ""
	getLog(1)
}

const typeBadgeClass = (type) => {
	const map = {
		"연차 등록":    "badge-info",
		"연차 수정":    "badge-primary",
		"연차 취소":    "badge-error",
		"사원 등록":    "badge-success",
		"퇴사사원 등록": "badge-warning",
	}
	return map[type] ?? "badge-ghost"
}

onMounted(() => {
	getLog()
})
</script>

<template>
	<div class="list p-4">
		<div class="flex justify-between items-center mb-4">
			<h2 class="text-lg font-semibold">로그 관리</h2>
		</div>

		<!-- 검색 영역 -->
		<div class="bg-base-200 rounded-lg p-4 mb-4 flex flex-wrap gap-3 items-end">
			<div class="form-control">
				<label class="label py-1"><span class="label-text text-xs">구분</span></label>
				<select v-model="typeFilter" class="select select-bordered select-sm w-36">
					<option value="">전체</option>
					<option value="연차 등록">연차 등록</option>
					<option value="연차 수정">연차 수정</option>
					<option value="연차 취소">연차 취소</option>
					<option value="사원 등록">사원 등록</option>
					<option value="퇴사사원 등록">퇴사사원 등록</option>
				</select>
			</div>
			<div class="form-control">
				<label class="label py-1"><span class="label-text text-xs">사원명</span></label>
				<input
					v-model="nameFilter"
					type="text"
					placeholder="사원명 검색"
					class="input input-bordered input-sm w-36"
					@keyup.enter="onSearch"
				/>
			</div>
			<div class="form-control">
				<label class="label py-1"><span class="label-text text-xs">시작일</span></label>
				<input v-model="startDate" type="date" class="input input-bordered input-sm w-40" />
			</div>
			<div class="form-control">
				<label class="label py-1"><span class="label-text text-xs">종료일</span></label>
				<input v-model="endDate" type="date" class="input input-bordered input-sm w-40" />
			</div>
			<div class="flex gap-2">
				<button class="btn btn-primary btn-sm" @click="onSearch">검색</button>
				<button class="btn btn-ghost btn-sm" @click="onReset">초기화</button>
			</div>
		</div>

		<p class="text-sm text-gray-400 mb-3">
			총 <span class="font-semibold text-gray-200">{{ pagination.total }}</span>건
		</p>

		<div class="overflow-x-auto">
			<table class="min-w-full text-sm border-collapse">
				<thead>
					<tr class="h-14">
						<th class="border w-28">구분</th>
						<th class="border w-36">사원명</th>
						<th class="border w-36">연차 관련 일자</th>
						<th class="border">내용</th>
						<th class="border w-44">처리일시</th>
					</tr>
				</thead>
				<tbody>
					<template v-if="loading">
						<tr v-for="i in 10" :key="'skeleton-' + i" class="h-12 text-center">
							<td v-for="col in 5" :key="col" class="border">
								<div class="skeleton h-[20px] w-3/4 mx-auto bg-gray-700 animate-pulse rounded"></div>
							</td>
						</tr>
					</template>
					<template v-else-if="items.length > 0">
						<tr v-for="log in items" :key="log.id" class="h-12 text-center hover:bg-white/5 transition-colors">
							<td class="border">
								<span class="badge badge-sm" :class="typeBadgeClass(log.type)">{{ log.type }}</span>
							</td>
							<td class="border">{{ log.name }}</td>
							<td class="border">{{ log.ref_date ?? '-' }}</td>
							<td class="border text-left px-3">{{ log.reason ?? '-' }}</td>
							<td class="border text-xs">{{ log.date ?? '-' }}</td>
						</tr>
					</template>
					<tr v-else>
						<td colspan="5" class="text-center text-gray-400 py-6 border">
							로그 데이터가 없습니다.
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="flex justify-center mt-4">
			<div v-if="loading" class="text-gray-400 text-center py-12">
				<span class="loading loading-spinner text-primary loading-xl"></span>
			</div>
			<div v-else-if="pagination.last_page > 1" class="join">
				<button class="join-item btn btn-square" @click="goPage(pagination.current_page - 1)">
					<img src="@/assets/icon/page_left.svg" alt="이전 페이지" class="w-6 h-6" />
				</button>
				<button
					v-for="page in pagination.last_page"
					:key="page"
					class="join-item btn btn-square"
					:class="{ 'btn-active': page === pagination.current_page }"
					@click="goPage(page)"
				>
					{{ page }}
				</button>
				<button class="join-item btn btn-square" @click="goPage(pagination.current_page + 1)">
					<img src="@/assets/icon/page_right.svg" alt="다음 페이지" class="w-6 h-6" />
				</button>
			</div>
		</div>
	</div>
</template>
