<script setup lang="ts">
import PageHeader from '@/components/cms/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatBytes, locales } from '@/lib/cms';
import type { MediaItem } from '@/types/cms';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Copy, FileText, Loader2, Search, Trash2, Upload } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    items: MediaItem[];
    pagination: { links: { url: string | null; label: string; active: boolean }[]; total: number };
    query: { q: string | null; type: string | null };
}>();

const search = ref(props.query.q ?? '');
const type = ref(props.query.type ?? '');
const dragging = ref(false);
const input = ref<HTMLInputElement>();
const upload = useForm<{ files: File[] }>({ files: [] });
const selected = ref<MediaItem | null>(null);
const altForm = useForm<{ alt: Record<string, string> }>({ alt: {} });
const copied = ref(false);

let timer: ReturnType<typeof setTimeout>;
watch([search, type], () => {
    clearTimeout(timer);
    timer = setTimeout(
        () => router.get(route('admin.media.index'), { q: search.value || undefined, type: type.value || undefined }, { preserveState: true, replace: true }),
        300,
    );
});

function send(files: FileList | null) {
    if (!files?.length) return;
    upload.files = Array.from(files);
    upload.post(route('admin.media.store'), {
        preserveScroll: true,
        forceFormData: true,
        onFinish: () => {
            upload.reset();
            if (input.value) input.value.value = '';
        },
    });
}

function open(item: MediaItem) {
    selected.value = item;
    altForm.alt = { ...Object.fromEntries(Object.keys(locales()).map((l) => [l, item.alt?.[l] ?? ''])) };
    copied.value = false;
}

function saveAlt() {
    if (!selected.value) return;
    altForm.patch(route('admin.media.update', selected.value.id), { preserveScroll: true, onSuccess: () => (selected.value = null) });
}

function destroy() {
    if (selected.value && confirm('Supprimer ce fichier ? Il disparaîtra aussi des pages qui l\'utilisent.')) {
        router.delete(route('admin.media.destroy', selected.value.id), { preserveScroll: true, onSuccess: () => (selected.value = null) });
    }
}

async function copyUrl() {
    if (!selected.value) return;
    await navigator.clipboard.writeText(new URL(selected.value.url, window.location.origin).href);
    copied.value = true;
}
</script>

<template>
    <Head title="Médiathèque" />
    <AppLayout :breadcrumbs="[{ title: 'Médiathèque', href: route('admin.media.index') }]">
        <div
            class="relative mx-auto w-full max-w-7xl space-y-6 p-4 md:p-6"
            @dragover.prevent="dragging = true"
            @dragleave.self.prevent="dragging = false"
            @drop.prevent="
                dragging = false;
                send($event.dataTransfer?.files ?? null);
            "
        >
            <PageHeader title="Médiathèque" :description="`${pagination.total} fichier${pagination.total > 1 ? 's' : ''} · images, PDF, documents`">
                <input ref="input" type="file" multiple class="hidden" @change="send(($event.target as HTMLInputElement).files)" />
                <Button :disabled="upload.processing" @click="input?.click()">
                    <Loader2 v-if="upload.processing" class="animate-spin" />
                    <Upload v-else />
                    Ajouter des fichiers
                </Button>
            </PageHeader>

            <div class="flex flex-wrap gap-3">
                <div class="relative min-w-56 flex-1">
                    <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Rechercher un fichier…"
                        class="h-9 w-full rounded-md border border-input bg-transparent pl-9 pr-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                </div>
                <div class="inline-flex rounded-lg border bg-muted/50 p-1 text-sm">
                    <button
                        v-for="(label, value) in { '': 'Tout', image: 'Images', file: 'Documents' }"
                        :key="value"
                        type="button"
                        class="rounded-md px-3 py-1"
                        :class="type === value ? 'bg-background shadow-sm' : 'text-muted-foreground'"
                        @click="type = value"
                    >
                        {{ label }}
                    </button>
                </div>
            </div>

            <p v-if="Object.keys(upload.errors).length" class="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
                {{ Object.values(upload.errors).join(' ') }}
            </p>

            <div
                v-if="dragging"
                class="pointer-events-none absolute inset-4 z-20 flex items-center justify-center rounded-2xl border-2 border-dashed border-primary bg-primary/5 text-lg font-medium text-primary"
            >
                Déposez vos fichiers pour les ajouter
            </div>

            <div v-if="items.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8">
                <button
                    v-for="item in items"
                    :key="item.id"
                    type="button"
                    class="group overflow-hidden rounded-lg border bg-card text-left transition hover:border-primary/50 hover:shadow-sm"
                    @click="open(item)"
                >
                    <div class="flex aspect-square items-center justify-center bg-muted/40 p-2">
                        <img v-if="item.is_image" :src="item.url" :alt="item.filename" loading="lazy" class="max-h-full max-w-full object-contain" />
                        <FileText v-else class="size-10 text-muted-foreground" />
                    </div>
                    <p class="truncate border-t px-2 py-1.5 text-xs" :title="item.filename">{{ item.filename }}</p>
                </button>
            </div>
            <div v-else class="flex flex-col items-center gap-3 rounded-xl border-2 border-dashed py-20 text-center text-muted-foreground">
                <Upload class="size-10" />
                <p>Glissez-déposez des fichiers ici, ou cliquez sur « Ajouter des fichiers ».</p>
            </div>

            <nav v-if="pagination.links.length > 3" class="flex flex-wrap justify-center gap-1">
                <template v-for="link in pagination.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="min-w-9 rounded-md border px-3 py-1.5 text-center text-sm"
                        :class="link.active ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent'"
                        ><span v-html="link.label"
                    /></Link>
                </template>
            </nav>
        </div>

        <Dialog :open="!!selected" @update:open="(v) => !v && (selected = null)">
            <DialogContent v-if="selected" class="max-w-3xl">
                <DialogHeader><DialogTitle class="truncate pr-6">{{ selected.filename }}</DialogTitle></DialogHeader>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="flex aspect-square items-center justify-center rounded-lg border bg-muted/40 p-3">
                        <img v-if="selected.is_image" :src="selected.url" alt="" class="max-h-full max-w-full object-contain" />
                        <FileText v-else class="size-16 text-muted-foreground" />
                    </div>
                    <div class="space-y-4 text-sm">
                        <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-muted-foreground">
                            <dt>Taille</dt>
                            <dd class="text-foreground">{{ formatBytes(selected.size) }}</dd>
                            <template v-if="selected.width">
                                <dt>Dimensions</dt>
                                <dd class="text-foreground">{{ selected.width }} × {{ selected.height }} px</dd>
                            </template>
                            <dt>Ajouté le</dt>
                            <dd class="text-foreground">{{ selected.created_at }}</dd>
                        </dl>
                        <Button type="button" variant="outline" size="sm" @click="copyUrl"><Copy /> {{ copied ? 'Adresse copiée !' : 'Copier l\'adresse' }}</Button>
                        <form v-if="selected.is_image" class="space-y-3" @submit.prevent="saveAlt">
                            <p class="font-medium">Texte alternatif <span class="font-normal text-muted-foreground">(accessibilité et Google)</span></p>
                            <div v-for="(label, code) in locales()" :key="code" class="space-y-1">
                                <label class="text-xs uppercase text-muted-foreground">{{ label }}</label>
                                <input
                                    v-model="altForm.alt[code]"
                                    type="text"
                                    class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                />
                            </div>
                            <Button type="submit" size="sm" :disabled="altForm.processing">Enregistrer</Button>
                        </form>
                        <Button type="button" variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="destroy"><Trash2 /> Supprimer le fichier</Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
