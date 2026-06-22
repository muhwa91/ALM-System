<script setup>
import FullCalendar from "@fullcalendar/vue3"
import dayGridPlugin from "@fullcalendar/daygrid"
import koLocale from "@fullcalendar/core/locales/ko"
import { ref, computed } from "vue"
import MessageModal from "@/components/MessageModal.vue"
import ConfirmModal from "@/components/ConfirmModal.vue"

const calendarRef    = ref(null)
const holidayEvents  = ref([])
const leaveEvents    = ref([])

const selectedYear  = ref(new Date().getFullYear())
const selectedMonth = ref(new Date().getMonth() + 1)
const years = Array.from({ length: 11 }, (_, i) => 2020 + i)

// 이벤트 편집 모달
const showEditModal   = ref(false)
const editTarget      = ref({ id: null, name: "", date: "", reason: "", status: "" })
const editDate        = ref("")
const editReason      = ref("")

// 메시지 / 확인 모달
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

// 캘린더에 표시할 이벤트 (공휴일 + 연차)
const events = computed(() => [...holidayEvents.value, ...leaveEvents.value])

// 공휴일 로드
const loadHolidays = async (year, month) => {
	try {
		const res = await api.get("/holidays", { params: { year, month } })
		holidayEvents.value = (res.data.holidays || []).map(h => ({
			title: h.name,
			start: h.date,
			color: "#ef4444",
			extendedProps: { type: "holiday" },
		}))
	} catch (err) {
		console.error("공휴일 불러오기 실패:", err)
	}
}

// 연차 이벤트 로드
const loadLeaveEvents = async (year, month) => {
	try {
		const res = await api.get("/annual/events", { params: { year, month } })
		if (res.status === 200) {
			leaveEvents.value = res.data.events.map(e => ({
				title: `✔ ${e.name}`,
				start: e.date,
				color: "#3b82f6",
				textColor: "#fff",
				extendedProps: {
					type:   "leave",
					id:     e.id,
					name:   e.name,
					reason: e.reason ?? "",
					status: e.status,
				},
			}))
		}
	} catch (err) {
		console.error("연차 이벤트 불러오기 실패:", err)
	}
}

const loadCalendarData = (year, month) => {
	Promise.all([loadHolidays(year, month), loadLeaveEvents(year, month)])
}

const goToDate = () => {
	const calendarApi = calendarRef.value.getApi()
	const dateStr = `${selectedYear.value}-${String(selectedMonth.value).padStart(2, "0")}-01`
	calendarApi.gotoDate(dateStr)
	loadCalendarData(selectedYear.value, selectedMonth.value)
}

const prevMonth = () => {
	const calendarApi = calendarRef.value.getApi()
	calendarApi.prev()
	syncDate(calendarApi)
}

const nextMonth = () => {
	const calendarApi = calendarRef.value.getApi()
	calendarApi.next()
	syncDate(calendarApi)
}

const syncDate = (calendarApi) => {
	const date = calendarApi.getDate()
	selectedYear.value  = date.getFullYear()
	selectedMonth.value = date.getMonth() + 1
	loadCalendarData(selectedYear.value, selectedMonth.value)
}

// 이벤트 클릭 (연차만 처리)
const onEventClick = (info) => {
	const props = info.event.extendedProps
	if (props.type !== "leave") return

	editTarget.value = {
		id:     props.id,
		name:   props.name,
		date:   info.event.startStr,
		reason: props.reason,
		status: props.status,
	}
	editDate.value   = info.event.startStr
	editReason.value = props.reason
	showEditModal.value = true
}

const closeEditModal = () => {
	showEditModal.value = false
}

// 연차 날짜/사유 변경
const doSaveEdit = async () => {
	try {
		const res = await api.put(`/annual/use/${editTarget.value.id}`, {
			date:   editDate.value,
			reason: editReason.value,
		})
		if (res.status === 200) {
			showEditModal.value = false
			openMessageModal(res.data.message, "success")
			loadLeaveEvents(selectedYear.value, selectedMonth.value)
		}
	} catch (err) {
		openMessageModal(err.response?.data?.message ?? "변경 실패", "error")
	}
}

const saveEdit = () => {
	if (!editDate.value) {
		openMessageModal("날짜를 선택해주세요.", "error")
		return
	}
	const today = new Date().toISOString().split("T")[0]
	if (editDate.value < today) {
		openConfirm("과거 일자를 선택하였습니다. 연차를 변경하시겠습니까?", doSaveEdit)
		return
	}
	doSaveEdit()
}

// 연차 삭제
const deleteLeave = () => {
	openConfirm(`${editTarget.value.name}의 ${editTarget.value.date} 연차를 삭제하시겠습니까?`, async () => {
		try {
			const res = await api.delete(`/annual/use/${editTarget.value.id}`)
			if (res.status === 200) {
				showEditModal.value = false
				openMessageModal(res.data.message, "success")
				loadLeaveEvents(selectedYear.value, selectedMonth.value)
			}
		} catch (err) {
			openMessageModal(err.response?.data?.message ?? "삭제 실패", "error")
		}
	})
}

const calendarOptions = ref({
	plugins:             [dayGridPlugin],
	initialView:         "dayGridMonth",
	locale:              koLocale,
	headerToolbar:       false,
	fixedWeekCount:      false,
	showNonCurrentDates: false,
	events,
	eventClick: onEventClick,
	datesSet: (arg) => {
		const year  = arg.view.currentStart.getFullYear()
		const month = arg.view.currentStart.getMonth() + 1
		loadCalendarData(year, month)
	},
})
</script>

<template>
	<div>
		<div class="flex items-center gap-2 mb-1 text-xs text-gray-400 justify-end pr-1">
			<span class="inline-block w-3 h-3 rounded-sm bg-red-500"></span> 공휴일
			<span class="inline-block w-3 h-3 rounded-sm bg-blue-500 ml-2"></span> 연차
		</div>

		<div class="calendar-header flex items-center justify-center gap-3 mb-4">
			<button
				class="px-3 py-1 rounded-lg bg-gray-700 text-white hover:bg-gray-600 transition"
				@click="prevMonth"
			>◀</button>

			<select
				v-model="selectedYear"
				class="px-2 py-1 rounded-lg border border-gray-600 bg-gray-800 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
				@change="goToDate"
			>
				<option v-for="y in years" :key="y" :value="y">{{ y }}</option>
			</select>

			<select
				v-model="selectedMonth"
				class="px-2 py-1 rounded-lg border border-gray-600 bg-gray-800 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
				@change="goToDate"
			>
				<option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
			</select>

			<button
				class="px-3 py-1 rounded-lg bg-gray-700 text-white hover:bg-gray-600 transition"
				@click="nextMonth"
			>▶</button>
		</div>

		<FullCalendar ref="calendarRef" :options="calendarOptions" />
	</div>

	<!-- 연차 편집 모달 -->
	<dialog v-if="showEditModal" class="modal modal-open" @click.self="closeEditModal">
		<div class="modal-box rounded-xl shadow-lg">
			<h3 class="font-bold text-lg mb-4">연차 수정 — {{ editTarget.name }}</h3>
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
			<div class="modal-action flex justify-between mt-4">
				<button class="btn btn-error btn-sm" @click="deleteLeave">삭제</button>
				<div class="flex gap-2">
					<button class="btn btn-primary btn-sm" @click="saveEdit">저장</button>
					<button class="btn btn-ghost btn-sm" @click="closeEditModal">취소</button>
				</div>
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
