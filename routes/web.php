<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Site\FormController;
use App\Http\Controllers\Site\SiteController;
use App\Http\Controllers\Site\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Administration
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    // Contenus (pages, offres, réalisations, publications…)
    Route::controller(Admin\ResourceController::class)->prefix('content/{resource}')->name('resources.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('reorder', 'reorder')->name('reorder');
        Route::get('{id}/edit', 'edit')->whereNumber('id')->name('edit');
        Route::put('{id}', 'update')->whereNumber('id')->name('update');
        Route::delete('{id}', 'destroy')->whereNumber('id')->name('destroy');
        Route::post('{id}/duplicate', 'duplicate')->whereNumber('id')->name('duplicate');
    });

    // Médiathèque
    Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
    Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
    Route::patch('media/{media}', [Admin\MediaController::class, 'update'])->name('media.update');
    Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');

    // Menus
    Route::get('menus/{location?}', [Admin\MenuController::class, 'edit'])->name('menus.edit');
    Route::put('menus/{location}', [Admin\MenuController::class, 'update'])->name('menus.update');
    Route::get('link-targets', [Admin\MenuController::class, 'linkTargets'])->name('link-targets');

    // Réglages du site
    Route::get('settings/{tab?}', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings/{tab}', [Admin\SettingsController::class, 'update'])->name('settings.update');

    // Messages reçus
    Route::controller(Admin\InboxController::class)->prefix('inbox')->name('inbox.')->group(function () {
        Route::get('messages', 'messages')->name('messages');
        Route::patch('messages/{message}', 'markMessage')->name('messages.mark');
        Route::delete('messages/{message}', 'destroyMessage')->name('messages.destroy');
        Route::get('applications', 'applications')->name('applications');
        Route::patch('applications/{application}', 'markApplication')->name('applications.mark');
        Route::get('applications/{application}/cv', 'downloadCv')->name('applications.cv');
        Route::delete('applications/{application}', 'destroyApplication')->name('applications.destroy');
        Route::get('subscribers', 'subscribers')->name('subscribers');
        Route::delete('subscribers/{subscriber}', 'destroySubscriber')->name('subscribers.destroy');
        Route::get('export/{type}', 'export')->name('export');
    });

    // Utilisateurs (administrateurs uniquement)
    Route::middleware('admin')->group(function () {
        Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::post('users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    });
});

// Compatibilité avec le kit de démarrage.
Route::redirect('dashboard', '/admin')->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Site public
|--------------------------------------------------------------------------
*/
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');

// « / » → langue préférée du navigateur
Route::get('/', [SiteController::class, 'root'])->name('root');

Route::prefix('{locale}')
    ->where(['locale' => implode('|', array_keys(config('elc.locales')))])
    ->middleware('locale')
    ->name('site.')
    ->group(function () {
        Route::get('/', [SiteController::class, 'home'])->name('home');

        Route::post('contact', [FormController::class, 'contact'])->middleware('throttle:6,1')->name('contact');
        Route::post('newsletter', [FormController::class, 'newsletter'])->middleware('throttle:6,1')->name('newsletter');
        Route::post('apply', [FormController::class, 'apply'])->middleware('throttle:4,1')->name('apply');

        // Préfixes traduits (realisations / case-studies…) résolus dans le contrôleur.
        Route::get('{segment}/{slug}/{child?}', [SiteController::class, 'section'])->name('section');
        Route::get('{slug}', [SiteController::class, 'page'])->name('page');
    });
