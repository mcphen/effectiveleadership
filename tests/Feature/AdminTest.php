<?php

use App\Cms\Html;
use App\Cms\Resources\Registry;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->editor = User::factory()->create(['role' => 'editor']);
});

test('l\'admin est réservée aux utilisateurs connectés', function () {
    $this->get('/admin')->assertRedirect('/login');
    $this->get('/admin/content/pages')->assertRedirect('/login');
});

test('l\'inscription publique est désactivée', function () {
    $this->get('/register')->assertNotFound();
});

test('chaque écran de l\'admin s\'ouvre', function () {
    $this->actingAs($this->admin);

    foreach (array_keys(Registry::all()) as $resource) {
        $this->get("/admin/content/$resource")->assertOk();
        $this->get("/admin/content/$resource/create")->assertOk();
    }

    foreach (['/admin', '/admin/media', '/admin/menus', '/admin/settings', '/admin/settings/theme', '/admin/inbox/messages', '/admin/inbox/applications', '/admin/inbox/subscribers', '/admin/users'] as $url) {
        $this->get($url)->assertOk();
    }
});

test('un éditeur ne gère pas les utilisateurs', function () {
    $this->actingAs($this->editor)->get('/admin/users')->assertForbidden();
});

test('un compte désactivé est déconnecté', function () {
    $this->editor->update(['is_active' => false]);
    $this->actingAs($this->editor)->get('/admin')->assertRedirect('/login');
});

test('créer une page avec des blocs génère les slugs et nettoie le contenu', function () {
    $this->actingAs($this->editor)->post('/admin/content/pages', [
        'title' => ['fr' => 'Notre méthode', 'en' => 'Our method'],
        'slug' => ['fr' => '', 'en' => ''],
        'status' => 'published',
        'blocks' => [
            ['type' => 'text', 'data' => ['title' => ['fr' => 'Titre', 'en' => 'Title'], 'content' => ['fr' => '<p>Bonjour <script>alert(1)</script><strong>ELC</strong></p>', 'en' => '']]],
            ['type' => 'inconnu', 'data' => []],
        ],
    ])->assertRedirect();

    $page = Page::query()->latest('id')->first();

    expect($page->slugFor('fr'))->toBe('notre-methode')
        ->and($page->slugFor('en'))->toBe('our-method')
        ->and($page->blocks)->toHaveCount(1)
        ->and($page->blocks[0]['data']['content']['fr'])->toBe('<p>Bonjour <strong>ELC</strong></p>')
        ->and($page->blocks[0]['settings']['background'])->toBe('white');

    $this->get('/en/our-method')->assertOk()->assertSee('Title');
    $this->get('/fr/notre-methode')->assertOk()->assertSee('Titre');
});

test('deux pages ne peuvent pas avoir le même slug', function () {
    $this->actingAs($this->editor);
    foreach ([1, 2] as $i) {
        $this->post('/admin/content/pages', ['title' => ['fr' => 'Doublon', 'en' => ''], 'status' => 'published']);
    }

    expect(Page::query()->orderBy('id')->get()->map(fn ($p) => $p->slugFor('fr'))->all())->toBe(['doublon', 'doublon-2']);
});

test('le titre français est obligatoire', function () {
    $this->actingAs($this->editor)
        ->post('/admin/content/pages', ['title' => ['fr' => '', 'en' => 'Only English']])
        ->assertSessionHasErrors('title.fr');
});

test('dupliquer crée une copie non publiée', function () {
    $this->actingAs($this->editor);
    $page = Page::query()->create(['title' => ['fr' => 'Original'], 'slug' => ['fr' => 'original'], 'status' => 'published', 'is_home' => true, 'blocks' => []]);

    $this->post("/admin/content/pages/{$page->id}/duplicate")->assertRedirect();

    $copy = Page::query()->latest('id')->first();
    expect($copy->getTranslation('title', 'fr'))->toBe('Original (copie)')
        ->and($copy->status)->toBe('draft')
        ->and($copy->is_home)->toBeFalse()
        ->and($copy->slugFor('fr'))->toBe('original-copie');
});

test('les réglages sont enregistrés et appliqués au site', function () {
    $this->actingAs($this->editor)->put('/admin/settings/theme', [
        'primary' => '#AA0000', 'secondary' => '#008080', 'dark' => '#0C2B3A', 'accent' => '#C9973F',
        'heading_font' => 'Lora', 'body_font' => 'Inter', 'radius' => '10',
    ])->assertRedirect();

    expect(setting('theme.primary'))->toBe('#AA0000')
        ->and(\App\Cms\Theme::cssVariables())->toContain('--brand: 170 0 0')->toContain("'Lora'");
});

test('le nettoyeur HTML retire les attributs et liens dangereux', function () {
    expect(Html::clean('<p onclick="x()">a <a href="javascript:alert(1)">b</a> <a href="page:3" target="_blank">c</a></p><iframe src="x"></iframe>'))
        ->toBe('<p>a <a>b</a> <a href="page:3" target="_blank" rel="noopener">c</a></p>');
});
