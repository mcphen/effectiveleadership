<script setup lang="ts">
import {
    AlignLeft, Award, Badge, BarChart3, Briefcase, Building2, FileText, FolderTree, Heading, HelpCircle, Image, Images,
    LayoutGrid, LayoutPanelLeft, ListChecks, ListOrdered, Mail, MapPin, Megaphone, MessageSquareQuote, Newspaper, Palette,
    PanelBottom, PanelTop, PlayCircle, Quote, Search, Send, Share2, Square, UserPlus,
} from 'lucide-vue-next';
import { computed, type Component } from 'vue';

const props = defineProps<{ name: string }>();

const map: Record<string, Component> = {
    'align-left': AlignLeft, award: Award, badge: Badge, 'bar-chart-3': BarChart3, briefcase: Briefcase, 'building-2': Building2,
    'file-text': FileText, 'folder-tree': FolderTree, heading: Heading, 'help-circle': HelpCircle, image: Image, images: Images,
    'layout-grid': LayoutGrid, 'layout-panel-left': LayoutPanelLeft, 'list-checks': ListChecks, 'list-ordered': ListOrdered,
    mail: Mail, 'map-pin': MapPin, megaphone: Megaphone, 'message-square-quote': MessageSquareQuote, newspaper: Newspaper,
    palette: Palette, 'panel-bottom': PanelBottom, 'panel-top': PanelTop, 'play-circle': PlayCircle, quote: Quote, search: Search,
    send: Send, 'share-2': Share2, 'user-plus': UserPlus,
};

const icon = computed(() => map[props.name] ?? Square);
</script>

<template>
    <component :is="icon" />
</template>
