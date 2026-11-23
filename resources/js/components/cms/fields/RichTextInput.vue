<script setup lang="ts">
import LinkInput from '@/components/cms/fields/LinkInput.vue';
import ToggleInput from '@/components/cms/fields/ToggleInput.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import TextAlign from '@tiptap/extension-text-align';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import {
    AlignCenter, AlignLeft, AlignRight, Bold, Heading2, Heading3, Italic, Link2, List, ListOrdered, Minus, Quote, Redo2,
    RemoveFormatting, Underline, Undo2,
} from 'lucide-vue-next';
import { onBeforeUnmount, ref, watch } from 'vue';

const model = defineModel<string | null>();
const props = defineProps<{ compact?: boolean; placeholder?: string }>();

const INTERNAL = /^(page|solution|category|case|post|job):\d+$/;

const editor = useEditor({
    content: model.value ?? '',
    extensions: [
        StarterKit.configure({
            heading: { levels: [2, 3, 4] },
            code: false,
            codeBlock: false,
            link: {
                openOnClick: false,
                autolink: true,
                defaultProtocol: 'https',
                protocols: ['page', 'solution', 'category', 'case', 'post', 'job'],
                isAllowedUri: (url, ctx) => INTERNAL.test(url) || /^(#|\/|mailto:|tel:)/.test(url) || ctx.defaultValidate(url),
                HTMLAttributes: { rel: null, target: null },
            },
        }),
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
    ],
    editorProps: {
        attributes: {
            class: 'prose-admin min-h-[inherit] px-4 py-3 focus:outline-none',
        },
    },
    onUpdate: ({ editor }) => {
        const html = editor.getHTML();
        model.value = html === '<p></p>' ? '' : html;
    },
});

// Changement de valeur depuis l'extérieur (ex. passage FR → EN).
watch(model, (value) => {
    const e = editor.value;
    if (!e) return;
    const current = e.getHTML();
    if ((value ?? '') !== current && !(value === '' && current === '<p></p>')) {
        e.commands.setContent(value ?? '', { emitUpdate: false });
    }
});

onBeforeUnmount(() => editor.value?.destroy());

// Fenêtre d'insertion de lien
const linkOpen = ref(false);
const linkHref = ref<string | null>('');
const linkBlank = ref(false);

function openLink() {
    const attrs = editor.value?.getAttributes('link') ?? {};
    linkHref.value = attrs.href ?? '';
    linkBlank.value = attrs.target === '_blank';
    linkOpen.value = true;
}

function applyLink() {
    const chain = editor.value?.chain().focus().extendMarkRange('link');
    if (!chain) return;
    if (!linkHref.value) {
        chain.unsetLink().run();
    } else {
        chain.setLink({ href: linkHref.value, target: linkBlank.value ? '_blank' : null } as any).run();
    }
    linkOpen.value = false;
}

const btn = (active: boolean) =>
    `inline-flex size-8 items-center justify-center rounded-md transition hover:bg-accent ${active ? 'bg-primary/10 text-primary' : 'text-muted-foreground'}`;
</script>

<template>
    <div class="overflow-hidden rounded-md border border-input bg-background shadow-sm focus-within:ring-1 focus-within:ring-ring dark:bg-input/30">
        <div v-if="editor" class="flex flex-wrap items-center gap-0.5 border-b bg-muted/40 px-1.5 py-1">
            <template v-if="!props.compact">
                <button type="button" :class="btn(editor.isActive('heading', { level: 2 }))" title="Titre" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()">
                    <Heading2 class="size-4" />
                </button>
                <button type="button" :class="btn(editor.isActive('heading', { level: 3 }))" title="Sous-titre" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()">
                    <Heading3 class="size-4" />
                </button>
                <span class="mx-1 h-5 w-px bg-border" />
            </template>
            <button type="button" :class="btn(editor.isActive('bold'))" title="Gras" @click="editor.chain().focus().toggleBold().run()"><Bold class="size-4" /></button>
            <button type="button" :class="btn(editor.isActive('italic'))" title="Italique" @click="editor.chain().focus().toggleItalic().run()"><Italic class="size-4" /></button>
            <button type="button" :class="btn(editor.isActive('underline'))" title="Souligné" @click="editor.chain().focus().toggleUnderline().run()">
                <Underline class="size-4" />
            </button>
            <button type="button" :class="btn(editor.isActive('link'))" title="Lien" @click="openLink"><Link2 class="size-4" /></button>
            <span class="mx-1 h-5 w-px bg-border" />
            <button type="button" :class="btn(editor.isActive('bulletList'))" title="Liste à puces" @click="editor.chain().focus().toggleBulletList().run()">
                <List class="size-4" />
            </button>
            <button type="button" :class="btn(editor.isActive('orderedList'))" title="Liste numérotée" @click="editor.chain().focus().toggleOrderedList().run()">
                <ListOrdered class="size-4" />
            </button>
            <template v-if="!props.compact">
                <button type="button" :class="btn(editor.isActive('blockquote'))" title="Citation" @click="editor.chain().focus().toggleBlockquote().run()">
                    <Quote class="size-4" />
                </button>
                <button type="button" :class="btn(false)" title="Ligne de séparation" @click="editor.chain().focus().setHorizontalRule().run()"><Minus class="size-4" /></button>
                <span class="mx-1 h-5 w-px bg-border" />
                <button type="button" :class="btn(editor.isActive({ textAlign: 'left' }))" title="Aligner à gauche" @click="editor.chain().focus().setTextAlign('left').run()">
                    <AlignLeft class="size-4" />
                </button>
                <button type="button" :class="btn(editor.isActive({ textAlign: 'center' }))" title="Centrer" @click="editor.chain().focus().setTextAlign('center').run()">
                    <AlignCenter class="size-4" />
                </button>
                <button type="button" :class="btn(editor.isActive({ textAlign: 'right' }))" title="Aligner à droite" @click="editor.chain().focus().setTextAlign('right').run()">
                    <AlignRight class="size-4" />
                </button>
            </template>
            <span class="ml-auto" />
            <button type="button" :class="btn(false)" title="Effacer la mise en forme" @click="editor.chain().focus().unsetAllMarks().clearNodes().run()">
                <RemoveFormatting class="size-4" />
            </button>
            <button type="button" :class="btn(false)" title="Annuler" :disabled="!editor.can().undo()" @click="editor.chain().focus().undo().run()"><Undo2 class="size-4" /></button>
            <button type="button" :class="btn(false)" title="Rétablir" :disabled="!editor.can().redo()" @click="editor.chain().focus().redo().run()"><Redo2 class="size-4" /></button>
        </div>
        <EditorContent :editor="editor" :class="props.compact ? 'min-h-24' : 'min-h-40'" />

        <Dialog v-model:open="linkOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader><DialogTitle>Lien</DialogTitle></DialogHeader>
                <LinkInput v-model="linkHref" />
                <ToggleInput v-model="linkBlank" label="Ouvrir dans un nouvel onglet" />
                <DialogFooter class="gap-2">
                    <Button v-if="editor?.isActive('link')" type="button" variant="ghost" class="text-destructive" @click="(linkHref = ''), applyLink()">Retirer le lien</Button>
                    <Button type="button" @click="applyLink">Appliquer</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
