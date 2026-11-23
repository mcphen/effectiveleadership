<script setup lang="ts">
import IconInput from '@/components/cms/fields/IconInput.vue';
import ImageInput from '@/components/cms/fields/ImageInput.vue';
import LinkInput from '@/components/cms/fields/LinkInput.vue';
import SelectInput from '@/components/cms/fields/SelectInput.vue';
import TextareaInput from '@/components/cms/fields/TextareaInput.vue';
import ToggleInput from '@/components/cms/fields/ToggleInput.vue';
import { useEditLocale } from '@/composables/useEditLocale';
import { defaultLocale, emptyValue, stripHtml } from '@/lib/cms';
import type { Field } from '@/types/cms';
import { Languages } from 'lucide-vue-next';
import { computed, defineAsyncComponent } from 'vue';

// Chargés à la demande (évite les imports circulaires et allège la page).
const RichTextInput = defineAsyncComponent(() => import('@/components/cms/fields/RichTextInput.vue'));
const RepeaterInput = defineAsyncComponent(() => import('@/components/cms/fields/RepeaterInput.vue'));
const SeoInput = defineAsyncComponent(() => import('@/components/cms/fields/SeoInput.vue'));

/**
 * Affiche un champ selon sa définition et modifie directement model[field.name].
 * Les champs traduisibles ({fr, en}) affichent la langue choisie dans les onglets FR / EN.
 */
const props = defineProps<{
    field: Field;
    model: Record<string, any>;
    errors?: Record<string, string>;
    errorKey?: string;
    hideLabel?: boolean;
}>();

const locale = useEditLocale();
const id = `f_${Math.random().toString(36).slice(2, 9)}`;
const key = computed(() => props.errorKey ?? props.field.name);

if (props.model[props.field.name] === undefined || props.model[props.field.name] === null) {
    if (props.field.t || ['repeater', 'seo', 'toggle'].includes(props.field.type) || props.field.default !== undefined) {
        props.model[props.field.name] = emptyValue(props.field);
    }
}
if (props.field.t && typeof props.model[props.field.name] !== 'object') {
    props.model[props.field.name] = { [defaultLocale()]: props.model[props.field.name] ?? '' };
}

/** Valeur liée : la langue courante pour un champ traduisible, sinon la valeur brute. */
const value = computed({
    get: () => (props.field.t ? (props.model[props.field.name]?.[locale.value] ?? '') : props.model[props.field.name]),
    set: (v) => {
        if (props.field.t) {
            props.model[props.field.name] = { ...(props.model[props.field.name] ?? {}), [locale.value]: v };
        } else {
            props.model[props.field.name] = v;
        }
    },
});

const error = computed(() => {
    if (!props.errors) return null;
    return props.errors[key.value] ?? (props.field.t ? props.errors[`${key.value}.${locale.value}`] : null) ?? null;
});

/** Texte de référence dans la langue par défaut, affiché quand la traduction est vide. */
const reference = computed(() => {
    if (!props.field.t || locale.value === defaultLocale()) return '';
    if (stripHtml(String(value.value ?? ''))) return '';
    return stripHtml(String(props.model[props.field.name]?.[defaultLocale()] ?? ''));
});

const inputClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm placeholder:text-muted-foreground/70 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-input/30';
</script>

<template>
    <div class="space-y-1.5" :class="field.width === 'half' ? '' : 'sm:col-span-2'">
        <div v-if="!hideLabel && field.type !== 'toggle' && field.type !== 'seo'" class="flex items-center gap-2">
            <label :for="id" class="text-sm font-medium leading-none">
                {{ field.label }}<span v-if="field.required" class="text-destructive"> *</span>
            </label>
            <span
                v-if="field.t"
                class="rounded bg-primary/10 px-1 py-px text-[10px] font-bold uppercase tracking-wide text-primary"
                :title="`Champ traduisible – vous éditez la version ${locale.toUpperCase()}`"
                >{{ locale }}</span
            >
        </div>

        <!-- Texte court -->
        <input v-if="field.type === 'text' || field.type === 'email'" :id="id" v-model="value" :type="field.type" :class="inputClass" :placeholder="reference || field.placeholder" />

        <!-- Slug -->
        <div v-else-if="field.type === 'slug'" class="flex items-center rounded-md border border-input shadow-sm focus-within:ring-1 focus-within:ring-ring dark:bg-input/30">
            <span class="shrink-0 border-r px-2 text-xs text-muted-foreground">/{{ locale }}/</span>
            <input :id="id" v-model="value" type="text" class="h-9 w-full bg-transparent px-2 text-sm focus:outline-none" :placeholder="reference || 'généré automatiquement'" />
        </div>

        <TextareaInput v-else-if="field.type === 'textarea'" :id="id" v-model="value" :rows="field.rows" :placeholder="reference || field.placeholder" />

        <RichTextInput v-else-if="field.type === 'richtext'" v-model="value" :compact="field.compact" />

        <input v-else-if="field.type === 'number'" :id="id" v-model.number="value" type="number" :class="inputClass" :min="field.min" :step="field.step" />

        <input v-else-if="field.type === 'date'" :id="id" v-model="value" type="date" :class="inputClass" />

        <div v-else-if="field.type === 'range'" class="flex items-center gap-3">
            <input :id="id" v-model.number="value" type="range" :min="field.min ?? 0" :max="field.max ?? 100" :step="field.step ?? 1" class="flex-1 accent-primary" />
            <span class="w-14 text-right text-sm tabular-nums text-muted-foreground">{{ value ?? field.default }}{{ field.suffix }}</span>
        </div>

        <ToggleInput v-else-if="field.type === 'toggle'" :id="id" v-model="value" :label="field.label" />

        <SelectInput v-else-if="field.type === 'select'" :id="id" v-model="value" :options="field.options ?? {}" :placeholder="field.required ? undefined : (field.placeholder ?? '—')" />

        <div v-else-if="field.type === 'color'" class="flex items-center gap-2">
            <input v-model="value" type="color" class="h-9 w-12 cursor-pointer rounded-md border border-input bg-transparent p-1" />
            <input :id="id" v-model="value" type="text" :class="inputClass" class="font-mono uppercase" maxlength="9" />
        </div>

        <ImageInput v-else-if="field.type === 'image'" v-model="value" accept="image" />
        <ImageInput v-else-if="field.type === 'file'" v-model="value" accept="file" />

        <LinkInput v-else-if="field.type === 'link'" :id="id" v-model="value" />

        <IconInput v-else-if="field.type === 'icon'" v-model="value" />

        <RepeaterInput v-else-if="field.type === 'repeater'" :field="field" :model="model" :errors="errors" :error-key="key" />

        <SeoInput v-else-if="field.type === 'seo'" :model="model" :name="field.name" />

        <p v-if="reference && ['textarea', 'richtext'].includes(field.type)" class="flex items-start gap-1.5 rounded-md bg-amber-500/10 px-2 py-1.5 text-xs text-amber-800 dark:text-amber-300">
            <Languages class="mt-px size-3.5 shrink-0" />
            <span class="line-clamp-2"><strong>À traduire</strong> — version française : « {{ reference }} »</span>
        </p>
        <p v-if="field.help" class="text-xs text-muted-foreground">{{ field.help }}</p>
        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
    </div>
</template>
