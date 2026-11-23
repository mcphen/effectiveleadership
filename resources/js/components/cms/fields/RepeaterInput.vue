<script setup lang="ts">
import FieldsForm from '@/components/cms/FieldsForm.vue';
import { Button } from '@/components/ui/button';
import { useEditLocale } from '@/composables/useEditLocale';
import { clone, emptyItem, stripHtml, tr, uid } from '@/lib/cms';
import type { Field } from '@/types/cms';
import { ChevronDown, Copy, GripVertical, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import draggable from 'vuedraggable';

/** Liste d'éléments répétables (cartes, étapes, boutons…), réordonnables par glisser-déposer. */
const props = defineProps<{ field: Field; model: Record<string, any>; errors?: Record<string, string>; errorKey?: string }>();

const locale = useEditLocale();

if (!Array.isArray(props.model[props.field.name])) {
    props.model[props.field.name] = [];
}
for (const item of props.model[props.field.name]) {
    item._id ??= uid();
}

const items = computed({
    get: () => props.model[props.field.name] as Record<string, any>[],
    set: (v) => (props.model[props.field.name] = v),
});

const openIds = ref<Set<string>>(new Set(items.value.length <= 1 ? items.value.map((i) => i._id) : []));
const toggle = (id: string) => (openIds.value.has(id) ? openIds.value.delete(id) : openIds.value.add(id));
const canAdd = computed(() => !props.field.max || items.value.length < props.field.max);

function add() {
    const item = emptyItem(props.field.fields ?? []);
    items.value = [...items.value, item];
    openIds.value.add(item._id);
}

function duplicate(index: number) {
    const copy = { ...clone(items.value[index]), _id: uid() };
    const list = [...items.value];
    list.splice(index + 1, 0, copy);
    items.value = list;
    openIds.value.add(copy._id);
}

function remove(index: number) {
    if (confirm('Supprimer cet élément ?')) {
        items.value = items.value.filter((_, i) => i !== index);
    }
}

function label(item: Record<string, any>, index: number): string {
    const key = props.field.item_label ?? props.field.fields?.[0]?.name ?? '';
    const text = stripHtml(tr(item[key], locale.value));
    return text || `Élément ${index + 1}`;
}

const singular = computed(() => (props.field.max === 1 ? 'Ajouter' : 'Ajouter un élément'));
</script>

<template>
    <div class="space-y-2">
        <draggable v-model="items" item-key="_id" handle=".drag-handle" :animation="180" ghost-class="opacity-40" class="space-y-2">
            <template #item="{ element, index }">
                <div class="rounded-lg border bg-card">
                    <div class="flex items-center gap-1 px-2 py-1.5">
                        <button type="button" class="drag-handle cursor-grab rounded p-1 text-muted-foreground hover:bg-accent active:cursor-grabbing" title="Déplacer">
                            <GripVertical class="size-4" />
                        </button>
                        <button type="button" class="flex min-w-0 flex-1 items-center gap-2 py-1 text-left text-sm" @click="toggle(element._id)">
                            <ChevronDown class="size-4 shrink-0 text-muted-foreground transition" :class="openIds.has(element._id) ? '' : '-rotate-90'" />
                            <span class="truncate font-medium">{{ label(element, index) }}</span>
                        </button>
                        <button type="button" class="rounded p-1.5 text-muted-foreground hover:bg-accent hover:text-foreground" title="Dupliquer" @click="duplicate(index)">
                            <Copy class="size-3.5" />
                        </button>
                        <button type="button" class="rounded p-1.5 text-muted-foreground hover:bg-destructive/10 hover:text-destructive" title="Supprimer" @click="remove(index)">
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                    <div v-if="openIds.has(element._id)" class="border-t bg-muted/20 p-4">
                        <FieldsForm :fields="field.fields ?? []" :model="element" :errors="errors" :error-prefix="`${errorKey}.${index}`" />
                    </div>
                </div>
            </template>
        </draggable>

        <Button v-if="canAdd" type="button" variant="outline" size="sm" class="w-full border-dashed" @click="add">
            <Plus /> {{ singular }}
        </Button>
    </div>
</template>
