<?php

use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Solution;
use App\Models\SolutionCategory;
use Database\Seeders\ContentSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(ContentSeeder::class);
});

test('la racine redirige vers la langue du navigateur', function () {
    $this->get('/', ['Accept-Language' => 'en-GB,en;q=0.9'])->assertRedirect('/en');
    $this->get('/', ['Accept-Language' => 'fr-FR'])->assertRedirect('/fr');
});

test('toutes les pages publiées s\'affichent dans les deux langues', function () {
    $entities = collect()
        ->concat(Page::query()->published()->get())
        ->concat(SolutionCategory::query()->published()->get())
        ->concat(Solution::query()->published()->get());

    expect($entities)->not->toBeEmpty();

    foreach ($entities as $entity) {
        foreach (['fr', 'en'] as $locale) {
            $this->get(parse_url($entity->url($locale), PHP_URL_PATH))->assertOk();
        }
    }
});

test('une offre affiche sa traduction anglaise et le lien vers la version française', function () {
    $solution = Solution::query()->first();

    $this->get(parse_url($solution->url('en'), PHP_URL_PATH))
        ->assertOk()
        ->assertSee($solution->getTranslation('title', 'en'))
        ->assertSee('hreflang="fr"', false)
        ->assertSee($solution->url('fr'), false);
});

test('un brouillon est invisible pour les visiteurs mais visible en aperçu', function () {
    $draft = Page::query()->where('status', 'draft')->first();
    $path = parse_url($draft->url('fr'), PHP_URL_PATH);

    $this->get($path)->assertNotFound();

    $this->actingAs(\App\Models\User::factory()->create(['role' => 'admin']))
        ->get($path)->assertOk()->assertSee('noindex', false);
});

test('une page inconnue renvoie une 404', function () {
    $this->get('/fr/page-qui-n-existe-pas')->assertNotFound();
    $this->get('/de')->assertNotFound();
});

test('le formulaire de contact enregistre le message', function () {
    $this->from('/fr/contact')->post('/fr/contact', [
        'name' => 'Awa Diop', 'email' => 'awa@example.com', 'message' => 'Bonjour, nous souhaitons un diagnostic.',
    ])->assertRedirect()->assertSessionHas('form_success', 'contact');

    expect(ContactMessage::query()->where('email', 'awa@example.com')->first())
        ->not->toBeNull()->locale->toBe('fr');
});

test('le formulaire de contact valide les champs obligatoires', function () {
    $this->from('/fr/contact')->post('/fr/contact', ['name' => ''])->assertSessionHasErrorsIn('contact', ['name', 'email', 'message']);
    expect(ContactMessage::query()->count())->toBe(0);
});

test('le champ piège bloque les robots sans rien enregistrer', function () {
    $this->post('/fr/contact', ['name' => 'Bot', 'email' => 'bot@spam.io', 'message' => 'spam', 'website' => 'http://spam.io'])
        ->assertSessionHas('form_success');
    expect(ContactMessage::query()->count())->toBe(0);
});

test('inscription à la newsletter sans doublon', function () {
    $this->post('/en/newsletter', ['email' => 'Reader@Example.com']);
    $this->post('/en/newsletter', ['email' => 'reader@example.com']);

    expect(NewsletterSubscriber::query()->count())->toBe(1)
        ->and(NewsletterSubscriber::query()->first()->locale)->toBe('en');
});

test('une candidature spontanée stocke le CV hors du dossier public', function () {
    Storage::fake('local');

    $this->post('/fr/apply', [
        'name' => 'Moussa Ba', 'email' => 'moussa@example.com',
        'cv' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
    ])->assertSessionHas('form_success', 'apply');

    $application = JobApplication::query()->first();
    expect($application->job_offer_id)->toBeNull();
    Storage::disk('local')->assertExists($application->cv_path);
});

test('le plan du site liste les pages publiées', function () {
    $this->get('/sitemap.xml')->assertOk()->assertSee('/fr/qui-sommes-nous')->assertSee('/en/who-we-are')->assertDontSee('mentions-legales');
});
