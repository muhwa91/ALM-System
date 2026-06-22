import { defineStore } from "pinia"
import { ref } from "vue"

export const useMemberNavigationStore = defineStore("memberNavigation", () => {
	const status = ref(null)
	const page = ref(1)

	const setNavigation = (s, p) => {
		status.value = s
		page.value = p
	}

	return { status, page, setNavigation }
})
