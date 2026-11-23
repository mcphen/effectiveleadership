<script setup lang="ts">
import MediaPicker from '@/components/cms/MediaPicker.vue';
import { Button } from '@/components/ui/button';
import type { MediaItem } from '@/types/cms';
import { FileText, ImagePlus, RefreshCw, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

/** Champ image / fichier : stocke le chemin relatif (ex. media/2026/11/photo.jpg). */
const model = defineModel<string | null>();
const props = defineProps<{ accept?: 'image' | 'file'; compact?: boolean }>();
const open = ref(false);

const url = computed(() => {
    const v = model.value;
    if (!v) return null;
    return /^(https?:)?\/\//.test(v) || v.startsWith('/') ? v : `/storage/${v}`;
});
const isImage = computed(() => (props.accept ?? 'image') === 'image' || /\.(png|jpe?g|gif|webp|svg|ico)$/i.test(model.value ?? ''));
const filename = computed(() => (model.value ?? '').split('/').pop());

function select(item: MediaItem) {
    model.value = item.path;
}
</script>

<template>
    <div>
        <div v-if="model" class="group relative flex items-center gap-3 rounded-lg border bg-muted/30 p-2">
            <div
                class="flex shrink-0 items-center justify-center overflow-hidden rounded-md bg-[repeating-conic-gradient(#e5e7eb_0_25%,#fff_0_50%)] bg-[length:16px_16px] dark:bg-[repeating-conic-gradient(#27272a_0_25%,#18181b_0_50%)]"
                :class="compact ? 'size-14' : 'size-20'"
            >
                <img v-if="isImage" :src="url!" alt="" class="max-h-full max-w-full object-contain" />
                <FileText v-else class="size-7 text-muted-foreground" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium" :title="filename">{{ filename }}</p>
                <div class="mt-1 flex gap-1">
                    <Button type="button" size="sm" variant="outline" @click="open = true"><RefreshCw /> Remplacer</Button>
                    <Button type="button" size="sm" variant="ghost" class="text-destructive hover:text-destructive" @click="model = null">
                        <X /> Retirer
                    </Button>
                </div>
            </div>
        </div>
        <button
            v-else
            type="button"
            class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 text-sm text-muted-foreground transition hover:border-primary/60 hover:bg-primary/5 hover:text-primary"
            :class="compact ? 'py-3' : 'py-6'"
            @click="open = true"
        >
            <ImagePlus class="size-5" />
            {{ (accept ?? 'image') === 'image' ? 'Choisir une image' : 'Choisir un fichier' }}
        </button>
        <MediaPicker v-model:open="open" :accept="accept ?? 'image'" @select="select" />
    </div>
</template>
