<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { formatBytes } from '@/lib/cms';
import { http } from '@/lib/http';
import type { MediaItem } from '@/types/cms';
import { Check, FileText, Loader2, Search, Upload } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{ accept?: 'image' | 'file' | 'any' }>();
const open = defineModel<boolean>('open', { default: false });
const emit = defineEmits<{ select: [item: MediaItem] }>();

const items = ref<MediaItem[]>([]);
const next = ref<string | null>(null);
const loading = ref(false);
const uploading = ref(false);
const error = ref('');
const search = ref('');
const selected = ref<MediaItem | null>(null);
const dragging = ref(false);
const input = ref<HTMLInputElement>();

const typeParam = () => (props.accept === 'image' ? 'image' : props.accept === 'file' ? 'file' : undefined);

async function load(reset = true) {
    loading.value = true;
    error.value = '';
    try {
        const url = reset ? route('admin.media.index', { q: search.value || undefined, type: typeParam() }) : next.value!;
        const res = await http<{ data: MediaItem[]; next: string | null }>(url);
        items.value = reset ? res.data : [...items.value, ...res.data];
        next.value = res.next;
    } catch (e: any) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

async function upload(files: FileList | File[] | null) {
    if (!files || !files.length) return;
    uploading.value = true;
    error.value = '';
    const body = new FormData();
    Array.from(files).forEach((f) => body.append('files[]', f));
    try {
        const res = await http<{ data: MediaItem[] }>(route('admin.media.store'), { method: 'POST', body });
        items.value = [...res.data, ...items.value];
        selected.value = res.data[0] ?? null;
    } catch (e: any) {
        error.value = e.message;
    } finally {
        uploading.value = false;
        if (input.value) input.value.value = '';
    }
}

function onDrop(e: DragEvent) {
    dragging.value = false;
    upload(e.dataTransfer?.files ?? null);
}

function choose(item: MediaItem) {
    selected.value = item;
    confirm();
}

function confirm() {
    if (selected.value) {
        emit('select', selected.value);
        open.value = false;
    }
}

let timer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(() => load(), 300);
});

watch(open, (value) => {
    if (value) {
        selected.value = null;
        load();
    }
});
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="flex max-h-[90vh] max-w-5xl flex-col gap-0 p-0">
            <DialogHeader class="border-b p-5">
                <DialogTitle>Médiathèque</DialogTitle>
                <DialogDescription>Choisissez un fichier existant ou déposez-en un nouveau.</DialogDescription>
            </DialogHeader>

            <div class="flex flex-wrap items-center gap-3 border-b px-5 py-3">
                <div class="relative min-w-48 flex-1">
                    <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Rechercher un fichier…"
                        class="h-9 w-full rounded-md border border-input bg-transparent pl-9 pr-3 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                </div>
                <input
                    ref="input"
                    type="file"
                    multiple
                    class="hidden"
                    :accept="accept === 'image' ? 'image/*' : undefined"
                    @change="upload(($event.target as HTMLInputElement).files)"
                />
                <Button type="button" :disabled="uploading" @click="input?.click()">
                    <Loader2 v-if="uploading" class="animate-spin" />
                    <Upload v-else />
                    Téléverser
                </Button>
            </div>

            <div
                class="relative min-h-72 flex-1 overflow-y-auto p-5"
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="onDrop"
            >
                <div
                    v-if="dragging"
                    class="pointer-events-none absolute inset-3 z-10 flex items-center justify-center rounded-xl border-2 border-dashed border-primary bg-primary/5 text-primary"
                >
                    Déposez vos fichiers ici
                </div>

                <p v-if="error" class="mb-4 rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ error }}</p>

                <div v-if="!items.length && !loading" class="flex h-60 flex-col items-center justify-center gap-2 text-center text-muted-foreground">
                    <Upload class="size-8" />
                    <p>Aucun fichier. Glissez-déposez des fichiers ici ou cliquez sur « Téléverser ».</p>
                </div>

                <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
                    <button
                        v-for="item in items"
                        :key="item.id"
                        type="button"
                        class="group relative aspect-square overflow-hidden rounded-lg border bg-muted/40 text-left outline-none transition focus-visible:ring-2 focus-visible:ring-ring"
                        :class="selected?.id === item.id ? 'ring-2 ring-primary ring-offset-2 ring-offset-background' : 'hover:border-primary/50'"
                        :title="item.filename"
                        @click="selected = item"
                        @dblclick="choose(item)"
                    >
                        <img v-if="item.is_image" :src="item.url" :alt="item.filename" class="h-full w-full object-contain p-1" loading="lazy" />
                        <div v-else class="flex h-full flex-col items-center justify-center gap-2 p-2 text-muted-foreground">
                            <FileText class="size-8" />
                            <span class="line-clamp-2 break-all text-center text-[11px]">{{ item.filename }}</span>
                        </div>
                        <span v-if="selected?.id === item.id" class="absolute right-1.5 top-1.5 rounded-full bg-primary p-0.5 text-primary-foreground">
                            <Check class="size-3.5" />
                        </span>
                    </button>
                </div>

                <div v-if="loading" class="flex justify-center py-6 text-muted-foreground"><Loader2 class="animate-spin" /></div>
                <div v-else-if="next" class="flex justify-center pt-5">
                    <Button type="button" variant="outline" @click="load(false)">Charger plus</Button>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t bg-muted/30 px-5 py-3">
                <p class="truncate text-sm text-muted-foreground">
                    <template v-if="selected">
                        <strong class="text-foreground">{{ selected.filename }}</strong> · {{ formatBytes(selected.size) }}
                        <template v-if="selected.width"> · {{ selected.width }}×{{ selected.height }} px</template>
                    </template>
                    <template v-else>Aucun fichier sélectionné</template>
                </p>
                <div class="flex gap-2">
                    <Button type="button" variant="ghost" @click="open = false">Annuler</Button>
                    <Button type="button" :disabled="!selected" @click="confirm">Utiliser ce fichier</Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
