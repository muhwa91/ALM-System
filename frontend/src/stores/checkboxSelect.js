import { defineStore } from "pinia"
import { ref, watch } from "vue"

export const useCheckboxSelectStore = defineStore("checkboxSelect", () => {
    const items = ref([])
    const select = ref([])
    const selectAll = ref(false)

    // 기본 정렬
    const orderBy = ref({ field: "dept", direction: "asc" })

    // 전체선택 토글
    const toggleSelectAll = () => {
        if (selectAll.value) {
            select.value = items.value.map(item => item.id)
        } else {
            select.value = []
        }
    }

    // 개별 선택 → 전체 선택 자동 연동
    watch(select, (val) => {
        if (val.length === items.value.length && items.value.length > 0) {
            selectAll.value = true
        } else {
            selectAll.value = false
        }
    })

    // 선택수정 상태값 설정
    const mode = ref("")

    // 메시지 모달 상태
    const message = ref("")
    const messageType = ref("info")
    const showMessageModal = ref(false)

    // 선택수정
    const editSelect = async () => {
        if (select.value.length === 0) {
            message.value = "선택된 항목이 없습니다."
            messageType.value = "error"
            showMessageModal.value = true
            return
        }
    
        const selectedItems = items.value.filter(m => select.value.includes(m.id))

        // 입사일, 퇴사일 비교
        for (const m of selectedItems) {
            if (m.hire_date && m.retire_date) {
                const hire = new Date(m.hire_date)
                const retire = new Date(m.retire_date)
    
                if (hire > retire) {
                    message.value = `퇴사일은 입사일과 같거나 늦어야 합니다.\n입사일 : ${m.hire_date}\n퇴사일 : ${m.retire_date}`
                    messageType.value = "error"
                    showMessageModal.value = true
                    return
                }
            }
        }
    
        let payload = {}
        let endpoint = ""
    
        if (mode.value === "retire") {
            endpoint = "/member/update-retire"
            payload = {
                retire_data: selectedItems.map(m => ({
                    id: m.id,
                    retire_date: m.retire_date || null
                }))
            }
        }
    
        try {
            const response = await api.post(endpoint, payload)
        
            if (response.status === 200) {
                message.value = response.data.message
                messageType.value = response.data.status
            }
        } catch (err) {
            if (err.response) {
                message.value = err.response.data.message
                messageType.value = "error"
            } else {
                message.value = "네트워크 오류가 발생했습니다."
                messageType.value = "error"
            }
        } finally {
            select.value = []
            selectAll.value = false
            showMessageModal.value = true
        }
    }    

    // 정렬 실행
    const sortItems = (field) => {
        if (orderBy.value.field == field) {
            orderBy.value.direction = orderBy.value.direction == "asc" ? "desc" : "asc"
        } else {
            orderBy.value.field = field
            orderBy.value.direction = "asc"
        }

        items.value.sort((a, b) => {
            let x = a[field]
            let y = b[field]

            if (field.includes("date")) {
                x = x ? new Date(x) : new Date(0)
                y = y ? new Date(y) : new Date(0)
            }

            if (x < y) return orderBy.value.direction == "asc" ? -1 : 1
            if (x > y) return orderBy.value.direction == "asc" ? 1 : -1
            return 0
        })
    }

    return {
        items,
        select,
        selectAll,
        orderBy,
        mode,
        toggleSelectAll,
        editSelect,
        sortItems,
        message,
        messageType,
        showMessageModal,
    }
})
