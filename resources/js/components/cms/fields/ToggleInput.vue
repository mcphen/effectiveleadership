<script setup lang="ts">
const model = defineModel<boolean>({ default: false });
defineProps<{ label?: string; id?: string }>();
</script>

<template>
    <label class="inline-flex cursor-pointer items-center gap-3 select-none">
        <button
            :id="id"
            type="button"
            role="switch"
            :aria-checked="!!model"
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full border-2 border-transparent transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            :class="model ? 'bg-primary' : 'bg-input'"
            @click="model = !model"
        >
            <span class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg transition" :class="model ? 'translate-x-5' : 'translate-x-0'" />
        </button>
        <span v-if="label" class="text-sm">{{ label }}</span>
    </label>
</template>
