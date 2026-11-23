<script setup lang="ts">
import AdminIcon from '@/components/cms/AdminIcon.vue';
import FieldsForm from '@/components/cms/FieldsForm.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useEditLocale } from '@/composables/useEditLocale';
import { clone, newBlock, stripHtml, tr, uid, withDefaults } from '@/lib/cms';
import type { Block, BlockDefinition, Field } from '@/types/cms';
import {
    ArrowDown, ArrowUp, ChevronDown, ChevronsDownUp, ChevronsUpDown, Copy, Eye, EyeOff, GripVertical, Languages, LayoutTemplate,
    Plus, Search, Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import draggable from 'vuedraggable';

/**
 * Constructeur de pages : liste de blocs à ajouter, réordonner, masquer, dupliquer et modifier.
 */
const props = defineProps<{
    model: Record<string, any>;
    name: string;
    definitions: BlockDefinition[];
    groups: Record<string, string>;
    settingsFields: Field[];
    errors?: Record<string, string>;
}>();

const locale = useEditLocale();
const defs = computed(() => Object.fromEntries(props.definitions.map((d) => [d.type, d])));

if (!Array.isArray(props.model[props.name])) {
    props.model[props.name] = [];
}

// Complète les blocs existants (nouveaux champs ajoutés depuis leur création…).
props.model[props.name] = (props.model[props.name] as Block[])
    .filter((b) => defs.value[b.type])
    .map((b) => ({
        ...b,
        id: b.id || uid('b_'),
        data: withDefaults(defs.value[b.type].fields, b.data),
        settings: { ...clone(defs.value[b.type].settings), ...(b.settings ?? {}) },
    }));

const blocks = computed({
    get: () => props.model[props.name] as Block[],
    set: (v) => (props.model[props.name] = v),
});

const openIds = ref<Set<string>>(new Set());
const tabs = ref<Record<string, 'content' | 'settings'>>({});
const toggle = (id: string) => (openIds.value.has(id) ? openIds.value.delete(id) : openIds.value.add(id));
const allOpen = computed(() => blocks.value.length > 0 && blocks.value.every((b) => openIds.value.has(b.id)));
const toggleAll = () => (openIds.value = allOpen.value ? new Set() : new Set(blocks.value.map((b) => b.id)));

// ── Ajout de bloc ──
const pickerOpen = ref(false);
const insertAt = ref<number | null>(null);
const search = ref('');

function openPicker(index: number | null = null) {
    insertAt.value = index;
    search.value = '';
    pickerOpen.value = true;
}

const groupedDefinitions = computed(() => {
    const q = search.value.toLowerCase().trim();
    return Object.entries(props.groups)
        .map(([key, label]) => ({
            key,
            label,
            items: props.definitions.filter(
                (d) => d.group === key && (!q || d.label.toLowerCase().includes(q) || d.description.toLowerCase().includes(q)),
            ),
        }))
        .filter((g) => g.items.length);
});

function add(def: BlockDefinition) {
    const block = newBlock(def);
    const list = [...blocks.value];
    list.splice(insertAt.value ?? list.length, 0, block);
    blocks.value = list;
    openIds.value.add(block.id);
    pickerOpen.value = false;
    setTimeout(() => document.getElementById(`block-${block.id}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
}

// ── Actions sur un bloc ──
function move(index: number, delta: number) {
    const list = [...blocks.value];
    const [item] = list.splice(index, 1);
    list.splice(index + delta, 0, item);
    blocks.value = list;
}

function duplicate(index: number) {
    const copy = { ...clone(blocks.value[index]), id: uid('b_') };
    const list = [...blocks.value];
    list.splice(index + 1, 0, copy);
    blocks.value = list;
    openIds.value.add(copy.id);
}

function remove(index: number) {
    const def = defs.value[blocks.value[index].type];
    if (confirm(`Supprimer le bloc « ${def?.label} » ?`)) {
        blocks.value = blocks.value.filter((_, i) => i !== index);
    }
}

// ── Aperçu et traductions ──
function preview(block: Block): string {
    const d = block.data ?? {};
    for (const key of ['title', 'text', 'eyebrow', 'content', 'intro']) {
        const text = stripHtml(tr(d[key], locale.value));
        if (text) return text;
    }
    return '';
}

/** Nombre de champs remplis en français mais vides dans la langue éditée. */
function missing(fields: Field[], data: Record<string, any>): number {
    if (locale.value === 'fr') return 0;
    let count = 0;
    for (const f of fields) {
        const v = data?.[f.name];
        if (f.t && v && typeof v === 'object' && stripHtml(v.fr ?? '') && !stripHtml(v[locale.value] ?? '')) count++;
        if (f.type === 'repeater' && Array.isArray(v)) v.forEach((item) => (count += missing(f.fields ?? [], item)));
    }
    return count;
}

const errorFor = (index: number) => Object.keys(props.errors ?? {}).some((k) => k.startsWith(`${props.name}.${index}.`));
</script>

<template>
    <div class="space-y-3">
        <div v-if="blocks.length" class="flex items-center justify-between">
            <p class="text-sm text-muted-foreground">{{ blocks.length }} bloc{{ blocks.length > 1 ? 's' : '' }} · glissez pour réordonner</p>
            <Button type="button" variant="ghost" size="sm" @click="toggleAll">
                <component :is="allOpen ? ChevronsDownUp : ChevronsUpDown" />
                {{ allOpen ? 'Tout replier' : 'Tout déplier' }}
            </Button>
        </div>

        <draggable v-model="blocks" item-key="id" handle=".block-handle" :animation="200" ghost-class="opacity-30" class="space-y-3">
            <template #item="{ element: block, index }">
                <div :id="`block-${block.id}`" class="group/block relative scroll-mt-24">
                    <!-- Insérer un bloc ici -->
                    <button
                        v-if="index > 0"
                        type="button"
                        class="absolute -top-3 left-1/2 z-10 hidden -translate-x-1/2 items-center gap-1 rounded-full border bg-background px-2 py-0.5 text-xs text-muted-foreground shadow-sm hover:border-primary hover:text-primary group-hover/block:flex"
                        @click="openPicker(index)"
                    >
                        <Plus class="size-3" /> Insérer
                    </button>

                    <div
                        class="overflow-hidden rounded-xl border bg-card shadow-sm transition"
                        :class="[block.hidden ? 'opacity-60' : '', errorFor(index) ? 'border-destructive' : '', openIds.has(block.id) ? 'ring-1 ring-primary/30' : '']"
                    >
                        <!-- En-tête du bloc -->
                        <div class="flex items-center gap-1 px-2 py-2">
                            <button type="button" class="block-handle cursor-grab rounded p-1.5 text-muted-foreground hover:bg-accent active:cursor-grabbing" title="Déplacer">
                                <GripVertical class="size-4" />
                            </button>
                            <button type="button" class="flex min-w-0 flex-1 items-center gap-3 text-left" @click="toggle(block.id)">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary [&_svg]:size-[18px]">
                                    <AdminIcon :name="defs[block.type]?.icon ?? 'square'" />
                                </span>
                                <span class="min-w-0">
                                    <span class="flex items-center gap-2 text-sm font-semibold">
                                        {{ defs[block.type]?.label }}
                                        <span v-if="block.hidden" class="rounded bg-muted px-1.5 py-px text-[10px] font-medium uppercase text-muted-foreground">Masqué</span>
                                        <span
                                            v-if="missing(defs[block.type]?.fields ?? [], block.data)"
                                            class="inline-flex items-center gap-1 rounded bg-amber-500/15 px-1.5 py-px text-[10px] font-semibold text-amber-700 dark:text-amber-400"
                                            :title="`${missing(defs[block.type]?.fields ?? [], block.data)} texte(s) à traduire`"
                                        >
                                            <Languages class="size-3" /> {{ missing(defs[block.type]?.fields ?? [], block.data) }}
                                        </span>
                                    </span>
                                    <span class="block truncate text-xs text-muted-foreground">{{ preview(block) || defs[block.type]?.description }}</span>
                                </span>
                            </button>
                            <div class="flex items-center opacity-70 transition group-hover/block:opacity-100">
                                <button type="button" class="rounded p-1.5 hover:bg-accent disabled:opacity-30" title="Monter" :disabled="index === 0" @click="move(index, -1)">
                                    <ArrowUp class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-1.5 hover:bg-accent disabled:opacity-30"
                                    title="Descendre"
                                    :disabled="index === blocks.length - 1"
                                    @click="move(index, 1)"
                                >
                                    <ArrowDown class="size-4" />
                                </button>
                                <button type="button" class="rounded p-1.5 hover:bg-accent" :title="block.hidden ? 'Afficher sur le site' : 'Masquer sur le site'" @click="block.hidden = !block.hidden">
                                    <component :is="block.hidden ? EyeOff : Eye" class="size-4" />
                                </button>
                                <button type="button" class="rounded p-1.5 hover:bg-accent" title="Dupliquer" @click="duplicate(index)">
                                    <Copy class="size-4" />
                                </button>
                                <button type="button" class="rounded p-1.5 hover:bg-destructive/10 hover:text-destructive" title="Supprimer" @click="remove(index)">
                                    <Trash2 class="size-4" />
                                </button>
                                <button type="button" class="rounded p-1.5 hover:bg-accent" title="Ouvrir / fermer" @click="toggle(block.id)">
                                    <ChevronDown class="size-4 transition" :class="openIds.has(block.id) ? 'rotate-180' : ''" />
                                </button>
                            </div>
                        </div>

                        <!-- Contenu du bloc -->
                        <div v-if="openIds.has(block.id)" class="border-t">
                            <div class="flex gap-4 border-b bg-muted/30 px-4 text-sm">
                                <button
                                    v-for="(label, tab) in { content: 'Contenu', settings: 'Apparence' }"
                                    :key="tab"
                                    type="button"
                                    class="-mb-px border-b-2 py-2 font-medium transition"
                                    :class="(tabs[block.id] ?? 'content') === tab ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground'"
                                    @click="tabs[block.id] = tab"
                                >
                                    {{ label }}
                                </button>
                            </div>
                            <div class="p-4 sm:p-5">
                                <FieldsForm
                                    v-if="(tabs[block.id] ?? 'content') === 'content'"
                                    :fields="defs[block.type]?.fields ?? []"
                                    :model="block.data"
                                    :errors="errors"
                                    :error-prefix="`${name}.${index}.data`"
                                />
                                <FieldsForm v-else :fields="settingsFields" :model="block.settings" />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </draggable>

        <div v-if="!blocks.length" class="flex flex-col items-center gap-3 rounded-xl border-2 border-dashed px-6 py-12 text-center">
            <LayoutTemplate class="size-10 text-muted-foreground/60" />
            <div>
                <p class="font-medium">Cette page est vide</p>
                <p class="text-sm text-muted-foreground">Ajoutez des blocs (bannière, texte, cartes, chiffres clés…) pour construire la page.</p>
            </div>
            <Button type="button" @click="openPicker()"><Plus /> Ajouter un premier bloc</Button>
        </div>
        <Button v-else type="button" variant="outline" class="w-full border-dashed py-6" @click="openPicker()"><Plus /> Ajouter un bloc</Button>

        <!-- Catalogue de blocs -->
        <Dialog v-model:open="pickerOpen">
            <DialogContent class="flex max-h-[88vh] max-w-4xl flex-col gap-0 p-0">
                <DialogHeader class="border-b p-5">
                    <DialogTitle>Ajouter un bloc</DialogTitle>
                    <DialogDescription>Choisissez le type de section à ajouter à la page.</DialogDescription>
                    <div class="relative pt-2">
                        <Search class="absolute left-3 top-1/2 mt-1 size-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Rechercher un bloc…"
                            class="h-9 w-full rounded-md border border-input bg-transparent pl-9 pr-3 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        />
                    </div>
                </DialogHeader>
                <div class="space-y-6 overflow-y-auto p-5">
                    <section v-for="group in groupedDefinitions" :key="group.key">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ group.label }}</h3>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            <button
                                v-for="def in group.items"
                                :key="def.type"
                                type="button"
                                class="flex items-start gap-3 rounded-lg border p-3 text-left transition hover:border-primary hover:bg-primary/5"
                                @click="add(def)"
                            >
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary [&_svg]:size-[18px]">
                                    <AdminIcon :name="def.icon" />
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold">{{ def.label }}</span>
                                    <span class="block text-xs text-muted-foreground">{{ def.description }}</span>
                                </span>
                            </button>
                        </div>
                    </section>
                    <p v-if="!groupedDefinitions.length" class="py-8 text-center text-sm text-muted-foreground">Aucun bloc ne correspond.</p>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
