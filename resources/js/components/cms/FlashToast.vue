<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, X } from 'lucide-vue-next';
import { onBeforeUnmount, ref, watch } from 'vue';

/** Notification « Modifications enregistrées » après chaque action. */
const page = usePage<any>();
const toast = ref<{ type: 'success' | 'error'; text: string } | null>(null);
let timer: ReturnType<typeof setTimeout>;

function show(type: 'success' | 'error', text: string | null | undefined) {
    if (!text) return;
    toast.value = { type, text };
    clearTimeout(timer);
    timer = setTimeout(() => (toast.value = null), type === 'error' ? 7000 : 3500);
}

watch(
    () => page.props.flash,
    (flash: any) => {
        show('success', flash?.success);
        show('error', flash?.error);
    },
    { immediate: true, deep: true },
);

// Erreurs de validation : message générique.
const off = router.on('error', (event: any) => {
    const count = Object.keys(event.detail?.errors ?? {}).length;
    show('error', count ? `Le formulaire contient ${count} erreur${count > 1 ? 's' : ''}. Vérifiez les champs signalés en rouge.` : 'Une erreur est survenue.');
});
onBeforeUnmount(() => off());
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="toast"
            role="status"
            class="fixed bottom-5 right-5 z-[60] flex max-w-sm items-start gap-3 rounded-lg border bg-background px-4 py-3 text-sm shadow-lg"
            :class="toast.type === 'error' ? 'border-destructive/40' : 'border-emerald-500/40'"
        >
            <CircleCheck v-if="toast.type === 'success'" class="mt-px size-5 shrink-0 text-emerald-600" />
            <CircleAlert v-else class="mt-px size-5 shrink-0 text-destructive" />
            <p class="flex-1">{{ toast.text }}</p>
            <button type="button" class="text-muted-foreground hover:text-foreground" @click="toast = null"><X class="size-4" /></button>
        </div>
    </Transition>
</template>
