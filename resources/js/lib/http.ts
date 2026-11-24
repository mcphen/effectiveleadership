/** Petit client JSON pour les appels hors Inertia (médiathèque, liens internes). */
function xsrf(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

export async function http<T = any>(url: string, options: RequestInit = {}): Promise<T> {
    // PUT/PATCH/DELETE passent en POST + X-HTTP-Method-Override (bloqués par le pare-feu Sucuri), voir app.ts.
    const method = (options.method ?? 'GET').toUpperCase();
    const override = ['PUT', 'PATCH', 'DELETE'].includes(method);

    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        method: override ? 'POST' : method,
        headers: {
            ...(override ? { 'X-HTTP-Method-Override': method } : {}),
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrf(),
            ...(options.body && !(options.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
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
