<script setup>
import { ref, onMounted } from "vue"
import MessageModal from "@/components/MessageModal.vue"
import ConfirmModal from "@/components/ConfirmModal.vue"

const items      = ref([])
const loading    = ref(false)
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

const statusFilter = ref("")
const nameFilter   = ref("")
const startDate    = ref("")
const endDate      = ref("")

const showMessageModal = ref(false)
const message          = ref("")
const messageType      = ref("")

const showConfirmModal = ref(false)
const confirmTitle     = ref("")
const pendingAction    = ref(null)

const openMessageModal = (msg, type = "info") => {
	message.value = msg
	messageType.value = type
	showMessageModal.value = true
}

const openConfirm = (title, action) => {
	confirmTitle.value = title
	pendingAction.value = action
	showConfirmModal.value = true
}

const onConfirm = () => {
	showConfirmModal.value = false
	if (pendingAction.value) pendingAction.value()
}

const fetchList = async (page = 1) => {
	try {
		loading.value = true
		const params = { page }
		if (statusFilter.value) params.status     = statusFilter.value
		if (nameFilter.value)   params.name       = nameFilter.value
		if (startDate.value)    params.start_date = startDate.value
		if (endDate.value)      params.end_date   = endDate.value

		const res = await api.get("/annual/use/all", { params })
		if (res.status === 200) {
			items.value      = res.data.data
			pagination.value = res.data.pagination
		}
	} catch (err) {
		openMessageModal(err.response?.data?.message ?? "조회 실패", "error")
	} finally {
		loading.value = false
	}
}

const goPage = (page) => {
	const safe = Math.max(1, Math.min(page, pagination.value.last_page))
	fetchList(safe)
}

const onSearch = () => fetchList(1)

const onReset = () => {
	statusFilter.value = ""
	nameFilter.value   = ""
	startDate.value    = ""
	endDate.value      = ""
	fetchList(1)
}

// 수정 모달
const showEditModal = ref(false)
const editItem      = ref({ id: null, name: "", date: "", reason: "" })
const editDate      = ref("")
const editReason    = ref("")

const openEdit = (item) => {
	editItem.value   = { ...item }
	editDate.value   = item.date
	editReason.value = item.reason
	showEditModal.value = true
}

const saveEdit = async () => {
	if (!editDate.value) {
		openMessageModal("날짜를 선택해주세요.", "error")
		return
	}
	try {
		const res = await api.put(`/annual/use/${editItem.value.id}`, {
			date:   editDate.value,
			reason: editReason.value,
		})
		if (res.status === 200) {
			showEditModal.value = false
			openMessageModal(res.data.message, "success")
			fetchList(pagination.value.current_page)
		}
	} catch (err) {
		openMessageModal(err.response?.data?.message ?? "수정 실패", "error")
	}
}

// 취소(삭제)
const cancelUse = (id, name, date) => {
	openConfirm(`${name}의 ${date} 연차를 취소하시겠습니까?`, async () => {
		try {
			const res = await api.delete(`/annual/use/${id}`)
			if (res.status === 200) {
				openMessageModal(res.data.message, "success")
				fetchList(pagination.value.current_page)
			}
		} catch (err) {
			openMessageModal(err.response?.data?.message ?? "취소 실패", "error")
		}
	})
}

onMounted(() => fetchList())
</script>

<template>
	<div class="list p-4">
		<div class="flex justify-between items-center mb-4">
			<h2 class="text-lg font-semibold">연차 목록</h2>
		</div>

		<!-- 검색 영역 -->
		<div class="bg-base-200 rounded-lg p-4 mb-4 flex flex-wrap gap-3 items-end">
			<div class="form-control">
				<label class="label py-1"><span class="label-text text-xs">상태</span></label>
				<select v-model="statusFilter" class="select select-bordered select-sm w-28">
					<option value="">전체</option>
					<option value="approved">승인</option>
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
						<th class="border w-36">사원명</th>
						<th class="border w-36">날짜</th>
						<th class="border">사유</th>
						<th class="border w-36">처리</th>
					</tr>
				</thead>
				<tbody>
					<template v-if="loading">
						<tr v-for="i in 15" :key="'sk-' + i" class="h-12 text-center">
							<td v-for="col in 4" :key="col" class="border">
								<div class="skeleton h-[20px] w-3/4 mx-auto bg-gray-700 animate-pulse rounded"></div>
							</td>
						</tr>
					</template>
					<template v-else-if="items.length > 0">
						<tr v-for="item in items" :key="item.id" class="h-12 text-center hover:bg-white/5 transition-colors">
							<td class="border">{{ item.name }}</td>
							<td class="border">{{ item.date }}</td>
							<td class="border text-left px-3">{{ item.reason }}</td>
							<td class="border">
								<button
									class="btn btn-xs btn-info mr-1"
									@click="openEdit(item)"
								>수정</button>
								<button
									class="btn btn-xs btn-ghost"
									@click="cancelUse(item.id, item.name, item.date)"
								>삭제</button>
							</td>
						</tr>
					</template>
					<tr v-else>
						<td colspan="4" class="text-center text-gray-400 py-6 border">
							연차 신청 내역이 없습니다.
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

	<!-- 수정 모달 -->
	<dialog v-if="showEditModal" class="modal modal-open" @click.self="showEditModal = false">
		<div class="modal-box rounded-xl shadow-lg">
			<h3 class="font-bold text-lg mb-4">연차 수정 — {{ editItem.name }}</h3>
			<div class="space-y-4">
				<div class="form-control">
					<label class="label"><span class="label-text font-medium">날짜</span></label>
					<input v-model="editDate" type="date" class="input input-bordered w-full" />
				</div>
				<div class="form-control">
					<label class="label"><span class="label-text font-medium">사유</span></label>
					<textarea
						v-model="editReason"
						class="textarea textarea-bordered w-full"
						rows="3"
						placeholder="사유 입력"
					></textarea>
				</div>
			</div>
			<div class="modal-action flex justify-end gap-2 mt-4">
				<button class="btn btn-primary btn-sm" @click="saveEdit">저장</button>
				<button class="btn btn-ghost btn-sm" @click="showEditModal = false">취소</button>
			</div>
		</div>
	</dialog>

	<MessageModal
		:show="showMessageModal"
		:message="message"
		:messageType="messageType"
		@close="showMessageModal = false"
	/>
	<ConfirmModal
		:show="showConfirmModal"
		:title="confirmTitle"
		@confirm="onConfirm"
		@cancel="showConfirmModal = false"
	/>
</template>
