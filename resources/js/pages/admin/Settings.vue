<script setup lang="ts">
import AdminIcon from '@/components/cms/AdminIcon.vue';
import FieldsForm from '@/components/cms/FieldsForm.vue';
import LocaleTabs from '@/components/cms/LocaleTabs.vue';
import PageHeader from '@/components/cms/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { provideEditLocale } from '@/composables/useEditLocale';
import AppLayout from '@/layouts/AppLayout.vue';
import { defaultLocale } from '@/lib/cms';
import type { Field } from '@/types/cms';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Loader2, Save } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    tabs: Record<string, { label: string; icon: string }>;
    tab: string;
    fields: Field[];
    values: Record<string, any>;
}>();

provideEditLocale(defaultLocale());
const form = useForm<Record<string, any>>({ ...props.values });
const hasTranslatable = computed(() => props.fields.some((f) => f.t));

function submit() {
    form.put(route('admin.settings.update', props.tab), { preserveScroll: true, onSuccess: () => form.defaults() });
}
</script>

<template>
    <Head title="Réglages du site" />
    <AppLayout :breadcrumbs="[{ title: 'Réglages du site', href: route('admin.settings.edit') }]">
        <div class="mx-auto w-full max-w-5xl space-y-6 p-4 md:p-6">
            <PageHeader title="Réglages du site" description="Identité, couleurs, coordonnées et informations communes à toutes les pages." />

            <div class="grid gap-6 md:grid-cols-[220px_minmax(0,1fr)]">
                <nav class="flex gap-1 overflow-x-auto md:flex-col">
                    <Link
                        v-for="(t, key) in tabs"
                        :key="key"
                        :href="route('admin.settings.edit', key)"
                        class="flex shrink-0 items-center gap-2 rounded-md px-3 py-2 text-sm [&_svg]:size-4"
                        :class="key === tab ? 'bg-primary/10 font-medium text-primary' : 'text-muted-foreground hover:bg-accent hover:text-foreground'"
                    >
                        <AdminIcon :name="t.icon" />
                        {{ t.label }}
                    </Link>
                </nav>

                <form class="space-y-5 rounded-xl border bg-card p-5" @submit.prevent="submit">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b pb-4">
                        <h2 class="text-lg font-semibold">{{ tabs[tab].label }}</h2>
                        <LocaleTabs v-if="hasTranslatable" />
                    </div>
                    <FieldsForm :fields="fields" :model="form" :errors="form.errors" />
                    <div class="flex justify-end border-t pt-4">
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="animate-spin" />
                            <Save v-else />
                            Enregistrer
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
