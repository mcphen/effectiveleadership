<script setup lang="ts">
import ImageInput from '@/components/cms/fields/ImageInput.vue';
import TextareaInput from '@/components/cms/fields/TextareaInput.vue';
import ToggleInput from '@/components/cms/fields/ToggleInput.vue';
import { useEditLocale } from '@/composables/useEditLocale';
import { emptyValue } from '@/lib/cms';
import { computed } from 'vue';

/** Titre et description pour Google + image de partage (réseaux sociaux). */
const props = defineProps<{ model: Record<string, any>; name: string }>();
const locale = useEditLocale();

if (!props.model[props.name] || typeof props.model[props.name] !== 'object' || Array.isArray(props.model[props.name])) {
    props.model[props.name] = emptyValue({ name: props.name, type: 'seo', label: '' });
}
const seo = computed(() => props.model[props.name]);
seo.value.title ??= {};
seo.value.description ??= {};

const title = computed({
    get: () => seo.value.title[locale.value] ?? '',
    set: (v: string) => (seo.value.title = { ...seo.value.title, [locale.value]: v }),
});
const description = computed({
    get: () => seo.value.description[locale.value] ?? '',
    set: (v: string) => (seo.value.description = { ...seo.value.description, [locale.value]: v }),
});

const counter = (len: number, max: number) => (len > max ? 'text-destructive' : len > max * 0.85 ? 'text-amber-600' : 'text-muted-foreground');
const inputClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm placeholder:text-muted-foreground/70 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-input/30';
</script>

<template>
    <div class="space-y-4">
        <p class="text-xs text-muted-foreground">Facultatif : par défaut, le titre et le résumé du contenu sont utilisés.</p>
        <div class="space-y-1.5">
            <div class="flex items-center justify-between text-sm font-medium">
                <span>Titre Google <span class="ml-1 rounded bg-primary/10 px-1 text-[10px] font-bold uppercase text-primary">{{ locale }}</span></span>
                <span class="text-xs tabular-nums" :class="counter(title.length, 60)">{{ title.length }}/60</span>
            </div>
            <input v-model="title" type="text" :class="inputClass" />
        </div>
        <div class="space-y-1.5">
            <div class="flex items-center justify-between text-sm font-medium">
                <span>Description Google</span>
                <span class="text-xs tabular-nums" :class="counter(description.length, 160)">{{ description.length }}/160</span>
            </div>
            <TextareaInput v-model="description" :rows="3" />
        </div>
        <div class="space-y-1.5">
            <span class="text-sm font-medium">Image de partage</span>
            <ImageInput v-model="seo.image" compact />
        </div>
        <ToggleInput v-model="seo.noindex" label="Masquer dans Google (noindex)" />
    </div>
</template>
