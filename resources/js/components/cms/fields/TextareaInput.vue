<script setup lang="ts">
const model = defineModel<string | null>();
defineProps<{ rows?: number; placeholder?: string; id?: string }>();
</script>

<template>
    <textarea
        :id="id"
        v-model="model"
        :rows="rows ?? 3"
        :placeholder="placeholder"
        class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground/70 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-input/30"
    />
</template>
