/** Petit client JSON pour les appels hors Inertia (médiathèque, liens internes). */
function xsrf(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

/**
 * Le pare-feu du site (Sucuri / GoDaddy Website Security) bloque les méthodes PUT, PATCH et DELETE,
 * ainsi que l'en-tête X-HTTP-Method-Override. Ces requêtes partent donc en POST, avec un champ
 * « _method » dans le corps, que Laravel interprète nativement.
 */
export function spoofedMethod(method: string): boolean {
    return ['PUT', 'PATCH', 'DELETE'].includes(method.toUpperCase());
}

export function withMethodField(method: string, data: unknown): unknown {
    const value = method.toUpperCase();

    if (data instanceof FormData) {
        data.set('_method', value);
        return data;
    }
    if (typeof data === 'string') {
        try {
            return JSON.stringify({ ...JSON.parse(data || '{}'), _method: value });
        } catch {
            return data;
        }
    }
    if (data && typeof data === 'object') {
        return { ...(data as Record<string, unknown>), _method: value };
    }
    return { _method: value };
}

export async function http<T = any>(url: string, options: RequestInit = {}): Promise<T> {
    const method = (options.method ?? 'GET').toUpperCase();
    let body = options.body;
    if (spoofedMethod(method)) {
        const spoofed = withMethodField(method, body);
        body = spoofed instanceof FormData || typeof spoofed === 'string' ? spoofed : JSON.stringify(spoofed);
    }

    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        method: spoofedMethod(method) ? 'POST' : method,
        body,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrf(),
            ...(body && !(body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
            ...(options.headers ?? {}),
        },
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const errors = data?.errors ? Object.values(data.errors).flat().join(' ') : data?.message;
        throw new Error(errors || `Erreur ${response.status}`);
    }

    return data as T;
}
