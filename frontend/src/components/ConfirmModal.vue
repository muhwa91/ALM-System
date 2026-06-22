<script setup>
defineProps({
	show: Boolean,
	title: {
		type: String,
		default: "확인 필요"
	},
	confirmText: {
		type: String,
		default: "확인"
	},
	cancelText: {
		type: String,
		default: "취소"
	}
})

defineEmits(["confirm", "cancel"])
</script>

<template>
	<dialog v-if="show" class="modal modal-open" @click.self="$emit('cancel')">
		<div class="modal-box rounded-xl shadow-lg">
			<h3 class="font-bold text-lg text-center mb-2">{{ title }}</h3>
			<div class="modal-action justify-center gap-3">
				<button class="btn btn-primary" @click="$emit('confirm')">
					{{ confirmText }}
				</button>
				<button class="btn" @click="$emit('cancel')">
					{{ cancelText }}
				</button>
			</div>
		</div>
	</dialog>
</template>