import { http } from '@/lib/http';
import type { LinkTarget } from '@/types/cms';
import { ref } from 'vue';

const targets = ref<LinkTarget[]>([]);
let promise: Promise<void> | null = null;

/** Liste (mise en cache) des contenus internes vers lesquels on peut faire un lien. */
export function useLinkTargets() {
    if (!promise) {
        promise = http<LinkTarget[]>(route('admin.link-targets'))
            .then((data) => {
                targets.value = data;
            })
            .catch(() => {
                promise = null;
            });
    }

    const find = (value: string | null | undefined) => targets.value.find((t) => t.value === value);

    return { targets, find, reload: () => ((promise = null), useLinkTargets()) };
}
