<script setup lang="ts">
import SiteIcon from '@/components/cms/SiteIcon.vue';
import { onClickOutside } from '@vueuse/core';
import { ChevronDown, X } from 'lucide-vue-next';
import { ref } from 'vue';
import data from '../../../../data/icons.json';

const model = defineModel<string | null>();
const open = ref(false);
const root = ref<HTMLElement>();
onClickOutside(root, () => (open.value = false));
const icons = data.pickable as string[];
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="flex h-9 w-full items-center gap-2 rounded-md border border-input bg-transparent px-3 text-sm shadow-sm hover:bg-accent/50 dark:bg-input/30"
            @click="open = !open"
        >
            <SiteIcon v-if="model" :name="model" :size="18" class="text-primary" />
            <span class="flex-1 truncate text-left" :class="model ? '' : 'text-muted-foreground'">{{ model ?? 'Aucune icône' }}</span>
            <X v-if="model" class="size-4 text-muted-foreground hover:text-foreground" @click.stop="model = null" />
            <ChevronDown class="size-4 text-muted-foreground" />
        </button>
        <div v-if="open" class="absolute left-0 z-40 mt-1 grid w-72 grid-cols-7 gap-1 rounded-lg border bg-popover p-2 shadow-xl">
            <button
                v-for="name in icons"
                :key="name"
                type="button"
                :title="name"
                class="flex aspect-square items-center justify-center rounded-md hover:bg-accent"
                :class="model === name ? 'bg-primary/10 text-primary ring-1 ring-primary' : ''"
                @click="
                    model = name;
                    open = false;
                "
            >
                <SiteIcon :name="name" :size="20" />
            </button>
        </div>
    </div>
</template>
