<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const site = computed(() => (usePage().props as any).site as { name: string; logo: string | null } | null);
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-[#156080] font-serif text-sm font-bold text-white">EL</div>
    <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-semibold">{{ site?.name ?? 'ELC' }}</span>
        <span class="truncate text-xs text-muted-foreground">Administration</span>
    </div>
</template>
