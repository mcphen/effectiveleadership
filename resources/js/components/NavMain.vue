<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuBadge, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import type { Component } from 'vue';

export interface NavLink {
    title: string;
    href: string;
    icon: Component;
    badge?: number;
    exact?: boolean;
}

export interface NavGroup {
    title: string;
    items: NavLink[];
}

defineProps<{ groups: NavGroup[] }>();

const page = usePage();

/** Lien actif : même chemin, ou sous-page (ex. /admin/content/pages/3/edit pour « Pages »). */
function isActive(item: NavLink): boolean {
    const current = page.url.split('?')[0];
    const path = new URL(item.href, window.location.origin).pathname;
    return item.exact ? current === path : current === path || current.startsWith(path + '/');
}
</script>

<template>
    <SidebarGroup v-for="group in groups" :key="group.title" class="px-2 py-1">
        <SidebarGroupLabel v-if="group.title">{{ group.title }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in group.items" :key="item.title">
                <SidebarMenuButton as-child :is-active="isActive(item)" :tooltip="item.title">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
                <SidebarMenuBadge v-if="item.badge" class="rounded-full bg-primary text-primary-foreground">{{ item.badge }}</SidebarMenuBadge>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
