<script setup>
import { storeToRefs } from "pinia"
import { useCheckboxSelectStore } from "@/stores/checkboxSelect"
import { useMemberNavigationStore } from "@/stores/memberNavigation"
import { ref, watch, computed } from "vue"

import { useRoute } from "vue-router"
import MemberTable from "@/components/MemberTable.vue"
import MessageModal from "@/components/MessageModal.vue"
import ConfirmModal from "@/components/ConfirmModal.vue"

const route = useRoute()
const checkbox = useCheckboxSelectStore()
const nav = useMemberNavigationStore()
const { items, select, selectAll } = storeToRefs(checkbox)
const pagination = ref({ current_page: 1, last_page: 1 })
const loading = ref(false)
const memberStatus = ref("all")
const orderBy = ref({ field: "hire_date", direction: "desc" })

/**
 * 입사일 sort
 */
const onSort = (sort) => {
	orderBy.value = sort
	getMember(1)
}

/**
 * 사원리스트 데이터 획득
 */
const getMember = async (page = 1, status = "all") => {
    try {
        loading.value = true
		const params = { page }

        if (status !== "all") {
            params.status = status
        }

        if (orderBy.value?.field && orderBy.value?.direction) {
			params.orderBy = orderBy.value.field
			params.direction = orderBy.value.direction
		}

		const response = await api.get("/member", { params })

        if (response.status === 200) {
            items.value = response.data.member
            pagination.value = response.data.pagination
        } else {
            checkbox.message = response.data.message
            checkbox.messageType = "error"
            checkbox.showMessageModal = true
        }
    } catch (err) {
        checkbox.message = err.response.data.message
        checkbox.messageType = "error"
        checkbox.showMessageModal = true
    } finally {
        loading.value = false
    }
}

/**
 * 사원 추가 모달
 */
const openModal = ref(false)
const newMember = ref({
    affiliation: "스타",
    dept: "영업",
    name: "",
    hire_date: "",
})

const addAffiliationOptions = computed(() => {
    if (newMember.value.dept === "영업") return ["스타", "정보"]
    if (newMember.value.dept === "관리") return ["일반", "주말"]
    return []
})

const onAddDeptChange = () => {
    newMember.value.affiliation = addAffiliationOptions.value[0] ?? ""
}

/**
 * 사원 추가 확인 모달
 */
const showAddConfirm = ref(false)

const openAddConfirm = () => {
    if (!newMember.value.name) {
        checkbox.message = "사원명을 입력해주세요."
        checkbox.messageType = "warning"
        checkbox.showMessageModal = true
        return
    }
    if (!newMember.value.hire_date) {
        checkbox.message = "입사일을 선택해주세요."
        checkbox.messageType = "warning"
        checkbox.showMessageModal = true
        return
    }
    showAddConfirm.value = true
}

const addMember = async () => {
    showAddConfirm.value = false
    try {
        const response = await api.post("/member/add", {
            mb_affiliation: newMember.value.affiliation,
            mb_department: newMember.value.dept,
            mb_name: newMember.value.name.trim(),
            mb_hire_date: newMember.value.hire_date
        })

        if (response.status === 201) {
            checkbox.message = response.data.message
            checkbox.messageType = "success"
            checkbox.showMessageModal = true

            openModal.value = false
            await getMember()
            newMember.value = {
                affiliation: "스타",
                dept: "영업",
                name: "",
                hire_date: "",
            }
        } else {
            checkbox.message = response.data.message
            checkbox.messageType = "error"
            checkbox.showMessageModal = true
        }
    } catch (err) {
        checkbox.message = err.response.data.message
        checkbox.messageType = "error"
        checkbox.showMessageModal = true
    }
}

// 페이지 파라미터 설정
watch(
    () => [route.query.status, route.query.page],
    ([status, page]) => {
        memberStatus.value = status || nav.status || "all"
        const currentPage = Number(page) || nav.page || 1
        getMember(currentPage, memberStatus.value)
    },
    { immediate: true }
)

// 타이틀 설정
const memberList = computed(() => {
    switch (memberStatus.value) {
        case "employed":
            return "재직 사원"
        case "retired":
            return "퇴사 사원"
        default:
            return "전체 사원"
    }
})
</script>

<template>
    <div class="list p-4">
        <div class="flex justify-between items-center mb-2">
            <h2 class="text-lg font-semibold">{{ memberList }}</h2>
            <div class="flex gap-2 ml-auto">
                <button class="btn btn-primary" @click="openModal = true">사원 추가<img src="@/assets/icon/member_add.svg" alt="사원추가" class="w-6 h-6" /></button>
            </div>
        </div>

        <MemberTable
			:items="items"
			:loading="loading"
			:pagination="pagination"
			v-model:select="select"
			v-model:selectAll="selectAll"
			:onPageChange="(page) => getMember(page)"
            @sort="onSort"
		/>
    </div>
    <div class="mt-4 flex items-center gap-4">
        <button class="btn btn-success" @click="checkbox.mode = 'retire'; checkbox.editSelect()">
            선택 수정
        </button>
    </div>

    <dialog v-if="openModal" class="modal modal-open">
        <div class="modal-box rounded-2xl shadow-lg">
            <h3 class="text-xl font-bold mb-4 text-center">사원 추가</h3>
            <form class="space-y-4">
                <div class="form-control">
                    <label class="label font-semibold">부서</label>
                    <select v-model="newMember.dept" class="select select-bordered w-full" @change="onAddDeptChange">
                        <option value="영업">영업</option>
                        <option value="관리">관리</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label font-semibold">소속</label>
                    <select v-model="newMember.affiliation" class="select select-bordered w-full">
                        <option v-for="opt in addAffiliationOptions" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label font-semibold">사원명</label>
                    <input type="text" v-model="newMember.name" class="input input-bordered w-full" placeholder="사원명 입력"/>
                </div>
                <div class="form-control">
                    <label class="label font-semibold">입사일</label>
                    <input type="date" v-model="newMember.hire_date" class="input input-bordered w-full"/>
                </div>
                <div class="modal-action flex justify-end gap-2">
                    <button type="button" class="btn btn-primary" @click="openAddConfirm">추가</button>
                    <button type="button" class="btn btn-outline" @click="openModal = false">취소</button>
                </div>
            </form>
        </div>
    </dialog>

    <ConfirmModal
        :show="showAddConfirm"
        :title="`${newMember.name} 사원을 추가하시겠습니까?`"
        confirmText="추가"
        cancelText="취소"
        @confirm="addMember"
        @cancel="showAddConfirm = false"
    />

    <MessageModal
        :show="checkbox.showMessageModal"
        :message="checkbox.message"
        @close="checkbox.showMessageModal = false"
    />

</template>
