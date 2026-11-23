<script setup lang="ts">
import PageHeader from '@/components/cms/PageHeader.vue';
import SelectInput from '@/components/cms/fields/SelectInput.vue';
import ToggleInput from '@/components/cms/fields/ToggleInput.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface U {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
}

defineProps<{ users: U[]; roles: Record<string, string> }>();

const me = (usePage().props as any).auth.user;
const open = ref(false);
const editing = ref<U | null>(null);
const form = useForm({ name: '', email: '', role: 'editor', is_active: true, password: '' });

function create() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    open.value = true;
}

function edit(u: U) {
    editing.value = u;
    form.clearErrors();
    Object.assign(form, { name: u.name, email: u.email, role: u.role, is_active: u.is_active, password: '' });
    open.value = true;
}

function submit() {
    const options = { preserveScroll: true, onSuccess: () => (open.value = false) };
    if (editing.value) form.put(route('admin.users.update', editing.value.id), options);
    else form.post(route('admin.users.store'), options);
}

const destroy = (u: U) => confirm(`Supprimer le compte de ${u.name} ?`) && router.delete(route('admin.users.destroy', u.id), { preserveScroll: true });

const inputClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring';
</script>

<template>
    <Head title="Utilisateurs" />
    <AppLayout :breadcrumbs="[{ title: 'Utilisateurs', href: route('admin.users.index') }]">
        <div class="mx-auto w-full max-w-4xl space-y-6 p-4 md:p-6">
            <PageHeader title="Utilisateurs" description="Personnes ayant accès à l'administration. Les éditeurs gèrent le contenu ; les administrateurs gèrent aussi les comptes.">
                <Button @click="create"><Plus /> Ajouter un utilisateur</Button>
            </PageHeader>

            <div class="divide-y overflow-hidden rounded-xl border bg-card">
                <div v-for="u in users" :key="u.id" class="flex items-center gap-4 px-5 py-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                        {{ u.name.slice(0, 1).toUpperCase() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium">
                            {{ u.name }} <span v-if="u.id === me.id" class="text-xs font-normal text-muted-foreground">(vous)</span>
                        </p>
                        <p class="truncate text-sm text-muted-foreground">{{ u.email }}</p>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="u.role === 'admin' ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'">
                        {{ roles[u.role] ?? u.role }}
                    </span>
                    <span v-if="!u.is_active" class="rounded-full bg-destructive/10 px-2 py-0.5 text-xs text-destructive">Désactivé</span>
                    <Button variant="ghost" size="icon" title="Modifier" @click="edit(u)"><Pencil /></Button>
                    <Button v-if="u.id !== me.id" variant="ghost" size="icon" title="Supprimer" class="text-muted-foreground hover:text-destructive" @click="destroy(u)">
                        <Trash2 />
                    </Button>
                </div>
            </div>
        </div>

        <Dialog v-model:open="open">
            <DialogContent class="max-w-md">
                <DialogHeader><DialogTitle>{{ editing ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</DialogTitle></DialogHeader>
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Nom</label>
                        <input v-model="form.name" type="text" :class="inputClass" required />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">E-mail</label>
                        <input v-model="form.email" type="email" :class="inputClass" required />
                        <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Rôle</label>
                        <SelectInput v-model="form.role" :options="roles" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Mot de passe</label>
                        <input v-model="form.password" type="password" autocomplete="new-password" :class="inputClass" :required="!editing" />
                        <p class="text-xs text-muted-foreground">{{ editing ? 'Laisser vide pour ne pas le changer. ' : '' }}8 caractères minimum.</p>
                        <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                    </div>
                    <ToggleInput v-if="editing && editing.id !== me.id" v-model="form.is_active" label="Compte actif" />
                    <DialogFooter>
                        <Button type="submit" :disabled="form.processing">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
