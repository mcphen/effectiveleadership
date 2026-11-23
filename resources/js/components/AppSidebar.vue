<script setup lang="ts">
import NavMain, { type NavGroup } from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem,
} from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Award, Briefcase, Building2, ExternalLink, FileText, FolderTree, Image, Inbox, LayoutDashboard, Mail, Menu, MessageSquareQuote,
    Newspaper, Send, Settings, UserPlus, Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<any>();

const groups = computed<NavGroup[]>(() => {
    const counts = page.props.counts ?? {};
    const isAdmin = page.props.auth?.user?.role === 'admin';

    return [
        {
            title: '',
            items: [{ title: 'Tableau de bord', href: route('admin.dashboard'), icon: LayoutDashboard, exact: true }],
        },
        {
            title: 'Site',
            items: [
                { title: 'Pages', href: route('admin.resources.index', 'pages'), icon: FileText },
                { title: 'Menus', href: route('admin.menus.edit'), icon: Menu },
                { title: 'Médiathèque', href: route('admin.media.index'), icon: Image },
            ],
        },
        {
            title: 'Solutions',
            items: [
                { title: 'Pôles & catégories', href: route('admin.resources.index', 'solution-categories'), icon: FolderTree },
                { title: 'Offres', href: route('admin.resources.index', 'solutions'), icon: Briefcase },
            ],
        },
        {
            title: 'À propos',
            items: [
                { title: 'Réalisations', href: route('admin.resources.index', 'case-studies'), icon: Award },
                { title: 'Références clients', href: route('admin.resources.index', 'references'), icon: Building2 },
                { title: 'Témoignages', href: route('admin.resources.index', 'testimonials'), icon: MessageSquareQuote },
            ],
        },
        {
            title: 'Institute & carrières',
            items: [
                { title: 'Publications', href: route('admin.resources.index', 'posts'), icon: Newspaper },
                { title: "Offres d'emploi", href: route('admin.resources.index', 'jobs'), icon: UserPlus },
            ],
        },
        {
            title: 'Messages reçus',
            items: [
                { title: 'Contact', href: route('admin.inbox.messages'), icon: Mail, badge: counts.messages },
                { title: 'Candidatures', href: route('admin.inbox.applications'), icon: Inbox, badge: counts.applications },
                { title: 'Abonnés newsletter', href: route('admin.inbox.subscribers'), icon: Send },
            ],
        },
        {
            title: 'Configuration',
            items: [
                { title: 'Réglages du site', href: route('admin.settings.edit'), icon: Settings },
                ...(isAdmin ? [{ title: 'Utilisateurs', href: route('admin.users.index'), icon: Users }] : []),
            ],
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('admin.dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="groups" />
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child tooltip="Voir le site">
                        <a :href="page.props.site?.url ?? '/'" target="_blank" rel="noopener">
                            <ExternalLink />
                            <span>Voir le site</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
