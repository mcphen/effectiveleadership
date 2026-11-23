<script setup lang="ts">
const model = defineModel<string | number | null>();
defineProps<{ options: Record<string, string>; placeholder?: string; id?: string }>();
</script>

<template>
    <select
        :id="id"
        v-model="model"
        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-input/30 [&>option]:bg-background"
    >
        <option v-if="placeholder !== undefined" :value="null">{{ placeholder }}</option>
        <option v-for="(label, value) in options" :key="value" :value="value">{{ label }}</option>
    </select>
</template>
