<script setup lang="ts">
import PageHeader from '@/components/cms/PageHeader.vue';
import SelectInput from '@/components/cms/fields/SelectInput.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { localeName, translationLocale } from '@/lib/cms';
import type { ResourceMeta } from '@/types/cms';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Copy, ExternalLink, GripVertical, Home, Languages, MoreHorizontal, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import draggable from 'vuedraggable';

interface Column {
    name: string;
    label: string;
    type: 'title' | 'text' | 'image' | 'badge' | 'bool' | 'date';
    options?: Record<string, string>;
}

const props = defineProps<{
    resource: ResourceMeta;
    columns: Column[];
    filters: Record<string, { label: string; options: Record<string, string> }>;
    rows: Record<string, any>[];
    pagination: { current: number; last: number; total: number; links: { url: string | null; label: string; active: boolean }[] };
    query: { q: string; filters: Record<string, string> };
}>();

const search = ref(props.query.q ?? '');
const activeFilters = ref<Record<string, string | null>>({ ...(props.query.filters ?? {}) });
const items = ref([...props.rows]);
watch(
    () => props.rows,
    (rows) => (items.value = [...rows]),
);

const newLabel = computed(() => (props.resource.feminine ? 'Nouvelle ' : 'Nouveau ') + props.resource.singular);
const canReorder = computed(() => props.resource.sortable && !search.value && !Object.values(activeFilters.value).some(Boolean) && props.pagination.last === 1);

let timer: ReturnType<typeof setTimeout>;
function reload() {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(
            route('admin.resources.index', props.resource.slug),
            { q: search.value || undefined, filters: Object.fromEntries(Object.entries(activeFilters.value).filter(([, v]) => v)) },
            { preserveState: true, replace: true },
        );
    }, 300);
}
watch(search, reload);
watch(activeFilters, reload, { deep: true });

function saveOrder() {
    router.post(route('admin.resources.reorder', props.resource.slug), { ids: items.value.map((r) => r.id) }, { preserveScroll: true });
}

function destroy(row: Record<string, any>) {
    if (confirm(`Supprimer « ${row[props.resource.titleField]} » ? Cette action est définitive.`)) {
        router.delete(route('admin.resources.destroy', [props.resource.slug, row.id]), { preserveScroll: true });
    }
}

function duplicate(row: Record<string, any>) {
    router.post(route('admin.resources.duplicate', [props.resource.slug, row.id]));
}

const editUrl = (row: Record<string, any>) => route('admin.resources.edit', [props.resource.slug, row.id]);
const imageUrl = (v: string | null) => (!v ? null : /^(https?:)?\/\//.test(v) || v.startsWith('/') ? v : `/storage/${v}`);
</script>

<template>
    <Head :title="resource.label" />
    <AppLayout :breadcrumbs="[{ title: resource.label, href: route('admin.resources.index', resource.slug) }]">
        <div class="mx-auto w-full max-w-6xl space-y-6 p-4 md:p-6">
            <PageHeader :title="resource.label" :description="`${pagination.total} élément${pagination.total > 1 ? 's' : ''}`">
                <Button as-child>
                    <Link :href="route('admin.resources.create', resource.slug)"><Plus /> {{ newLabel }}</Link>
                </Button>
            </PageHeader>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-56 flex-1">
                    <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Rechercher…"
                        class="h-9 w-full rounded-md border border-input bg-transparent pl-9 pr-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                </div>
                <div v-for="(filter, name) in filters" :key="name" class="w-64">
                    <SelectInput v-model="activeFilters[name]" :options="filter.options" :placeholder="`${filter.label} : toutes`" />
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                            <tr>
                                <th v-if="canReorder" class="w-8" />
                                <th v-for="col in columns" :key="col.name" class="px-4 py-3 font-medium" :class="col.type === 'image' ? 'w-16' : ''">{{ col.label }}</th>
                                <th class="w-px" />
                            </tr>
                        </thead>
                        <draggable v-model="items" tag="tbody" item-key="id" handle=".row-handle" :disabled="!canReorder" :animation="180" ghost-class="opacity-40" @end="saveOrder">
                            <template #item="{ element: row }">
                                <tr class="border-b last:border-0 hover:bg-muted/30">
                                    <td v-if="canReorder" class="pl-2">
                                        <button type="button" class="row-handle cursor-grab rounded p-1 text-muted-foreground hover:bg-accent" title="Glisser pour réordonner">
                                            <GripVertical class="size-4" />
                                        </button>
                                    </td>
                                    <td v-for="col in columns" :key="col.name" class="px-4 py-3 align-middle">
                                        <template v-if="col.type === 'title'">
                                            <Link :href="editUrl(row)" class="font-medium hover:text-primary hover:underline">{{ row[col.name] || '(sans titre)' }}</Link>
                                            <Home v-if="row.is_home" class="ml-1.5 inline size-3.5 text-primary" />
                                            <span
                                                v-if="row._missing_translation"
                                                class="ml-2 inline-flex items-center gap-1 rounded bg-amber-500/15 px-1.5 py-px align-middle text-[10px] font-semibold text-amber-700 dark:text-amber-400"
                                                :title="`Traduction en ${localeName(translationLocale())} manquante`"
                                            >
                                                <Languages class="size-3" /> {{ translationLocale().toUpperCase() }}
                                            </span>
                                        </template>
                                        <div v-else-if="col.type === 'image'" class="flex size-10 items-center justify-center overflow-hidden rounded-md border bg-muted/40">
                                            <img v-if="row[col.name]" :src="imageUrl(row[col.name])!" alt="" class="max-h-full max-w-full object-contain" />
                                        </div>
                                        <span
                                            v-else-if="col.type === 'badge'"
                                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="row[col.name] === 'draft' ? 'bg-muted text-muted-foreground' : 'bg-primary/10 text-primary'"
                                            >{{ col.options?.[row[col.name]] ?? row[col.name] }}</span
                                        >
                                        <span v-else-if="col.type === 'bool'">
                                            <Check v-if="row[col.name]" class="size-4 text-emerald-600" />
                                            <span v-else class="text-muted-foreground">—</span>
                                        </span>
                                        <span v-else class="text-muted-foreground">{{ row[col.name] ?? '—' }}</span>
                                    </td>
                                    <td class="px-2 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button variant="ghost" size="icon" as-child title="Modifier">
                                                <Link :href="editUrl(row)"><Pencil /></Link>
                                            </Button>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon" title="Plus d'actions"><MoreHorizontal /></Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem v-if="row.url" as-child>
                                                        <a :href="row.url" target="_blank" rel="noopener"><ExternalLink class="mr-2 size-4" /> Voir sur le site</a>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="duplicate(row)"><Copy class="mr-2 size-4" /> Dupliquer</DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem class="text-destructive focus:text-destructive" @click="destroy(row)">
                                                        <Trash2 class="mr-2 size-4" /> Supprimer
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </draggable>
                    </table>
                </div>
                <div v-if="!items.length" class="px-6 py-14 text-center text-muted-foreground">
                    <p>Aucun élément{{ search ? ' ne correspond à votre recherche' : ' pour le moment' }}.</p>
                    <Button v-if="!search" as-child variant="outline" class="mt-4">
                        <Link :href="route('admin.resources.create', resource.slug)"><Plus /> {{ newLabel }}</Link>
                    </Button>
                </div>
            </div>

            <p v-if="resource.sortable && !canReorder && items.length > 1" class="text-xs text-muted-foreground">
                Effacez la recherche et les filtres pour réordonner par glisser-déposer.
            </p>

            <nav v-if="pagination.last > 1" class="flex flex-wrap justify-center gap-1">
                <template v-for="link in pagination.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="min-w-9 rounded-md border px-3 py-1.5 text-center text-sm"
                        :class="link.active ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent'"
                        ><span v-html="link.label"
                    /></Link>
                    <span v-else class="min-w-9 px-3 py-1.5 text-center text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </nav>
        </div>
    </AppLayout>
</template>
