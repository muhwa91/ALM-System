<script setup>
import { ref, watch, onMounted } from "vue"
import MessageModal from "@/components/MessageModal.vue"
import ConfirmModal from "@/components/ConfirmModal.vue"

const loading = ref(false)
const members = ref([])
const selectedId = ref("")
const annualUse = ref({ date: "", reason: "" })
const annualUseList = ref([])
const member = ref({
	id: "",
	affiliation: "",
	dept: "",
	name: "",
	hire_date: "",
	total_leave: 0,
	used_leave: 0,
	remain_leave: 0,
})

const showMessageModal = ref(false)
const message = ref("")
const messageType = ref("")

const showConfirmModal = ref(false)
const confirmTitle = ref("")
const pendingAction = ref(null)

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

// 사원 목록 조회
const fetchMembers = async () => {
	try {
		const response = await api.get("/annual/member-list")
		if (response.status === 200) {
			members.value = response.data.member
		}
	} catch (err) {
		console.error(err)
	}
}

// 특정 사원의 연차 현황 조회
const fetchMemberAnnual = async (id) => {
	if (!id) return
	try {
		loading.value = true
		const response = await api.post("/annual/info", { id })
		if (response.status === 200) {
			member.value = response.data.member
		}
	} catch (err) {
		openMessageModal("연차 현황 불러오기 실패", "error")
	} finally {
		loading.value = false
	}
}

// 연차 사용 내역 조회
const fetchAnnualUseList = async (id) => {
	if (!id) return
	try {
		const response = await api.get("/annual/use", { params: { mb_id: id } })
		if (response.status === 200) {
			annualUseList.value = response.data.data
		}
	} catch (err) {
		console.error(err)
	}
}

// 연차 등록
const saveAnnualUse = () => {
	if (!selectedId.value) {
		openMessageModal("사원을 선택하세요.", "error")
		return
	}
	if (!annualUse.value.date) {
		openMessageModal("연차 사용일을 선택하세요.", "error")
		return
	}
	openConfirm("연차를 등록하시겠습니까?", async () => {
		try {
			const res = await api.post("/annual/use", {
				mb_id:  selectedId.value,
				date:   annualUse.value.date,
				reason: annualUse.value.reason,
			})
			if (res.status === 200) {
				openMessageModal(res.data.message, "success")
				fetchMemberAnnual(selectedId.value)
				fetchAnnualUseList(selectedId.value)
				annualUse.value = { date: "", reason: "" }
			}
		} catch (err) {
			openMessageModal(err.response?.data?.message ?? "연차 등록 실패", "error")
		}
	})
}

// 수정 모달
const showEditModal = ref(false)
const editItem      = ref({ id: null, date: "", reason: "" })
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
			fetchAnnualUseList(selectedId.value)
		}
	} catch (err) {
		openMessageModal(err.response?.data?.message ?? "수정 실패", "error")
	}
}

// 삭제
const deleteUse = (id) => {
	openConfirm("연차를 삭제하시겠습니까?", async () => {
		try {
			const res = await api.delete(`/annual/use/${id}`)
			if (res.status === 200) {
				openMessageModal(res.data.message, "success")
				fetchMemberAnnual(selectedId.value)
				fetchAnnualUseList(selectedId.value)
			}
		} catch (err) {
			openMessageModal(err.response?.data?.message ?? "삭제 실패", "error")
		}
	})
}

watch(selectedId, (id) => {
	if (id) {
		fetchMemberAnnual(id)
		fetchAnnualUseList(id)
	} else {
		annualUseList.value = []
	}
})

onMounted(fetchMembers)
</script>


<template>
	<div class="max-w-4xl mx-auto p-10">
		<h2 class="text-2xl font-bold mb-10 text-center">연차 등록</h2>
		<div class="form-control mb-8">
			<label class="label">
				<span class="label-text font-medium mb-2">사원명 선택</span>
			</label>
			<select v-model="selectedId" class="select select-bordered w-full">
				<option disabled value="">사원을 선택하세요</option>
				<option v-for="m in members" :key="m.id" :value="m.id">{{ m.name }}</option>
			</select>
		</div>
		<div class="space-y-10">
			<div class="grid grid-cols-2 gap-4">
				<div class="form-control">
					<label class="label">
						<span class="label-text font-medium mb-2">소속</span>
					</label>
					<p v-if="loading" class="input input-bordered w-full bg-base-100 cursor-default">
						<div class="skeleton h-[20px] w-3/4 mx-auto bg-gray-700 animate-pulse rounded"></div>
					</p>
					<p v-else class="input input-bordered w-full bg-base-100 cursor-default">
						{{ member.affiliation }}
					</p>
				</div>
				<div class="form-control">
					<label class="label">
						<span class="label-text font-medium mb-2">부서</span>
					</label>
					<p v-if="loading" class="input input-bordered w-full bg-base-100 cursor-default">
						<div class="skeleton h-[20px] w-3/4 mx-auto bg-gray-700 animate-pulse rounded"></div>
					</p>
					<p v-else class="input input-bordered w-full bg-base-100 cursor-default">
						{{ member.dept }}
					</p>
				</div>
				<div class="form-control">
					<label class="label">
						<span class="label-text font-medium mb-2">입사일</span>
					</label>
					<p v-if="loading" class="input input-bordered w-full bg-base-100 cursor-default">
						<div class="skeleton h-[20px] w-3/4 mx-auto bg-gray-700 animate-pulse rounded"></div>
					</p>
					<p v-else class="input input-bordered w-full bg-base-100 cursor-default">
						{{ member.hire_date }}
					</p>
				</div>
			</div>

			<div class="grid grid-cols-3 gap-4">
				<div class="stat bg-base-200 rounded-lg shadow p-4 text-center">
					<div class="stat-title">총 연차</div>
					<div class="stat-value">
						<template v-if="loading">
							<span class="loading loading-spinner loading-lg text-primary"></span>
						</template>
						<template v-else>{{ member.total_leave }}</template>
					</div>
				</div>
				<div class="stat bg-base-200 rounded-lg shadow p-4 text-center">
					<div class="stat-title">사용 연차</div>
					<div class="stat-value">
						<template v-if="loading">
							<span class="loading loading-spinner loading-lg text-primary"></span>
						</template>
						<template v-else>{{ member.used_leave }}</template>
					</div>
				</div>
				<div class="stat bg-base-200 rounded-lg shadow p-4 text-center">
					<div class="stat-title">잔여 연차</div>
					<div class="stat-value">
						<template v-if="loading">
							<span class="loading loading-spinner loading-lg text-primary"></span>
						</template>
						<template v-else>{{ member.remain_leave }}</template>
					</div>
				</div>
			</div>

			<div class="bg-base-200 p-6 rounded-lg shadow space-y-4">
				<h3 class="text-lg font-semibold mb-4">연차 등록</h3>
				<div class="grid grid-cols-2 gap-4">
					<div class="form-control col-span-2">
						<label class="label">
							<span class="label-text font-medium mb-2">연차 사용일</span>
						</label>
						<input v-model="annualUse.date" type="date" class="input input-bordered w-full" />
					</div>
					<div class="form-control col-span-2">
						<label class="label">
							<span class="label-text font-medium mb-2">연차 사유</span>
						</label>
						<textarea
							v-model="annualUse.reason"
							class="textarea textarea-bordered w-full"
							rows="3"
							placeholder="연차 사유를 입력하세요">
						</textarea>
					</div>
				</div>
				<div class="flex justify-end">
					<button class="btn btn-primary" @click="saveAnnualUse">연차 등록</button>
				</div>
			</div>

			<!-- 연차 사용 내역 -->
			<div v-if="selectedId" class="bg-base-200 p-6 rounded-lg shadow">
				<h3 class="text-lg font-semibold mb-4">연차 사용 내역</h3>
				<div class="overflow-x-auto">
					<table class="min-w-full text-sm border-collapse">
						<thead>
							<tr class="h-12">
								<th class="border border-gray-700 w-36">날짜</th>
								<th class="border border-gray-700">사유</th>
								<th class="border border-gray-700 w-36">처리</th>
							</tr>
						</thead>
						<tbody>
							<template v-if="annualUseList.length > 0">
								<tr v-for="item in annualUseList" :key="item.id" class="h-12 text-center">
									<td class="border border-gray-700">{{ item.date }}</td>
									<td class="border border-gray-700 text-left px-3">{{ item.reason }}</td>
									<td class="border border-gray-700">
										<button class="btn btn-xs btn-info mr-1" @click="openEdit(item)">수정</button>
										<button class="btn btn-xs btn-ghost" @click="deleteUse(item.id)">삭제</button>
									</td>
								</tr>
							</template>
							<tr v-else>
								<td colspan="3" class="text-center text-gray-400 py-6 border border-gray-700">
									연차 사용 내역이 없습니다.
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<!-- 수정 모달 -->
	<dialog v-if="showEditModal" class="modal modal-open" @click.self="showEditModal = false">
		<div class="modal-box rounded-xl shadow-lg">
			<h3 class="font-bold text-lg mb-4">연차 수정</h3>
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
