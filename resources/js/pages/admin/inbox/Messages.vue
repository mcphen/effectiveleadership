<script setup lang="ts">
import PageHeader from '@/components/cms/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Mail, MailOpen, Phone, Search, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Message {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    company: string | null;
    subject: string | null;
    message: string;
    locale: string;
    page_url: string | null;
    read: boolean;
    date: string;
}

const props = defineProps<{ items: Message[]; links: { url: string | null; label: string; active: boolean }[]; query: { q: string | null } }>();

const search = ref(props.query.q ?? '');
const openId = ref<number | null>(null);

let timer: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(() => router.get(route('admin.inbox.messages'), { q: search.value || undefined }, { preserveState: true, replace: true }), 300);
});

function open(m: Message) {
    openId.value = openId.value === m.id ? null : m.id;
    if (!m.read && openId.value === m.id) {
        router.patch(route('admin.inbox.messages.mark', m.id), {}, { preserveScroll: true, preserveState: true });
    }
}

const toggleRead = (m: Message) => router.patch(route('admin.inbox.messages.mark', m.id), {}, { preserveScroll: true, preserveState: true });
const destroy = (m: Message) => confirm('Supprimer ce message ?') && router.delete(route('admin.inbox.messages.destroy', m.id), { preserveScroll: true });
</script>

<template>
    <Head title="Messages de contact" />
    <AppLayout :breadcrumbs="[{ title: 'Messages de contact', href: route('admin.inbox.messages') }]">
        <div class="mx-auto w-full max-w-5xl space-y-6 p-4 md:p-6">
            <PageHeader title="Messages de contact" description="Messages envoyés depuis le formulaire de contact du site.">
                <Button variant="outline" as-child>
                    <a :href="route('admin.inbox.export', 'messages')"><Download /> Exporter (Excel)</a>
                </Button>
            </PageHeader>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <input
                    v-model="search"
                    type="search"
                    placeholder="Rechercher un nom, un e-mail, un mot…"
                    class="h-9 w-full rounded-md border border-input bg-transparent pl-9 pr-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                />
            </div>

            <div class="divide-y overflow-hidden rounded-xl border bg-card">
                <div v-for="m in items" :key="m.id">
                    <button type="button" class="flex w-full gap-3 px-5 py-4 text-left hover:bg-muted/40" @click="open(m)">
                        <span class="mt-1.5 size-2 shrink-0 rounded-full" :class="m.read ? '' : 'bg-primary'" />
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap justify-between gap-2 text-sm">
                                <span :class="m.read ? '' : 'font-semibold'">
                                    {{ m.name }} <span class="font-normal text-muted-foreground">· {{ m.email }}</span>
                                </span>
                                <span class="text-xs text-muted-foreground">{{ m.date }}</span>
                            </span>
                            <span class="block truncate text-sm text-muted-foreground">
                                <strong v-if="m.subject" class="font-medium text-foreground">{{ m.subject }} — </strong>{{ m.message }}
                            </span>
                        </span>
                    </button>
                    <div v-if="openId === m.id" class="space-y-4 border-t bg-muted/20 px-5 py-4 text-sm">
                        <div class="flex flex-wrap gap-x-6 gap-y-1 text-muted-foreground">
                            <span v-if="m.company">Organisation : <strong class="text-foreground">{{ m.company }}</strong></span>
                            <span v-if="m.phone" class="inline-flex items-center gap-1"><Phone class="size-3.5" /> {{ m.phone }}</span>
                            <span>Langue : {{ m.locale.toUpperCase() }}</span>
                            <a v-if="m.page_url" :href="m.page_url" target="_blank" class="truncate underline">Page d'envoi</a>
                        </div>
                        <p class="whitespace-pre-line">{{ m.message }}</p>
                        <div class="flex flex-wrap gap-2">
                            <Button size="sm" as-child>
                                <a :href="`mailto:${m.email}?subject=${encodeURIComponent('Re: ' + (m.subject ?? 'Votre message'))}`"><Mail /> Répondre</a>
                            </Button>
                            <Button size="sm" variant="outline" @click="toggleRead(m)"><MailOpen /> Marquer comme {{ m.read ? 'non lu' : 'lu' }}</Button>
                            <Button size="sm" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(m)"><Trash2 /> Supprimer</Button>
                        </div>
                    </div>
                </div>
                <p v-if="!items.length" class="px-6 py-14 text-center text-muted-foreground">Aucun message.</p>
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
