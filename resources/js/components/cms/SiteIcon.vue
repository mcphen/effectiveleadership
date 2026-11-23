<script setup lang="ts">
import data from '../../../data/icons.json';
import { computed } from 'vue';

/** Icône du site public (mêmes tracés que les gabarits Blade). */
const props = defineProps<{ name: string | null | undefined; size?: number }>();
const body = computed(() => (props.name ? (data.icons as Record<string, string>)[props.name] : '') ?? '');
</script>

<template>
    <svg
        v-if="body"
        xmlns="http://www.w3.org/2000/svg"
        :width="size ?? 20"
        :height="size ?? 20"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.75"
        stroke-linecap="round"
        stroke-linejoin="round"
        v-html="body"
    />
</template>
