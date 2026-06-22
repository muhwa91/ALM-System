<script setup>
import { storeToRefs } from "pinia"
import { useCheckboxSelectStore } from "@/stores/checkboxSelect"
import { ref, computed, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import MessageModal from "@/components/MessageModal.vue"
import { useMemberNavigationStore } from "@/stores/memberNavigation"

const nav      = useMemberNavigationStore()
const route    = useRoute()
const router   = useRouter()
const checkbox = useCheckboxSelectStore()
const { message, messageType, showMessageModal } = storeToRefs(checkbox)

const memberId = route.query.id

const member = ref({
	affiliation: "",
	dept: "",
	name: "",
	hire_date: "",
	retire_date: "",
})

const loading = ref(false)
const saving  = ref(false)
const orgMember = ref(null)

const affiliationOptions = computed(() => {
	if (member.value.dept === "영업") return ["스타", "정보"]
	if (member.value.dept === "관리") return ["일반", "주말"]
	return []
})

const onDeptChange = () => {
	member.value.affiliation = affiliationOptions.value[0] ?? ""
}

const goBack = () => {
	router.push({ path: "/member", query: { status: nav.status, page: nav.page } })
}

const fetchMember = async () => {
	try {
		loading.value = true
		const response = await api.get("/member/info", { params: { id: memberId } })
		if (response.status === 200) {
			member.value    = response.data.member
			orgMember.value = { ...member.value }
		} else {
			message.value = response.data.message
			messageType.value = "error"
			showMessageModal.value = true
		}
	} catch (err) {
		message.value = err.response.data.message
		messageType.value = "error"
		showMessageModal.value = true
	} finally {
		loading.value = false
	}
}

const isChanged = () => JSON.stringify(member.value) !== JSON.stringify(orgMember.value)

const updateMember = async () => {
	if (!member.value.hire_date) {
		message.value = "입사일은 필수 등록입니다."
		messageType.value = "error"
		showMessageModal.value = true
		return
	}
	if (member.value.hire_date && member.value.retire_date) {
		if (new Date(member.value.hire_date) > new Date(member.value.retire_date)) {
			message.value = "퇴사일은 입사일과 같거나 늦어야 합니다."
			messageType.value = "error"
			showMessageModal.value = true
			return
		}
	}

	try {
		saving.value = true
		const response = await api.put("/member/info", {
			id:          member.value.id,
			affiliation: member.value.affiliation,
			dept:        member.value.dept,
			name:        member.value.name.trim(),
			hire_date:   member.value.hire_date,
			retire_date: member.value.retire_date
		})
		if (response.status === 200) {
			message.value = response.data.message
			messageType.value = "success"
			showMessageModal.value = true
			goBack()
		} else {
			message.value = response.data.message
			messageType.value = "error"
			showMessageModal.value = true
		}
	} catch (err) {
		message.value = err.response.data.message
		messageType.value = "error"
		showMessageModal.value = true
	} finally {
		saving.value = false
	}
}

onMounted(fetchMember)
</script>

<template>
	<div class="max-w-3xl mx-auto p-10">
        <h2 class="text-2xl font-bold mb-10 text-center">{{ member.name }} 사원 정보</h2>
        <div v-if="loading" class="text-gray-400 text-center py-12">
			<span class="loading loading-spinner text-primary loading-xl"></span>
		</div>
        <div v-else class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium mb-2">부서</span>
                    </label>
                    <select v-model="member.dept" class="select select-bordered w-full" @change="onDeptChange">
                        <option value="영업">영업</option>
                        <option value="관리">관리</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium mb-2">소속</span>
                    </label>
                    <select v-model="member.affiliation" class="select select-bordered w-full">
                        <option v-for="opt in affiliationOptions" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium mb-2">사원명</span>
                    </label>
                    <input v-model="member.name" type="text" class="input input-bordered w-full" />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium mb-2">입사일</span>
                    </label>
                    <input v-model="member.hire_date" type="date" class="input input-bordered w-full" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium mb-2">퇴사일</span>
                    </label>
                    <input v-model="member.retire_date" type="date" class="input input-bordered w-full" />
                </div>
            </div>
            <div class="mt-12 flex justify-center gap-3">
                <button class="btn btn-primary" :disabled="saving || !isChanged()" @click="updateMember">
					<span v-if="saving" class="loading loading-spinner"></span>
					<span v-else>저장</span>
				</button>
                <button class="btn btn-outline" @click="goBack">취소</button>
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
