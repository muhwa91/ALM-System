<script setup>
import { RouterView, useRoute } from "vue-router"
import Header from "./components/layout/Header.vue"
import Sidebar from "./components/layout/Sidebar.vue"

const route = useRoute()
</script>

<template>
	<template v-if="route.name === 'login'">
		<RouterView />
	</template>
	<template v-else>
		<div class="flex flex-col h-screen">
			<Header class="flex-shrink-0" />
			<div class="flex flex-1 overflow-hidden">
				<Sidebar class="hidden md:block w-64 flex-shrink-0" />
				<main class="flex-1 overflow-y-auto p-4">
					<RouterView />
				</main>
			</div>
		</div>
	</template>
</template>
