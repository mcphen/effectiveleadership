<script setup lang="ts">
import { useLinkTargets } from '@/composables/useLinkTargets';
import { onClickOutside } from '@vueuse/core';
import { ExternalLink, FileText, Link2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

/**
 * Lien : soit un contenu interne (« page:3 », « solution:5 »…), soit une adresse libre
 * (https://…, #ancre, mailto:, tel:). Les liens internes suivent automatiquement les changements d'adresse.
 */
const model = defineModel<string | null>();
defineProps<{ id?: string; placeholder?: string }>();

const { targets, find } = useLinkTargets();
const open = ref(false);
const query = ref('');
const root = ref<HTMLElement>();
onClickOutside(root, () => (open.value = false));

const internal = computed(() => (model.value && /^(page|solution|category|case|post|job):\d+$/.test(model.value) ? find(model.value) : null));
const isInternalMissing = computed(() => !!model.value && /^(page|solution|category|case|post|job):\d+$/.test(model.value) && !internal.value && targets.value.length > 0);

const groups = computed(() => {
    const q = query.value.toLowerCase().trim();
    const out: Record<string, typeof targets.value> = {};
    for (const t of targets.value) {
        if (q && !t.label.toLowerCase().includes(q)) continue;
        (out[t.group] ??= []).push(t);
    }
    return out;
});

function pick(value: string) {
    model.value = value;
    open.value = false;
    query.value = '';
}

function useCustom() {
    if (query.value.trim()) {
        pick(query.value.trim());
    }
}
</script>

<template>
    <div ref="root" class="relative">
        <!-- Lien interne choisi -->
        <div v-if="internal || isInternalMissing" class="flex h-9 items-center gap-2 rounded-md border border-input bg-muted/40 px-2 text-sm">
            <FileText class="size-4 shrink-0 text-primary" />
            <span class="min-w-0 flex-1 truncate" :title="internal?.url">
                <template v-if="internal">
                    <span class="text-muted-foreground">{{ internal.group }} ›</span> {{ internal.label }}
                </template>
                <span v-else class="text-destructive">Contenu supprimé</span>
            </span>
            <button type="button" class="rounded p-0.5 text-muted-foreground hover:bg-accent hover:text-foreground" title="Changer" @click="open = !open">
                <Link2 class="size-4" />
            </button>
            <button type="button" class="rounded p-0.5 text-muted-foreground hover:bg-accent hover:text-foreground" title="Retirer" @click="model = null">
                <X class="size-4" />
            </button>
        </div>

        <!-- Adresse libre -->
        <div v-else class="relative">
            <input
                :id="id"
                v-model="model"
                type="text"
                :placeholder="placeholder ?? 'https://… ou choisir une page'"
                class="flex h-9 w-full rounded-md border border-input bg-transparent py-1 pl-3 pr-9 text-sm shadow-sm placeholder:text-muted-foreground/70 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-input/30"
            />
            <button
                type="button"
                class="absolute right-1 top-1/2 -translate-y-1/2 rounded p-1.5 text-muted-foreground hover:bg-accent hover:text-foreground"
                title="Choisir une page du site"
                @click="open = !open"
            >
                <Link2 class="size-4" />
            </button>
        </div>

        <div v-if="open" class="absolute left-0 right-0 top-full z-40 mt-1 overflow-hidden rounded-lg border bg-popover shadow-xl">
            <div class="border-b p-2">
                <input
                    v-model="query"
                    type="text"
                    autofocus
                    placeholder="Rechercher une page, une offre… ou coller une adresse"
                    class="h-8 w-full rounded-md border border-input bg-transparent px-2 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    @keydown.enter.prevent="useCustom"
                />
            </div>
            <div class="max-h-72 overflow-y-auto py-1">
                <button
                    v-if="query.trim() && /^(https?:|mailto:|tel:|#|\/)/.test(query.trim())"
                    type="button"
                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-accent"
                    @click="useCustom"
                >
                    <ExternalLink class="size-4 text-muted-foreground" /> Utiliser « {{ query.trim() }} »
                </button>
                <template v-for="(items, group) in groups" :key="group">
                    <p class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">{{ group }}</p>
                    <button
                        v-for="t in items"
                        :key="t.value"
                        type="button"
                        class="block w-full truncate px-3 py-1.5 text-left text-sm hover:bg-accent"
                        :class="t.value === model ? 'font-semibold text-primary' : ''"
                        @click="pick(t.value)"
                    >
                        {{ t.label }}
                    </button>
                </template>
                <p v-if="!Object.keys(groups).length" class="px-3 py-4 text-center text-sm text-muted-foreground">Aucun résultat.</p>
            </div>
        </div>
    </div>
</template>
