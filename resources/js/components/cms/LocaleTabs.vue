<script setup lang="ts">
import { locales } from '@/lib/cms';
import { useEditLocale } from '@/composables/useEditLocale';

/** Onglets « Français / English » : choisit la langue des champs traduisibles. */
defineProps<{ missing?: Record<string, number> }>();
const locale = useEditLocale();
const list = locales();
</script>

<template>
    <div class="inline-flex items-center rounded-lg border bg-muted/50 p-1 text-sm" role="tablist" aria-label="Langue d'édition">
        <button
            v-for="(label, code) in list"
            :key="code"
            type="button"
            role="tab"
            :aria-selected="locale === code"
            class="relative flex items-center gap-2 rounded-md px-3 py-1.5 font-medium transition"
            :class="locale === code ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
            @click="locale = code as string"
        >
            <span class="rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary">{{ code }}</span>
            {{ label }}
            <span
                v-if="missing && missing[code as string]"
                class="ml-1 rounded-full bg-amber-500/15 px-1.5 text-[11px] font-semibold text-amber-700 dark:text-amber-400"
                :title="`${missing[code as string]} champ(s) à traduire`"
                >{{ missing[code as string] }}</span
            >
        </button>
    </div>
</template>
