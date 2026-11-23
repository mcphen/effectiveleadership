<script setup lang="ts">
import PageHeader from '@/components/cms/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { FileDown, Mail, MailOpen, Phone, Trash2 } from 'lucide-vue-next';

interface Application {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    message: string | null;
    locale: string;
    job: string;
    has_cv: boolean;
    read: boolean;
    date: string;
}

defineProps<{ items: Application[]; links: { url: string | null; label: string; active: boolean }[] }>();

const toggleRead = (a: Application) => router.patch(route('admin.inbox.applications.mark', a.id), {}, { preserveScroll: true });
const destroy = (a: Application) =>
    confirm('Supprimer cette candidature et son CV ?') && router.delete(route('admin.inbox.applications.destroy', a.id), { preserveScroll: true });
</script>

<template>
    <Head title="Candidatures" />
    <AppLayout :breadcrumbs="[{ title: 'Candidatures', href: route('admin.inbox.applications') }]">
        <div class="mx-auto w-full max-w-5xl space-y-6 p-4 md:p-6">
            <PageHeader title="Candidatures" description="Candidatures reçues via les offres d'emploi et la candidature spontanée." />

            <div class="space-y-3">
                <article v-for="a in items" :key="a.id" class="rounded-xl border bg-card p-5" :class="a.read ? '' : 'border-primary/40'">
                    <header class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold">
                                <span v-if="!a.read" class="mr-1.5 inline-block size-2 rounded-full bg-primary align-middle" />{{ a.name }}
                            </p>
                            <p class="text-sm text-primary">{{ a.job }}</p>
                        </div>
                        <span class="text-xs text-muted-foreground">{{ a.date }}</span>
                    </header>
                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-muted-foreground">
                        <a :href="`mailto:${a.email}`" class="hover:underline">{{ a.email }}</a>
                        <span v-if="a.phone" class="inline-flex items-center gap-1"><Phone class="size-3.5" /> {{ a.phone }}</span>
                    </div>
                    <p v-if="a.message" class="mt-3 whitespace-pre-line text-sm">{{ a.message }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <Button v-if="a.has_cv" size="sm" as-child>
                            <a :href="route('admin.inbox.applications.cv', a.id)"><FileDown /> Télécharger le CV</a>
                        </Button>
                        <Button size="sm" variant="outline" as-child>
                            <a :href="`mailto:${a.email}`"><Mail /> Répondre</a>
                        </Button>
                        <Button size="sm" variant="outline" @click="toggleRead(a)"><MailOpen /> Marquer comme {{ a.read ? 'non lue' : 'lue' }}</Button>
                        <Button size="sm" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(a)"><Trash2 /> Supprimer</Button>
                    </div>
                </article>
                <p v-if="!items.length" class="rounded-xl border bg-card px-6 py-14 text-center text-muted-foreground">Aucune candidature.</p>
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
