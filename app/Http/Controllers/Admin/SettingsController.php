<?php

namespace App\Http\Controllers\Admin;

use App\Cms\Fields;
use App\Cms\SettingsSchema;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(?string $tab = null): Response
    {
        $tabs = SettingsSchema::tabs();
        $tab ??= array_key_first($tabs);
        abort_unless(isset($tabs[$tab]), 404);

        return Inertia::render('admin/Settings', [
            'tabs' => collect($tabs)->map(fn ($t) => ['label' => $t['label'], 'icon' => $t['icon']]),
            'tab' => $tab,
            'fields' => $tabs[$tab]['fields'],
            'values' => static::values($tab),
        ]);
    }

    public function update(Request $request, string $tab): RedirectResponse
    {
        $tabs = SettingsSchema::tabs();
        abort_unless(isset($tabs[$tab]), 404);
        $fields = $tabs[$tab]['fields'];

        $data = $request->validate(Fields::rules($fields), [], Fields::attributes($fields));

        Setting::put($tab, collect($fields)->mapWithKeys(fn ($f) => [
            $f['name'] => Fields::sanitize($f, $data[$f['name']] ?? Fields::emptyValue($f)),
        ])->all());

        return back()->with('success', 'Réglages enregistrés.');
    }

    /** Valeurs d'un onglet, complétées par les valeurs par défaut. */
    public static function values(string $tab): array
    {
        $stored = (array) Setting::get($tab, []);

        return collect(SettingsSchema::tabs()[$tab]['fields'])->mapWithKeys(fn ($f) => [
            $f['name'] => $stored[$f['name']] ?? Fields::emptyValue($f),
        ])->all();
    }
}
