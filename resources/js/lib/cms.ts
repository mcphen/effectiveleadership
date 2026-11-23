import type { Block, BlockDefinition, Field } from '@/types/cms';
import { usePage } from '@inertiajs/vue3';

export const uid = (prefix = '') => prefix + Math.random().toString(36).slice(2, 10);

export const clone = <T>(value: T): T => JSON.parse(JSON.stringify(value ?? null));

export function locales(): Record<string, string> {
    return ((usePage().props as any).site?.locales as Record<string, string>) ?? { fr: 'Français', en: 'English' };
}

export function defaultLocale(): string {
    return ((usePage().props as any).site?.defaultLocale as string) ?? 'fr';
}

/** Valeur vide d'un champ, en respectant la structure traduisible {fr, en}. */
export function emptyValue(field: Field): any {
    if (field.default !== undefined && field.default !== null) {
        return clone(field.default);
    }
    if (field.t) {
        return Object.fromEntries(Object.keys(locales()).map((l) => [l, '']));
    }
    switch (field.type) {
        case 'repeater':
        case 'blocks':
            return [];
        case 'toggle':
            return false;
        case 'seo':
            return { title: { fr: '', en: '' }, description: { fr: '', en: '' }, image: null, noindex: false };
        default:
            return null;
    }
}

export function emptyItem(fields: Field[]): Record<string, any> {
    return { _id: uid(), ...Object.fromEntries(fields.map((f) => [f.name, emptyValue(f)])) };
}

/** Complète un objet avec les valeurs par défaut des champs manquants. */
export function withDefaults(fields: Field[], data: Record<string, any> | null | undefined): Record<string, any> {
    const out: Record<string, any> = { ...(data ?? {}) };
    for (const f of fields) {
        if (out[f.name] === undefined || out[f.name] === null) {
            out[f.name] = emptyValue(f);
        } else if (f.t && typeof out[f.name] === 'string') {
            out[f.name] = { [defaultLocale()]: out[f.name] };
        }
    }
    return out;
}

export function newBlock(def: BlockDefinition): Block {
    return { id: uid('b_'), type: def.type, hidden: false, data: emptyItem(def.fields), settings: clone(def.settings) };
}

/** Texte d'une valeur traduisible dans une langue, avec repli sur la langue par défaut. */
export function tr(value: any, locale?: string): string {
    if (value === null || value === undefined) return '';
    if (typeof value !== 'object') return String(value);
    const l = locale ?? defaultLocale();
    return value[l] || value[defaultLocale()] || '';
}

export function stripHtml(html: string): string {
    return (html ?? '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

/** Indique si un champ traduisible a une valeur dans la langue donnée (récursif pour répéteurs / blocs). */
export function isTranslated(value: any, locale: string): boolean {
    return typeof value === 'object' && value !== null && !!stripHtml(value[locale] ?? '');
}

export function formatBytes(bytes: number): string {
    if (bytes < 1024) return bytes + ' o';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' Ko';
    return (bytes / 1024 / 1024).toFixed(1) + ' Mo';
}
