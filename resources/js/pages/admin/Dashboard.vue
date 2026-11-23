<script setup lang="ts">
import AdminIcon from '@/components/cms/AdminIcon.vue';
import PageHeader from '@/components/cms/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, ExternalLink, FilePlus2, Image, Inbox, Languages, Mail, Menu, Settings } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
    stats: { label: string; value: number; href: string; icon: string }[];
    messages: { id: number; name: string; subject: string | null; excerpt: string; date: string; read: boolean }[];
    applications: number;
    missingTranslations: { resource: string; type: string; id: number; title: string }[];
}>();

const page = usePage<any>();
const firstName = computed(() => (page.props.auth?.user?.name ?? '').split(' ')[0]);

const shortcuts = [
    { label: 'Créer une page', href: route('admin.resources.create', 'pages'), icon: FilePlus2 },
    { label: 'Publier un article', href: route('admin.resources.create', 'posts'), icon: FilePlus2 },
    { label: 'Modifier le menu', href: route('admin.menus.edit'), icon: Menu },
    { label: 'Médiathèque', href: route('admin.media.index'), icon: Image },
    { label: 'Logo, couleurs, coordonnées', href: route('admin.settings.edit'), icon: Settings },
];
</script>

<template>
    <Head title="Tableau de bord" />
    <AppLayout :breadcrumbs="[{ title: 'Tableau de bord', href: route('admin.dashboard') }]">
        <div class="mx-auto w-full max-w-6xl space-y-8 p-4 md:p-6">
            <PageHeader :title="`Bonjour ${firstName} 👋`" description="Gérez le contenu du site Effective Leadership Consulting.">
                <Button variant="outline" as-child>
                    <a :href="page.props.site?.url" target="_blank" rel="noopener"><ExternalLink /> Voir le site</a>
                </Button>
            </PageHeader>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
                <Link v-for="s in stats" :key="s.label" :href="s.href" class="group rounded-xl border bg-card p-4 transition hover:border-primary/50 hover:shadow-sm">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary [&_svg]:size-4"><AdminIcon :name="s.icon" /></span>
                    <p class="mt-3 text-2xl font-semibold tabular-nums">{{ s.value }}</p>
                    <p class="text-sm text-muted-foreground group-hover:text-foreground">{{ s.label }}</p>
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <section class="rounded-xl border bg-card">
                    <header class="flex items-center justify-between border-b px-5 py-3">
                        <h2 class="flex items-center gap-2 font-semibold"><Mail class="size-4" /> Derniers messages</h2>
                        <Link :href="route('admin.inbox.messages')" class="text-sm text-primary hover:underline">Tout voir</Link>
                    </header>
                    <ul v-if="messages.length" class="divide-y">
                        <li v-for="m in messages" :key="m.id">
                            <Link :href="route('admin.inbox.messages')" class="flex gap-3 px-5 py-3 hover:bg-muted/40">
                                <span class="mt-1.5 size-2 shrink-0 rounded-full" :class="m.read ? 'bg-transparent' : 'bg-primary'" />
                                <span class="min-w-0 flex-1">
                                    <span class="flex justify-between gap-2 text-sm">
                                        <span :class="m.read ? '' : 'font-semibold'">{{ m.name }}</span>
                                        <span class="shrink-0 text-xs text-muted-foreground">{{ m.date }}</span>
                                    </span>
                                    <span class="block truncate text-sm text-muted-foreground">
                                        <template v-if="m.subject">{{ m.subject }} — </template>{{ m.excerpt }}
                                    </span>
                                </span>
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="px-5 py-10 text-center text-sm text-muted-foreground">Aucun message pour l'instant.</p>
                    <Link
                        v-if="applications"
                        :href="route('admin.inbox.applications')"
                        class="flex items-center gap-2 border-t px-5 py-3 text-sm text-primary hover:bg-muted/40"
                    >
                        <Inbox class="size-4" /> {{ applications }} candidature{{ applications > 1 ? 's' : '' }} non lue{{ applications > 1 ? 's' : '' }}
                        <ArrowRight class="ml-auto size-4" />
                    </Link>
                </section>

                <div class="space-y-6">
                    <section class="rounded-xl border bg-card p-5">
                        <h2 class="mb-3 font-semibold">Raccourcis</h2>
                        <div class="space-y-1">
                            <Link v-for="s in shortcuts" :key="s.label" :href="s.href" class="flex items-center gap-3 rounded-md px-2 py-2 text-sm hover:bg-accent">
                                <component :is="s.icon" class="size-4 text-muted-foreground" />
                                {{ s.label }}
                            </Link>
                        </div>
                    </section>

                    <section v-if="missingTranslations.length" class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-5">
                        <h2 class="mb-1 flex items-center gap-2 font-semibold"><Languages class="size-4 text-amber-600" /> À traduire en anglais</h2>
                        <p class="mb-3 text-xs text-muted-foreground">Ces contenus s'affichent en français sur la version anglaise du site.</p>
                        <ul class="space-y-1 text-sm">
                            <li v-for="m in missingTranslations" :key="`${m.resource}-${m.id}`">
                                <Link :href="route('admin.resources.edit', [m.resource, m.id])" class="flex gap-2 rounded px-1 py-1 hover:bg-amber-500/10">
                                    <span class="shrink-0 text-muted-foreground">{{ m.type }}</span>
                                    <span class="truncate">{{ m.title }}</span>
                                </Link>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
