// Génère resources/data/icons.json (balisage SVG interne des icônes Lucide utilisées par le site).
// Usage : node scripts/build-icons.mjs
import { readFile, writeFile } from 'node:fs/promises';

// Icônes proposées dans l'admin (champ « icône » des cartes).
export const PICKABLE = [
    'target', 'compass', 'users', 'user-check', 'trending-up', 'bar-chart-3', 'lightbulb', 'award', 'briefcase',
    'building-2', 'globe', 'handshake', 'layers', 'rocket', 'shield-check', 'sparkles', 'graduation-cap', 'book-open',
    'puzzle', 'refresh-cw', 'gauge', 'heart-handshake', 'sprout', 'tree-deciduous', 'network', 'search', 'star',
    'circle-check', 'message-circle', 'landmark', 'workflow', 'crown', 'brain', 'eye', 'route', 'scale', 'mic',
    'presentation', 'clipboard-check', 'chart-line', 'flag', 'key', 'mountain', 'anchor', 'zap', 'goal',
];

// Icônes d'interface utilisées par les gabarits du site.
const UI = [
    'arrow-right', 'arrow-left', 'arrow-up-right', 'chevron-down', 'chevron-left', 'chevron-right', 'menu', 'x', 'mail',
    'phone', 'map-pin', 'linkedin', 'facebook', 'instagram', 'youtube', 'twitter', 'quote', 'plus', 'minus', 'download',
    'calendar', 'check', 'file-text', 'send', 'clock', 'external-link', 'globe-2', 'paperclip', 'play', 'megaphone',
];

const toSvg = ([tag, attrs, children]) => {
    const a = Object.entries(attrs || {}).map(([k, v]) => `${k}="${v}"`).join(' ');
    return `<${tag} ${a}/>`;
};

const out = {};
for (const name of [...new Set([...PICKABLE, ...UI])]) {
    const src = await readFile(`node_modules/lucide/dist/esm/icons/${name}.js`, 'utf8').catch(() => null);
    if (!src) { console.warn('icône introuvable :', name); continue; }
    const body = src.slice(src.indexOf('[', src.indexOf('defaultAttributes,')), src.lastIndexOf(']'));
    const nodes = JSON.parse(body.replace(/(\w+):/g, '"$1":').replace(/"(\w+)":\s*"/g, '"$1": "'));
    out[name] = nodes.map(toSvg).join('');
}
await writeFile('resources/data/icons.json', JSON.stringify({ pickable: PICKABLE.filter((n) => out[n]), icons: out }, null, 0));
console.log(Object.keys(out).length, 'icônes');
