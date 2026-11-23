<script setup lang="ts">
import LocaleTabs from '@/components/cms/LocaleTabs.vue';
import PageHeader from '@/components/cms/PageHeader.vue';
import MenuTree from '@/components/cms/MenuTree.vue';
import { Button } from '@/components/ui/button';
import { provideEditLocale } from '@/composables/useEditLocale';
import AppLayout from '@/layouts/AppLayout.vue';
import { defaultLocale, uid } from '@/lib/cms';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Loader2, Save } from 'lucide-vue-next';

const props = defineProps<{
    locations: Record<string, string>;
    location: string;
    items: any[];
    maxDepth: number;
}>();

provideEditLocale(defaultLocale());

/** Complète la structure de chaque lien (libellés traduisibles, sous-liens). */
const normalize = (items: any[]): any[] =>
    (items ?? []).map((i) => ({
        _id: i._id ?? uid(),
        link: i.link ?? '',
        new_tab: !!i.new_tab,
        label: typeof i.label === 'object' && i.label ? { ...i.label } : { [defaultLocale()]: i.label ?? '' },
        description: typeof i.description === 'object' && i.description ? { ...i.description } : {},
        children: normalize(i.children ?? []),
    }));

const form = useForm<{ items: any[] }>({ items: normalize(props.items) });

function submit() {
    form.put(route('admin.menus.update', props.location), { preserveScroll: true, onSuccess: () => form.defaults() });
}
</script>

<template>
    <Head title="Menus" />
    <AppLayout :breadcrumbs="[{ title: 'Menus', href: route('admin.menus.edit') }]">
        <div class="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6">
            <PageHeader title="Menus" description="Organisez la navigation du site : glissez les liens pour les réordonner ou les imbriquer.">
                <LocaleTabs />
                <Button :disabled="form.processing || !form.isDirty" @click="submit">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    <Save v-else />
                    Enregistrer
                </Button>
            </PageHeader>

            <div class="flex flex-wrap gap-2 border-b">
                <Link
                    v-for="(label, key) in locations"
                    :key="key"
                    :href="route('admin.menus.edit', key)"
                    class="-mb-px border-b-2 px-3 py-2 text-sm font-medium"
                    :class="key === location ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground'"
                >
                    {{ label }}
                </Link>
            </div>

            <p v-if="maxDepth > 1" class="rounded-lg bg-muted/50 px-4 py-3 text-sm text-muted-foreground">
                Un lien placé <strong>sous</strong> un autre apparaît dans son menu déroulant. Utilisez le bouton « Sous-lien » ou glissez un lien dans la zone
                en retrait.
            </p>

            <MenuTree :items="form.items" :depth="1" :max-depth="maxDepth" @update:items="(v) => (form.items = v)" />
        </div>
    </AppLayout>
</template>
