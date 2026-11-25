import { inject, provide, ref, type Ref } from 'vue';

const KEY = Symbol('editLocale');

/** Langue en cours d'édition dans un formulaire (onglets FR / EN). */
export function provideEditLocale(initial = 'en'): Ref<string> {
    const locale = ref(initial);
    provide(KEY, locale);
    return locale;
}

export function useEditLocale(): Ref<string> {
    return inject<Ref<string>>(KEY, ref('en'));
}
