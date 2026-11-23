<script setup lang="ts">
import BlockEditor from '@/components/cms/BlockEditor.vue';
import FieldRenderer from '@/components/cms/FieldRenderer.vue';
import LocaleTabs from '@/components/cms/LocaleTabs.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { provideEditLocale } from '@/composables/useEditLocale';
import AppLayout from '@/layouts/AppLayout.vue';
import { defaultLocale, locales, stripHtml, tr } from '@/lib/cms';
import type { BlockDefinition, Field, ResourceMeta } from '@/types/cms';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Copy, ExternalLink, Loader2, MoreHorizontal, Save, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted } from 'vue';

const props = defineProps<{
    resource: ResourceMeta;
    fields: Field[];
    values: Record<string, any>;
    blocks: BlockDefinition[];
    blockGroups: Record<string, string>;
    blockSettings: Field[];
    publicUrl: string | null;
    publicUrls: Record<string, string> | null;
}>();

const locale = provideEditLocale(defaultLocale());
const form = useForm<Record<string, any>>({ ...props.values });
const isNew = computed(() => !props.values.id);

const mainFields = computed(() => props.fields.filter((f) => !f.side && f.type !== 'blocks'));
const blocksField = computed(() => props.fields.find((f) => f.type === 'blocks'));
const sideFields = computed(() => props.fields.filter((f) => f.side && f.type !== 'seo'));
const seoField = computed(() => props.fields.find((f) => f.type === 'seo'));

const title = computed(() => {
    const t = stripHtml(tr(form[props.resource.titleField], locale.value));
    if (t) return t;
    return isNew.value ? (props.resource.feminine ? 'Nouvelle ' : 'Nouveau ') + props.resource.singular : '(sans titre)';
});

/** Nombre de textes français sans traduction, par langue (indicateur sur les onglets). */
const missing = computed(() => {
    const out: Record<string, number> = {};
    const count = (fields: Field[], data: Record<string, any>, l: string): number => {
        let n = 0;
        for (const f of fields) {
            const v = data?.[f.name];
            if (f.t && v && typeof v === 'object' && stripHtml(v[defaultLocale()] ?? '') && !stripHtml(v[l] ?? '')) n++;
            if (f.type === 'repeater' && Array.isArray(v)) v.forEach((i) => (n += count(f.fields ?? [], i, l)));
            if (f.type === 'blocks' && Array.isArray(v)) {
                v.forEach((b) => {
                    const def = props.blocks.find((d) => d.type === b.type);
                    if (def && !b.hidden) n += count(def.fields, b.data, l);
                });
            }
        }
        return n;
    };
    for (const l of Object.keys(locales())) {
        if (l !== defaultLocale()) out[l] = count(props.fields, form.data(), l);
    }
    return out;
});

function submit() {
    // Après enregistrement, on reprend les valeurs normalisées par le serveur (slugs générés…).
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            form.defaults({ ...props.values });
            form.reset();
        },
    };
    if (isNew.value) {
        form.post(route('admin.resources.store', props.resource.slug), options);
    } else {
        form.put(route('admin.resources.update', [props.resource.slug, props.values.id]), options);
    }
}

function destroy() {
    if (confirm('Supprimer définitivement cet élément ?')) {
        form.defaults();
        router.delete(route('admin.resources.destroy', [props.resource.slug, props.values.id]));
    }
}

// Ctrl+S / Cmd+S pour enregistrer
function onKey(e: KeyboardEvent) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        if (!form.processing) submit();
    }
}

// Avertit avant de quitter la page avec des modifications non enregistrées.
function onBeforeUnload(e: BeforeUnloadEvent) {
    if (form.isDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
}
const removeNavigationGuard = router.on('before', (event) => {
    const method = event.detail.visit.method;
    if (form.isDirty && method === 'get' && !confirm('Vous avez des modifications non enregistrées. Quitter quand même ?')) {
        event.preventDefault();
    }
});

onMounted(() => {
    window.addEventListener('keydown', onKey);
    window.addEventListener('beforeunload', onBeforeUnload);
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    window.removeEventListener('beforeunload', onBeforeUnload);
    removeNavigationGuard();
});

const viewUrl = computed(() => props.publicUrls?.[locale.value] ?? props.publicUrl);
const errorCount = computed(() => Object.keys(form.errors).length);
</script>

<template>
    <Head :title="title" />
    <AppLayout
        :breadcrumbs="[
            { title: resource.label, href: route('admin.resources.index', resource.slug) },
            { title: isNew ? 'Création' : 'Modification', href: '#' },
        ]"
    >
        <form class="flex min-h-full flex-col" @submit.prevent="submit">
            <!-- Barre d'actions collante -->
            <div class="sticky top-0 z-30 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
                <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center gap-3 px-4 py-3 md:px-6">
                    <Button variant="ghost" size="icon" as-child title="Retour à la liste">
                        <Link :href="route('admin.resources.index', resource.slug)"><ArrowLeft /></Link>
                    </Button>
                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-lg font-semibold">{{ title }}</h1>
                        <p class="text-xs text-muted-foreground">
                            {{ resource.singular }}
                            <span v-if="form.isDirty" class="ml-1 font-medium text-amber-600">· modifications non enregistrées</span>
                        </p>
                    </div>
                    <LocaleTabs :missing="missing" />
                    <Button v-if="viewUrl" type="button" variant="outline" as-child>
                        <a :href="viewUrl" target="_blank" rel="noopener"><ExternalLink /> Voir</a>
                    </Button>
                    <DropdownMenu v-if="!isNew">
                        <DropdownMenuTrigger as-child>
                            <Button type="button" variant="outline" size="icon" title="Plus d'actions"><MoreHorizontal /></Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="router.post(route('admin.resources.duplicate', [resource.slug, values.id]))">
                                <Copy class="mr-2 size-4" /> Dupliquer
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem class="text-destructive focus:text-destructive" @click="destroy"><Trash2 class="mr-2 size-4" /> Supprimer</DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button type="submit" :disabled="form.processing" title="Enregistrer (Ctrl+S)">
                        <Loader2 v-if="form.processing" class="animate-spin" />
                        <Save v-else />
                        Enregistrer
                    </Button>
                </div>
                <p v-if="errorCount" class="bg-destructive/10 px-6 py-2 text-center text-sm text-destructive">
                    {{ errorCount }} champ{{ errorCount > 1 ? 's' : '' }} à corriger (signalé{{ errorCount > 1 ? 's' : '' }} en rouge).
                </p>
            </div>

            <div class="mx-auto grid w-full max-w-7xl gap-6 p-4 md:p-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                <!-- Colonne principale -->
                <div class="min-w-0 space-y-6">
                    <section v-if="mainFields.length" class="rounded-xl border bg-card p-5">
                        <div class="grid gap-x-4 gap-y-5 sm:grid-cols-2">
                            <FieldRenderer v-for="field in mainFields" :key="field.name" :field="field" :model="form" :errors="form.errors" />
                        </div>
                    </section>

                    <section v-if="blocksField">
                        <div class="mb-3">
                            <h2 class="text-base font-semibold">{{ blocksField.label }}</h2>
                            <p v-if="blocksField.help" class="text-sm text-muted-foreground">{{ blocksField.help }}</p>
                        </div>
                        <BlockEditor
                            :model="form"
                            :name="blocksField.name"
                            :definitions="blocks"
                            :groups="blockGroups"
                            :settings-fields="blockSettings"
                            :errors="form.errors"
                        />
                    </section>
                </div>

                <!-- Colonne latérale -->
                <aside class="space-y-6">
                    <section v-if="sideFields.length" class="space-y-5 rounded-xl border bg-card p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">Paramètres</h2>
                        <div class="grid gap-y-5">
                            <FieldRenderer v-for="field in sideFields" :key="field.name" :field="{ ...field, width: 'half' }" :model="form" :errors="form.errors" />
                        </div>
                    </section>
                    <section v-if="seoField" class="space-y-4 rounded-xl border bg-card p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">{{ seoField.label }}</h2>
                        <FieldRenderer :field="seoField" :model="form" :errors="form.errors" />
                    </section>
                </aside>
            </div>
        </form>
    </AppLayout>
</template>
