<script setup lang="ts">
import FieldRenderer from '@/components/cms/FieldRenderer.vue';
import type { Field } from '@/types/cms';

/** Grille de champs (2 colonnes ; les champs « half » occupent une colonne). */
defineProps<{
    fields: Field[];
    model: Record<string, any>;
    errors?: Record<string, string>;
    errorPrefix?: string;
}>();
</script>

<template>
    <div class="grid gap-x-4 gap-y-5 sm:grid-cols-2">
        <FieldRenderer
            v-for="field in fields"
            :key="field.name"
            :field="field"
            :model="model"
            :errors="errors"
            :error-key="errorPrefix ? `${errorPrefix}.${field.name}` : field.name"
        />
    </div>
</template>
