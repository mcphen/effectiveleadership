export type Translatable = Record<string, string>;

export interface Field {
    name: string;
    type:
        | 'text' | 'textarea' | 'richtext' | 'number' | 'range' | 'toggle' | 'select' | 'color' | 'date'
        | 'image' | 'file' | 'link' | 'icon' | 'slug' | 'repeater' | 'blocks' | 'seo' | 'email';
    label: string;
    t?: boolean;
    required?: boolean;
    help?: string;
    width?: 'half' | 'full';
    default?: unknown;
    options?: Record<string, string>;
    placeholder?: string;
    fields?: Field[];
    item_label?: string;
    max?: number;
    rows?: number;
    min?: number;
    step?: number;
    suffix?: string;
    side?: boolean;
    from?: string;
    compact?: boolean;
}

export interface BlockDefinition {
    type: string;
    label: string;
    description: string;
    group: string;
    icon: string;
    fields: Field[];
    settings: Record<string, unknown>;
}

export interface Block {
    id: string;
    type: string;
    hidden: boolean;
    data: Record<string, any>;
    settings: Record<string, any>;
}

export interface MediaItem {
    id: number;
    path: string;
    url: string;
    filename: string;
    mime: string;
    is_image: boolean;
    size: number;
    width: number | null;
    height: number | null;
    alt: Translatable;
    created_at: string;
}

export interface LinkTarget {
    value: string;
    label: string;
    group: string;
    url: string;
}

export interface ResourceMeta {
    slug: string;
    label: string;
    singular: string;
    feminine: boolean;
    icon: string;
    sortable: boolean;
    titleField: string;
}
