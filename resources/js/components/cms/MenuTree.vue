<script setup lang="ts">
import LinkInput from '@/components/cms/fields/LinkInput.vue';
import ToggleInput from '@/components/cms/fields/ToggleInput.vue';
import { Button } from '@/components/ui/button';
import { useEditLocale } from '@/composables/useEditLocale';
import { useLinkTargets } from '@/composables/useLinkTargets';
import { defaultLocale, locales, uid } from '@/lib/cms';
import { ChevronDown, CornerDownRight, GripVertical, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import draggable from 'vuedraggable';

defineOptions({ name: 'MenuTree' });

const props = defineProps<{ items: any[]; depth: number; maxDepth: number }>();
const emit = defineEmits<{ 'update:items': [items: any[]] }>();

const locale = useEditLocale();
const { find } = useLinkTargets();
const openIds = ref<Set<string>>(new Set());

const list = computed({
    get: () => props.items,
    set: (v) => emit('update:items', v),
});

const blank = () => ({
    _id: uid(),
    label: Object.fromEntries(Object.keys(locales()).map((l) => [l, ''])),
    description: Object.fromEntries(Object.keys(locales()).map((l) => [l, ''])),
    link: '',
    new_tab: false,
    children: [],
});

function add() {
    const item = blank();
    list.value = [...list.value, item];
    openIds.value.add(item._id);
}

function addChild(item: any) {
    const child = blank();
    item.children = [...(item.children ?? []), child];
}

function remove(index: number) {
    if (confirm('Supprimer ce lien' + (list.value[index].children?.length ? ' et ses sous-liens' : '') + ' ?')) {
        list.value = list.value.filter((_, i) => i !== index);
    }
}

function toggle(id: string) {
    openIds.value.has(id) ? openIds.value.delete(id) : openIds.value.add(id);
}

/** Libellé affiché : saisi, sinon titre du contenu lié. */
function label(item: any): string {
    return item.label?.[locale.value] || item.label?.[defaultLocale()] || find(item.link)?.label || 'Nouveau lien';
}
</script>

<template>
    <div class="space-y-2">
        <draggable
            v-model="list"
            item-key="_id"
            handle=".menu-handle"
            :group="{ name: 'menu', pull: true, put: depth <= maxDepth }"
            :animation="180"
            ghost-class="opacity-40"
            class="min-h-2 space-y-2"
        >
            <template #item="{ element: item, index }">
                <div class="rounded-lg border bg-card">
                    <div class="flex items-center gap-1 px-2 py-2">
                        <button type="button" class="menu-handle cursor-grab rounded p-1 text-muted-foreground hover:bg-accent" title="Déplacer">
                            <GripVertical class="size-4" />
                        </button>
                        <button type="button" class="flex min-w-0 flex-1 items-center gap-2 text-left" @click="toggle(item._id)">
                            <ChevronDown class="size-4 shrink-0 text-muted-foreground transition" :class="openIds.has(item._id) ? '' : '-rotate-90'" />
                            <span class="truncate text-sm font-medium">{{ label(item) }}</span>
                            <span class="truncate text-xs text-muted-foreground">{{ find(item.link)?.url ?? item.link }}</span>
                        </button>
                        <Button v-if="depth < maxDepth" type="button" variant="ghost" size="sm" @click="addChild(item)">
                            <CornerDownRight /> Sous-lien
                        </Button>
                        <button type="button" class="rounded p-1.5 text-muted-foreground hover:bg-destructive/10 hover:text-destructive" title="Supprimer" @click="remove(index)">
                            <Trash2 class="size-4" />
                        </button>
                    </div>

                    <div v-if="openIds.has(item._id)" class="grid gap-4 border-t bg-muted/20 p-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium">
                                Libellé <span class="rounded bg-primary/10 px-1 text-[10px] font-bold uppercase text-primary">{{ locale }}</span>
                            </label>
                            <input
                                v-model="item.label[locale]"
                                type="text"
                                :placeholder="item.label[defaultLocale()] || find(item.link)?.label || ''"
                                class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            />
                            <p class="text-xs text-muted-foreground">Laisser vide pour reprendre le titre de la page liée.</p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium">Lien</label>
                            <LinkInput v-model="item.link" />
                        </div>
                        <div v-if="depth > 1" class="space-y-1.5 sm:col-span-2">
                            <label class="text-sm font-medium">Description courte (menu déroulant)</label>
                            <input
                                v-model="item.description[locale]"
                                type="text"
                                class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            />
                        </div>
                        <ToggleInput v-model="item.new_tab" label="Ouvrir dans un nouvel onglet" />
                    </div>

                    <div v-if="depth < maxDepth" class="border-t border-dashed py-2 pl-8 pr-2">
                        <MenuTree :items="item.children ?? []" :depth="depth + 1" :max-depth="maxDepth" @update:items="(v) => (item.children = v)" />
                    </div>
                </div>
            </template>
        </draggable>
        <Button v-if="depth === 1" type="button" variant="outline" class="w-full border-dashed" @click="add"><Plus /> Ajouter un lien</Button>
    </div>
</template>
