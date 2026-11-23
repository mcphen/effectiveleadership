<script setup lang="ts">
import PageHeader from '@/components/cms/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Trash2 } from 'lucide-vue-next';

defineProps<{
    items: { id: number; email: string; name: string | null; locale: string; date: string }[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}>();

const destroy = (id: number) => confirm('Retirer cet abonné ?') && router.delete(route('admin.inbox.subscribers.destroy', id), { preserveScroll: true });
</script>

<template>
    <Head title="Abonnés newsletter" />
    <AppLayout :breadcrumbs="[{ title: 'Abonnés newsletter', href: route('admin.inbox.subscribers') }]">
        <div class="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6">
            <PageHeader title="Abonnés newsletter" :description="`${total} abonné${total > 1 ? 's' : ''}. Exportez la liste pour l'importer dans votre outil d'e-mailing.`">
                <Button variant="outline" as-child>
                    <a :href="route('admin.inbox.export', 'subscribers')"><Download /> Exporter (Excel)</a>
                </Button>
            </PageHeader>

            <div class="overflow-hidden rounded-xl border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/40 text-left text-xs uppercase tracking-wide text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-medium">E-mail</th>
                            <th class="px-4 py-3 font-medium">Langue</th>
                            <th class="px-4 py-3 font-medium">Inscrit le</th>
                            <th class="w-px" />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in items" :key="s.id" class="border-b last:border-0">
                            <td class="px-4 py-3 font-medium">{{ s.email }}</td>
                            <td class="px-4 py-3 uppercase text-muted-foreground">{{ s.locale }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ s.date }}</td>
                            <td class="px-2">
                                <Button variant="ghost" size="icon" title="Retirer" class="text-muted-foreground hover:text-destructive" @click="destroy(s.id)">
                                    <Trash2 />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-if="!items.length" class="px-6 py-14 text-center text-muted-foreground">Aucun abonné pour le moment.</p>
            </div>

            <nav v-if="links.length > 3" class="flex flex-wrap justify-center gap-1">
                <template v-for="link in links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="min-w-9 rounded-md border px-3 py-1.5 text-center text-sm"
                        :class="link.active ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent'"
                        ><span v-html="link.label"
                    /></Link>
                </template>
            </nav>
        </div>
    </AppLayout>
</template>
