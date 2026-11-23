<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user?->only(['id', 'name', 'email', 'role']),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'site' => fn () => $user ? [
                'name' => setting('identity.site_name') ?: config('app.name'),
                'logo' => media_url(setting('identity.logo')),
                'url' => localized_url('', default_locale()),
                'locales' => locales(),
                'defaultLocale' => default_locale(),
            ] : null,
            'counts' => fn () => $user ? [
                'messages' => \App\Models\ContactMessage::query()->whereNull('read_at')->count(),
                'applications' => \App\Models\JobApplication::query()->whereNull('read_at')->count(),
            ] : null,
        ];
    }
}
