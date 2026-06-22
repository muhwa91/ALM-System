<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import MessageModal from "@/components/MessageModal.vue"
import ConfirmModal from "@/components/ConfirmModal.vue"

const router = useRouter()

const annuals = ref([])
const loading = ref(false)
const pagination = ref({
	current_page: 1,
	last_page: 1,
	total: 0,
	per_page: 10,
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

const calcAnnualLeave = async (silent = false) => {
	try {
		loading.value = true
		const response = await api.post("/annual/calculate", { page: 1 })

		if (response.status === 200) {
			annuals.value = response.data.data
			pagination.value = response.data.pagination
			if (!silent) openMessageModal(response.data.message, "success")
		} else {
			if (!silent) openMessageModal(response.data.message, "error")
		}
	} catch (err) {
		if (!silent) openMessageModal(err.response?.data?.message, "error")
	} finally {
		loading.value = false
	}
}

const goPage = async (page) => {
	if (page < 1 || page > pagination.value.last_page) return
	try {
		loading.value = true
		const response = await api.get(`/annual/list?page=${page}`)
		if (response.status === 200) {
			annuals.value = response.data.data
			pagination.value = response.data.pagination
		}
	} catch (err) {
		openMessageModal(err.response?.data?.message, "error")
	} finally {
		loading.value = false
	}
}

const deleteAnnual = (id, name) => {
	openConfirm(`${name}의 연차 데이터를 삭제하시겠습니까?\n관련 신청 내역도 함께 삭제됩니다.`, async () => {
		try {
			const res = await api.delete(`/annual/${id}`)
			if (res.status === 200) {
				openMessageModal(res.data.message, "success")
				calcAnnualLeave(true)
			}
		} catch (err) {
			openMessageModal(err.response?.data?.message ?? "삭제 실패", "error")
		}
	})
}

onMounted(() => {
	calcAnnualLeave(true)
})
</script>


<template>
    <div class="list p-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-lg font-semibold">연차 현황</h2>
            <div class="flex gap-2 ml-auto">
                <button class="btn btn-secondary" @click="calcAnnualLeave">연차 계산</button>
            </div>
        </div>
        <p class="text-sm mb-4">
            ※ 연차 계산은 <span class="font-semibold text-gray-300">재직 사원</span> 기준으로 계산되며, <span class="font-semibold text-gray-300">입사일</span> 순으로 보여집니다.
        </p>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
                <thead>
                    <tr class="h-14">
                        <th class="border w-32">소속</th>
                        <th class="border w-32">부서</th>
                        <th class="border w-40">이름</th>
                        <th class="border w-32">총 연차</th>
                        <th class="border w-32">사용 연차</th>
                        <th class="border w-32">잔여 연차</th>
                        <th class="border w-24">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="loading">
						<tr v-for="i in 10" :key="'skeleton-'+i" class="h-12 text-center">
							<td v-for="col in 7" :key="col" class="border border-gray-700 h-12">
								<div class="skeleton h-[20px] w-3/5 mx-auto bg-gray-700 animate-pulse rounded"></div>
							</td>
						</tr>
					</template>
					<template v-else-if="annuals.length > 0">
						<tr
							v-for="m in annuals"
							:key="m.id"
							class="h-12 text-center transition-colors duration-150 hover:bg-white/5 cursor-pointer"
						>
							<td class="border border-gray-700" @click="router.push({ name: 'annualDetail', params: { id: m.id } })">{{ m.affiliation }}</td>
							<td class="border border-gray-700" @click="router.push({ name: 'annualDetail', params: { id: m.id } })">{{ m.dept }}</td>
							<td class="border border-gray-700" @click="router.push({ name: 'annualDetail', params: { id: m.id } })">{{ m.name }}</td>
							<td class="border border-gray-700" @click="router.push({ name: 'annualDetail', params: { id: m.id } })">{{ m.total_leave }}</td>
							<td class="border border-gray-700" @click="router.push({ name: 'annualDetail', params: { id: m.id } })">{{ m.used_leave }}</td>
							<td class="border border-gray-700" @click="router.push({ name: 'annualDetail', params: { id: m.id } })">{{ m.remain_leave }}</td>
							<td class="border border-gray-700">
								<button
									class="btn btn-xs btn-error"
									@click.stop="deleteAnnual(m.id, m.name)"
								>삭제</button>
							</td>
						</tr>
					</template>
					<tr v-else>
						<td colspan="7" class="text-center text-gray-400 py-6 border border-gray-700">
							데이터가 없습니다.
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
