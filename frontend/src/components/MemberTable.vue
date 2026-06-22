<script setup>
import { ref } from "vue"
import sortAsc from "@/assets/icon/sort_asc.svg"
import sortDesc from "@/assets/icon/sort_desc.svg"
import { useRoute, useRouter } from "vue-router"
import { useMemberNavigationStore } from "@/stores/memberNavigation"
const nav = useMemberNavigationStore()

const route = useRoute()
const router = useRouter()

const props = defineProps({
	items: Array,
	loading: Boolean,
	pagination: Object,
	select: Array,
	selectAll: Boolean,
	onPageChange: Function
})

const emit = defineEmits([
	"update:select",
	"update:selectAll",
    "sort"
])

const orderBy = ref({
	field: "hire_date",
	direction: "desc"
})

const toggleSort = () => {
	orderBy.value.direction =
		orderBy.value.direction === "asc" ? "desc" : "asc"
	emit("sort", orderBy.value)
}

const goPage = (page) => {
	const safePage = Math.max(1, Math.min(page, props.pagination.last_page))

	const status = route.query.status || "all"
	router.push({
		path: "/member",
		query: {
			status,
			page: safePage
		}
	})

	props.onPageChange(safePage)
}
</script>

<template>
	<div class="overflow-x-auto">
		<table class="min-w-full text-sm border-collapse">
			<thead>
                <tr class="h-14">
                    <th class="border w-12">
                        <input
							class="checkbox checkbox-error"
                            type="checkbox"
                            :checked="props.selectAll"
                            @change="e => {
								const checked = e.target.checked
								if (checked) {
									emit('update:select', props.items.map(i => i.id))
								} else {
									emit('update:select', [])
								}
								emit('update:selectAll', checked)
							}"
                        />
                    </th>
                    <th class="border w-32">부서</th>
                    <th class="border w-32">소속</th>
                    <th class="border w-40">사원명</th>
                    <th class="border w-40 cursor-pointer select-none text-center" @click="toggleSort">
						<div class="flex items-center justify-center gap-1">
							<span>입사일</span>
							<img :src="orderBy.direction === 'asc' ? sortAsc : sortDesc" class="w-4 h-4"/>
						</div>
                    </th>                    
                    <th class="border w-48">퇴사일</th>
                </tr>
            </thead>
            <tbody>
                <template v-if="props.loading">
                    <tr v-for="i in 10" :key="'skeleton-'+i" class="h-12 text-center">
						<td class="border border-gray-700 h-12">
							<input
								type="checkbox"
								class="checkbox checkbox-error animate-pulse"
								disabled
							/>
						</td>
						<td class="border border-gray-700 h-12">
							<div class="skeleton h-[20px] w-3/5 mx-auto bg-gray-700 animate-pulse rounded"></div>
						</td>
						<td class="border border-gray-700 h-12">
                            <div class="skeleton h-[20px] w-3/5 mx-auto bg-gray-700 animate-pulse rounded"></div>
                        </td>  
                        <td class="border border-gray-700 h-12">
                            <div class="skeleton h-[20px] w-4/5 mx-auto bg-gray-700 animate-pulse rounded"></div>
                        </td>
                        <td class="border border-gray-700 h-12">
                            <div class="skeleton h-[20px] w-3/4 mx-auto bg-gray-700 animate-pulse rounded"></div>
                        </td>
                        <td class="border border-gray-700 h-12">
                            <div class="skeleton h-[38px] w-4/5 mx-auto bg-gray-700 animate-pulse rounded border"></div>
                        </td>
                    </tr>
                </template>

				<template v-else-if="props.items.length > 0">
					<tr
						v-for="m in props.items"
						:key="m.id"
						class="h-12 text-center transition-colors duration-150"
						:class="m.retire_date && route.query.status === 'all'
							? 'bg-red-100 text-red-900 hover:bg-red-200'
							: 'hover:bg-white/80 hover:text-black'"
					>
						<td class="border border-gray-700">
							<input
								class="checkbox checkbox-error"
								type="checkbox"
								:value="m.id"
								:checked="props.select.includes(m.id)"
								@change="e => {
									const checked = e.target.checked
									let newValue = [...props.select]
									if (checked) {
										newValue.push(m.id)
									} else {
										newValue = newValue.filter(id => id !== m.id)
									}
									emit('update:select', newValue)
								}"
							/>
						</td>
						<td
							v-for="col in ['dept', 'affiliation', 'name', 'hire_date']"
							:key="col"
							class="border border-gray-700 cursor-pointer"
							@click="() => {
								const status = route.query.status || 'all'
								const page = route.query.page || 1
								nav.setNavigation(status, page)
								router.push({ path: '/member/info', query: { id: m.id } })
							}"
						>
							{{ m[col] }}
						</td>
						<td class="border border-gray-700">
							<input type="date" class="border rounded px-2 py-1" v-model="m.retire_date" />
						</td>
					</tr>
				</template>
				<tr v-else>
					<td colspan="6" class="text-center text-gray-400 py-6 border border-gray-700">
						사원이 없습니다.
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="flex justify-center mt-4">
		<div v-if="props.loading" class="text-gray-400 text-center py-12">
			<span class="loading loading-spinner text-primary loading-xl"></span>
		</div>
		<div v-else-if="props.pagination.last_page > 1" class="join">
			<button
				class="join-item btn btn-square"
				@click="goPage(props.pagination.current_page - 1)"
			>
			<img src="@/assets/icon/page_left.svg" alt="이전 페이지" class="w-6 h-6" />
			</button>
			<button
				v-for="page in props.pagination.last_page"
				:key="page"
				class="join-item btn btn-square"
				:class="{ 'btn-active': page === props.pagination.current_page }"
				@click="goPage(page)"
			>
				{{ page }}
			</button>
			<button
				class="join-item btn btn-square"
				@click="goPage(props.pagination.current_page + 1)"
			>
			<img src="@/assets/icon/page_right.svg" alt="이전 페이지" class="w-6 h-6" />
			</button>
		</div>
	</div>
</template>
