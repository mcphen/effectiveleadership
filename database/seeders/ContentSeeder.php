<?php

namespace Database\Seeders;

use App\Cms\Blocks;
use App\Cms\Fields;
use App\Models\ClientReference;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Solution;
use App\Models\SolutionCategory;
use App\Support\Headline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Contenu initial du site ELC, issu des documents fournis par le client
 * (database/seeders/content/content.json + logos des références).
 *
 * Relançable : php artisan db:seed --class=ContentSeeder (efface et recrée le contenu).
 */
class ContentSeeder extends Seeder
{
    protected array $c;

    /** @var array<string, Page> */
    protected array $pages = [];

    /** @var array<string, SolutionCategory> */
    protected array $categories = [];

    public function run(): void
    {
        $this->c = json_decode(file_get_contents(__DIR__.'/content/content.json'), true, flags: JSON_THROW_ON_ERROR);

        foreach ([Menu::class, Solution::class, SolutionCategory::class, Page::class, ClientReference::class] as $model) {
            $model::query()->delete();
        }

        // Ancien logo (sarcelle) remplacé par le logo bleu officiel : retiré de la médiathèque.
        Media::query()->where('path', 'media/seed/elc-logo.png')->delete();
        Storage::disk('public')->delete('media/seed/elc-logo.png');

        $logo = $this->media(__DIR__.'/content/brand/elc-logo-blue.png', 'Effective Leadership Consulting');
        $this->settings($logo);
        $this->solutions();
        $this->references();
        $this->pages();
        $this->headlines();
        $this->menus();
        $this->news();
    }

    // ───────────────────────── Réglages ─────────────────────────

    protected function settings(string $logo): void
    {
        $contact = $this->c['contact'];

        Setting::put('identity', [
            'site_name' => 'Effective Leadership Consulting',
            'tagline' => $this->c['home']['tagline'],
            'logo' => $logo,
            'logo_light' => null,
            'favicon' => null,
            'logo_height' => 52,
        ]);

        Setting::put('theme', [
            'primary' => '#156080', 'secondary' => '#008080', 'dark' => '#145773', 'accent' => '#C9973F',
            'heading_font' => 'Merriweather', 'body_font' => 'Source Sans 3', 'radius' => '4',
        ]);

        Setting::put('header', [
            'cta_label' => $this->t('Prendre rendez-vous', 'Book a meeting'),
            'cta_link' => 'page:0', // remplacé après création de la page Contact
            'topbar' => true,
        ]);

        Setting::put('contact', [
            'address' => $contact['address'],
            'email' => $contact['email'],
            'phones' => collect($contact['phones'])->map(fn ($n) => ['_id' => Str::random(8), 'number' => $n])->all(),
            'hours' => $this->t('', ''), // non communiqués par le client
            'map_query' => 'Liberté 6 Extension, Dakar, Sénégal',
            'notify_email' => null,
        ]);

        Setting::put('socials', ['linkedin' => null, 'facebook' => null, 'instagram' => null, 'youtube' => null, 'twitter' => null]);

        Setting::put('footer', [
            'about' => $this->t(
                'Cabinet de conseil en leadership et management au service du développement de leaders et d\'organisations efficaces en Afrique.',
                'A leadership and management consulting firm dedicated to developing effective leaders and organizations in Africa.'
            ),
            'newsletter' => true,
            'newsletter_title' => $this->t('Recevez nos publications', 'Get our publications'),
            'copyright' => $this->t('© {year} Effective Leadership Consulting.', '© {year} Effective Leadership Consulting.'),
        ]);

        Setting::put('seo', [
            'title_suffix' => ' | ELC',
            'description' => $this->c['home']['intro'],
            'image' => null,
            'analytics' => null,
        ]);
    }

    // ───────────────────────── Solutions ─────────────────────────

    protected function solutions(): void
    {
        foreach ($this->c['poles'] as $p => $pole) {
            $poleModel = SolutionCategory::query()->create([
                'title' => $pole['title'],
                'slug' => $this->slugs($pole['title']),
                'summary' => $pole['summary'],
                'blocks' => [],
                'is_published' => true,
                'sort' => $p + 1,
            ]);
            $this->categories[$pole['key']] = $poleModel;

            foreach ($pole['categories'] as $k => $category) {
                $categoryModel = SolutionCategory::query()->create([
                    'parent_id' => $poleModel->id,
                    'title' => $category['title'],
                    'slug' => $this->slugs($category['title']),
                    'summary' => $category['summary'],
                    'blocks' => [],
                    'is_published' => true,
                    'sort' => $k + 1,
                ]);
                $this->categories[$category['key']] = $categoryModel;

                foreach ($category['offers'] as $o => $offer) {
                    Solution::query()->create([
                        'solution_category_id' => $categoryModel->id,
                        'title' => $offer['title'],
                        'slug' => $this->slugs($offer['title']),
                        'subtitle' => $offer['subtitle'],
                        'signature' => $offer['signature'],
                        'summary' => $offer['summary'],
                        'blocks' => $this->offerBlocks($offer),
                        'is_published' => true,
                        'is_featured' => $o === 0,
                        'sort' => $o + 1,
                    ]);
                }
            }
        }
    }

    /** Transforme les sections d'un document d'offre en blocs. */
    protected function offerBlocks(array $offer): array
    {
        $blocks = [];
        $light = false;

        foreach ($offer['sections'] as $section) {
            $blocks[] = $this->block('text', [
                'title' => $section['heading'],
                'content' => $section['html'],
                'width' => 'narrow',
            ], ['background' => $light ? 'light' : 'white', 'spacing' => 'normal']);
            $light = ! $light;
        }

        if (! empty($offer['steps'])) {
            $blocks[] = $this->block('steps', [
                'eyebrow' => $this->t('Notre démarche', 'Our approach'),
                'title' => $this->t('Comment nous procédons', 'How we work'),
                'layout' => count($offer['steps']) > 4 ? 'timeline' : 'columns',
                'items' => $offer['steps'],
            ], ['background' => 'tint']);
        }

        if (! empty($offer['benefits'])) {
            $blocks[] = $this->block('checklist', [
                'eyebrow' => $this->t('Résultats', 'Outcomes'),
                'title' => $this->t('Bénéfices attendus', 'Expected benefits'),
                'columns' => '2',
                'items' => collect($offer['benefits'])->map(fn ($b) => ['text' => $b])->all(),
            ]);
        }

        if (! empty($offer['signature'])) {
            $blocks[] = $this->block('quote', ['text' => $offer['signature'], 'author' => $offer['title']], ['background' => 'dark']);
        }

        return Fields::sanitizeBlocks($blocks);
    }

    // ───────────────────────── Références ─────────────────────────

    protected function references(): void
    {
        foreach ($this->c['references'] as $i => $reference) {
            ClientReference::query()->create([
                'name' => $reference['name'],
                'logo' => $this->media(__DIR__.'/content/'.$reference['logo'], $reference['name']),
                'sector' => $reference['sector'],
                'is_published' => true,
                'sort' => $i + 1,
            ]);
        }
    }

    // ───────────────────────── Pages ─────────────────────────

    protected function pages(): void
    {
        $about = $this->c['about'];
        $sections = collect($about['sections'])->keyBy(fn ($s) => Str::slug($s['heading']['fr'] ?? ''));

        // Contact (créée d'abord : les autres pages y renvoient)
        $contact = $this->page('contact', $this->t('Contact', 'Contact'), [
            $this->block('page_header', [
                'eyebrow' => $this->t('Contact', 'Contact'),
                'title' => $this->t('Parlons de vos enjeux', 'Let\'s talk about your challenges'),
                'text' => $this->t(
                    'Une question, un projet d\'accompagnement, une demande de formation ? Notre équipe vous répond rapidement.',
                    'A question, a support project, a training request? Our team will get back to you quickly.'
                ),
            ]),
            $this->block('contact', [
                'eyebrow' => $this->t('Écrivez-nous', 'Write to us'),
                'title' => $this->t('Envoyez-nous un message', 'Send us a message'),
                'show_info' => true,
                'show_map' => true,
                'subjects' => [
                    ['label' => $this->t('Conseil – ELC Advisory', 'Consulting – ELC Advisory')],
                    ['label' => $this->t('Formation & développement du leadership', 'Training & leadership development')],
                    ['label' => $this->t('Coaching exécutif', 'Executive coaching')],
                    ['label' => $this->t('Partenariat', 'Partnership')],
                    ['label' => $this->t('Autre demande', 'Other request')],
                ],
            ]),
        ]);
        $contactLink = 'page:'.$contact->id;
        Setting::put('header', [...(array) setting('header'), 'cta_link' => $contactLink]);

        // Accueil
        $home = $this->c['home'];
        $showcaseLinks = ['category:'.$this->categories['institute']->id, 'category:'.$this->categories['advisory']->id, null];
        $this->page('accueil', $this->t('Accueil', 'Home'), [
            $this->block('hero', [
                'eyebrow' => $this->t('Effective Leadership Consulting', 'Effective Leadership Consulting'),
                // Une signature par ligne : « Developing Leaders. » / « Transforming Organizations. » / « Creating Impact. »
                'title' => collect($home['tagline'])->map(fn ($t) => str_replace('. ', '.
', $t))->all(),
                'text' => $home['intro'],
                'height' => 'large',
                'buttons' => [
                    ['label' => $this->t('Découvrir nos solutions', 'Explore our solutions'), 'url' => 'page:{solutions}', 'style' => 'primary'],
                    ['label' => $this->t('Nous contacter', 'Contact us'), 'url' => $contactLink, 'style' => 'outline'],
                ],
            ]),
            $this->block('cards', [
                'eyebrow' => $this->t('Notre promesse', 'Our promise'),
                'title' => $this->t('Trois engagements au service de votre performance', 'Three commitments to drive your performance'),
                'columns' => '3',
                'style' => 'numbered',
                'items' => collect($home['showcases'])->map(fn ($s, $i) => [
                    'title' => $s['title'],
                    'text' => $s['text'],
                    'link_label' => collect($s['cta'])->map(fn ($v) => trim(str_replace('→', '', $v)))->all(),
                    'url' => $showcaseLinks[$i] ?? 'page:{realisations}',
                ])->all(),
            ], ['background' => 'light']),
            $this->block('text_image', [
                'eyebrow' => $this->t('Qui sommes-nous', 'Who we are'),
                'title' => $this->t('Un Cabinet de Conseil en Leadership et Management', 'A Leadership and Management Consulting Firm'),
                'content' => $sections['qui-sommes-nous']['html'] ?? $this->t('', ''),
                'image_position' => 'right',
                'buttons' => [['label' => $this->t('En savoir plus sur ELC', 'More about ELC'), 'url' => 'page:{qui-sommes-nous}', 'style' => 'primary']],
            ]),
            $this->block('stats', [
                'eyebrow' => $this->t('ELC en chiffres', 'ELC in figures'),
                'title' => $this->t('Un impact mesurable', 'A measurable impact'),
                'items' => $about['stats'],
            ], ['background' => 'dark']),
            $this->block('solutions', [
                'eyebrow' => $this->t('Nos solutions', 'Our solutions'),
                'title' => $this->t('Deux pôles complémentaires', 'Two complementary practices'),
                'intro' => $this->t(
                    'ELC Advisory accompagne les dirigeants et les organisations dans leurs transformations ; ELC Institute développe les personnes et produit la connaissance.',
                    'ELC Advisory supports leaders and organizations through their transformations; ELC Institute develops people and produces knowledge.'
                ),
                'source' => 'poles',
            ]),
            $this->block('solutions', [
                'eyebrow' => $this->t('Offres phares', 'Flagship offerings'),
                'title' => $this->t('Nos offres signature', 'Our signature offerings'),
                'source' => 'featured',
                'columns' => '3',
            ], ['background' => 'light']),
            $this->block('references', [
                'eyebrow' => $this->t('Références', 'References'),
                'title' => $this->t('Ils nous font confiance', 'They trust us'),
                'layout' => 'marquee',
                'grayscale' => true,
            ]),
            $this->block('cta', [
                'title' => $this->t('Parlons de votre projet', 'Let\'s talk about your project'),
                'text' => $this->t(
                    'Un échange avec nos consultants pour définir ensemble la démarche la plus adaptée à votre organisation.',
                    'A conversation with our consultants to design together the approach that best fits your organisation.'
                ),
                'buttons' => [['label' => $this->t('Prendre rendez-vous', 'Book a meeting'), 'url' => $contactLink, 'style' => 'primary']],
            ], ['background' => 'primary']),
        ], isHome: true);

        // Qui sommes-nous
        $values = collect($about['values'])->map(fn ($v, $i) => [
            'icon' => ['shield-check', 'sprout', 'heart-handshake', 'globe', 'users', 'lightbulb', 'award'][$i] ?? 'star',
            'title' => $v['title'],
            'text' => $v['text'] ?? $this->t('', ''),
        ])->all();

        $this->page('qui-sommes-nous', $about['title'], array_filter([
            $this->block('page_header', [
                'eyebrow' => $this->t('À propos', 'About us'),
                'title' => $about['title'],
                'text' => $this->t(
                    'Un cabinet de conseil en leadership et management au service du développement de leaders et d\'organisations efficaces en Afrique.',
                    'A leadership and management consulting firm dedicated to developing effective leaders and organizations in Africa.'
                ),
            ]),
            isset($sections['qui-sommes-nous']) ? $this->block('text_image', [
                'eyebrow' => $this->t('Notre raison d\'être', 'Our purpose'),
                'title' => $this->t('Des leaders efficaces pour une Afrique qui avance', 'Effective leaders for a thriving Africa'),
                'content' => $sections['qui-sommes-nous']['html'],
            ]) : null,
            $this->block('cards', [
                'columns' => '2',
                'style' => 'accent',
                'items' => array_values(array_filter([
                    isset($sections['notre-vision']) ? ['icon' => 'eye', 'title' => $sections['notre-vision']['heading'], 'text' => $this->plain($sections['notre-vision']['html'])] : null,
                    isset($sections['notre-mission']) ? ['icon' => 'target', 'title' => $sections['notre-mission']['heading'], 'text' => $this->plain($sections['notre-mission']['html'])] : null,
                ])),
            ], ['background' => 'light']),
            $this->block('cards', [
                'eyebrow' => $this->t('Nos valeurs', 'Our values'),
                'title' => $this->t('Ce qui guide notre action', 'What guides our work'),
                'columns' => '3',
                'style' => 'bordered',
                'items' => $values,
            ]),
            $this->block('stats', ['eyebrow' => $this->t('ELC en chiffres', 'ELC in figures'), 'items' => $about['stats']], ['background' => 'dark']),
            isset($sections['notre-equipe']) ? $this->block('text', [
                'eyebrow' => $this->t('L\'équipe', 'The team'),
                'title' => $sections['notre-equipe']['heading'],
                'content' => $sections['notre-equipe']['html'],
                'width' => 'normal',
            ]) : null,
            isset($sections['consultants-prestataires']) ? $this->block('text', [
                'title' => $sections['consultants-prestataires']['heading'],
                'content' => $sections['consultants-prestataires']['html'],
                'width' => 'normal',
            ], ['background' => 'light']) : null,
            $this->block('cta', [
                'title' => $this->t('Travaillons ensemble', 'Let\'s work together'),
                'buttons' => [['label' => $this->t('Nous contacter', 'Contact us'), 'url' => $contactLink, 'style' => 'primary']],
            ], ['background' => 'primary']),
        ]));

        // Nos réalisations
        $this->page('realisations', $this->t('Nos réalisations', 'Our achievements'), array_filter([
            $this->block('page_header', [
                'eyebrow' => $this->t('À propos', 'About us'),
                'text' => $this->t(
                    'Des missions de conseil, de formation et de coaching menées auprès d\'organisations publiques et privées.',
                    'Consulting, training and coaching assignments carried out for public and private organizations.'
                ),
            ]),
            isset($sections['nos-realisations']) ? $this->block('text', [
                'eyebrow' => $this->t('Nos missions', 'Our assignments'),
                'content' => $sections['nos-realisations']['html'],
                'width' => 'normal',
            ]) : null,
            $this->block('case_studies', ['limit' => 0]),
            $this->block('references', [
                'eyebrow' => $this->t('Références', 'References'),
                'title' => $this->t('Ils nous font confiance', 'They trust us'),
                'layout' => 'grid',
                'grayscale' => false,
            ], ['background' => 'light']),
        ]));

        // Références
        $this->page('references', $this->t('Nos références', 'Our references'), [
            $this->block('page_header', [
                'eyebrow' => $this->t('À propos', 'About us'),
                'text' => $this->t(
                    'Entreprises, institutions publiques, organisations internationales, écoles : ils nous ont confié leurs enjeux de leadership et de transformation.',
                    'Companies, public institutions, international organizations, schools: they have entrusted us with their leadership and transformation challenges.'
                ),
            ]),
            $this->block('references', ['layout' => 'grid', 'grayscale' => false]),
            $this->block('cta', [
                'title' => $this->t('Rejoignez nos clients', 'Join our clients'),
                'buttons' => [['label' => $this->t('Nous contacter', 'Contact us'), 'url' => $contactLink, 'style' => 'primary']],
            ], ['background' => 'primary']),
        ]);

        // Témoignages (brouillon : aucun témoignage fourni pour l'instant)
        $this->page('temoignages', $this->t('Témoignages', 'Testimonials'), [
            $this->block('page_header', ['eyebrow' => $this->t('À propos', 'About us')]),
            $this->block('testimonials', ['layout' => 'grid']),
        ], status: 'draft');

        // Solutions
        $this->page('solutions', $this->t('Nos solutions', 'Our solutions'), [
            $this->block('page_header', [
                'eyebrow' => $this->t('Solutions', 'Solutions'),
                'text' => $this->t(
                    'Conseil, diagnostic, transformation, développement du leadership et recherche-action : des offres conçues pour produire des résultats durables.',
                    'Consulting, diagnostics, transformation, leadership development and action research: offerings designed to deliver lasting results.'
                ),
            ]),
            $this->block('solutions', ['source' => 'poles']),
            $this->block('solutions', [
                'eyebrow' => 'ELC Advisory',
                'title' => $this->t('Nos domaines de conseil', 'Our consulting areas'),
                'source' => 'categories',
                'category_id' => (string) $this->categories['advisory']->id,
                'columns' => '4',
            ], ['background' => 'light']),
            $this->block('solutions', [
                'eyebrow' => 'ELC Institute',
                'title' => $this->t('Développement & connaissance', 'Development & knowledge'),
                'source' => 'categories',
                'category_id' => (string) $this->categories['institute']->id,
                'columns' => '2',
            ]),
        ]);

        // Publications (brouillon tant qu'aucun article n'est publié)
        $this->page('publications', $this->t('Publications', 'Publications'), [
            $this->block('page_header', [
                'eyebrow' => 'ELC Institute',
                'text' => $this->t('Articles, newsletters et travaux de recherche-action.', 'Articles, newsletters and action research.'),
            ]),
            $this->block('posts', ['limit' => 9, 'paginate' => true]),
            $this->block('newsletter', [], ['background' => 'primary']),
        ], status: 'draft');

        // Carrières
        $this->page('carrieres', $this->t('Carrières', 'Careers'), [
            $this->block('page_header', [
                'eyebrow' => $this->t('Rejoindre ELC', 'Join ELC'),
                'text' => $this->t(
                    'Consultants, formateurs, coachs : rejoignez une équipe engagée pour le développement du leadership en Afrique.',
                    'Consultants, trainers, coaches: join a team committed to leadership development in Africa.'
                ),
            ]),
            $this->block('jobs', [
                'eyebrow' => $this->t('Postes ouverts', 'Open positions'),
                'title' => $this->t('Nos offres d\'emploi', 'Job openings'),
                'spontaneous' => true,
            ]),
        ]);

        // Mentions légales (à compléter par le client)
        $this->page('mentions-legales', $this->t('Mentions légales', 'Legal notice'), [
            $this->block('text', [
                'content' => $this->t(
                    '<p>[À compléter : raison sociale, adresse du siège, NINEA / RCCM, directeur de la publication, hébergeur du site.]</p>',
                    '<p>[To be completed: company name, registered office, NINEA / RCCM, publication director, website host.]</p>'
                ),
            ]),
        ], status: 'draft');

        // Remplace les liens provisoires « page:{slug} » par les vrais identifiants.
        foreach (Page::all() as $page) {
            $json = json_encode($page->blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $json = preg_replace_callback('/page:\{([a-z-]+)\}/', fn ($m) => 'page:'.($this->pages[$m[1]]->id ?? 0), $json);
            $page->blocks = json_decode($json, true);
            $page->save();
        }
    }

    // ───────────────────────── Menus ─────────────────────────

    protected function menus(): void
    {
        $link = fn (string $target, array $label = [], array $children = [], array $description = []) => [
            '_id' => Str::random(8),
            'label' => $label ?: ['fr' => '', 'en' => ''],
            'link' => $target,
            'new_tab' => false,
            'description' => $description ?: ['fr' => '', 'en' => ''],
            'children' => $children,
        ];
        $page = fn (string $slug) => 'page:'.$this->pages[$slug]->id;
        $category = fn (string $key) => 'category:'.$this->categories[$key]->id;
        $categoryChildren = fn (string $pole) => collect($this->c['poles'])->firstWhere('key', $pole)['categories'];

        Menu::query()->create([
            'location' => 'header',
            'name' => Menu::LOCATIONS['header'],
            'items' => [
                $link($page('qui-sommes-nous'), $this->t('À propos', 'About'), [
                    $link($page('qui-sommes-nous'), [], [], $this->t('Notre histoire, notre vision, notre équipe', 'Our story, vision and team')),
                    $link($page('realisations'), [], [], $this->t('Missions de conseil, formation et coaching', 'Consulting, training and coaching assignments')),
                    $link($page('references'), [], [], $this->t('Les organisations qui nous font confiance', 'Organizations that trust us')),
                ]),
                $link($category('advisory'), [], collect($categoryChildren('advisory'))->map(fn ($c) => $link($category($c['key']), [], [], $c['summary']))->all()),
                $link($category('institute'), [], collect($categoryChildren('institute'))->map(fn ($c) => $link($category($c['key']), [], [], $c['summary']))->all()),
                $link($page('carrieres')),
                $link($page('contact')),
            ],
        ]);

        Menu::query()->create([
            'location' => 'footer',
            'name' => Menu::LOCATIONS['footer'],
            'items' => [
                $link('#', $this->t('À propos', 'About'), [
                    $link($page('qui-sommes-nous')),
                    $link($page('realisations')),
                    $link($page('references')),
                    $link($page('carrieres')),
                ]),
                $link($page('solutions'), $this->t('Solutions', 'Solutions'), [
                    $link($category('advisory')),
                    $link($category('institute')),
                    $link($page('solutions'), $this->t('Toutes nos offres', 'All our offerings')),
                ]),
            ],
        ]);

        Menu::query()->create(['location' => 'footer_legal', 'name' => Menu::LOCATIONS['footer_legal'], 'items' => []]);
    }

    // ───────────────────────── Grands titres ─────────────────────────

    /** Majuscule à chaque mot important des grands titres (demande du client) : pages, blocs, offres. */
    protected function headlines(): void
    {
        foreach (Page::query()->get() as $page) {
            $page->title = Headline::translations($page->getTranslations('title'));
            $page->blocks = collect($page->blocks)->map(function (array $block) {
                if (isset($block['data']['title'])) {
                    $block['data']['title'] = Headline::translations($block['data']['title']);
                }
                if ($block['type'] === 'cards') {
                    $block['data']['items'] = collect($block['data']['items'] ?? [])
                        ->map(fn ($item) => [...$item, 'title' => Headline::translations($item['title'] ?? null)])->all();
                }

                return $block;
            })->all();
            $page->save();
        }

        foreach ([SolutionCategory::query()->get(), Solution::query()->get()] as $models) {
            foreach ($models as $model) {
                $model->title = Headline::translations($model->getTranslations('title'));
                $model->save();
            }
        }
    }

    // ───────────────────────── Fil d'actualité ─────────────────────────

    /**
     * Premières informations du fil : les offres signature du cabinet (contenu existant du site).
     * Le client les remplace par ses actualités depuis Réglages → Fil d'actualité.
     */
    protected function news(): void
    {
        Setting::put('news', [
            'enabled' => true,
            'label' => $this->t('Actualités', 'News'),
            'posts' => true,
            'items' => Solution::query()->published()->where('is_featured', true)->ordered()->limit(4)->get()
                ->map(fn (Solution $s) => [
                    '_id' => Str::random(8),
                    'text' => $this->t(
                        'Découvrez notre offre signature : '.$s->getTranslation('title', 'fr'),
                        'Discover our signature offering: '.$s->getTranslation('title', 'en')
                    ),
                    'link' => 'solution:'.$s->id,
                ])->all(),
        ]);
    }

    // ───────────────────────── Outils ─────────────────────────

    protected function page(string $key, array $title, array $blocks, bool $isHome = false, string $status = 'published'): Page
    {
        $page = Page::query()->create([
            'title' => $title,
            'slug' => $isHome ? $this->slugs($this->t('accueil', 'home')) : $this->slugs($title),
            'blocks' => Fields::sanitizeBlocks(array_values($blocks)),
            'seo' => null,
            'status' => $status,
            'is_home' => $isHome,
            'sort' => count($this->pages) + 1,
        ]);

        return $this->pages[$key] = $page;
    }

    protected function block(string $type, array $data, array $settings = []): array
    {
        return ['type' => $type, 'data' => $data, 'settings' => $settings + (Blocks::all()[$type]['settings'] ?? [])];
    }

    protected function t(string $fr, string $en): array
    {
        return ['en' => $en, 'fr' => $fr];
    }

    /** @return array<string, string> */
    protected function slugs(array $titles): array
    {
        return collect($titles)->map(fn ($t) => Str::slug(str_replace(['™', '®'], '', (string) $t)))->all();
    }

    protected function plain(array $html): array
    {
        return collect($html)->map(fn ($h) => trim(html_entity_decode(strip_tags((string) $h))))->all();
    }

    /** Copie un fichier dans la médiathèque et renvoie son chemin. */
    protected function media(string $source, string $alt): string
    {
        $name = Str::slug(pathinfo($source, PATHINFO_FILENAME));
        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        $path = "media/seed/$name.$extension";

        Storage::disk('public')->put($path, file_get_contents($source));
        [$width, $height] = @getimagesize($source) ?: [null, null];

        Media::query()->updateOrCreate(['path' => $path], [
            'disk' => 'public',
            'filename' => basename($source),
            'mime' => mime_content_type($source) ?: 'image/'.$extension,
            'size' => filesize($source),
            'width' => $width,
            'height' => $height,
            'alt' => ['fr' => $alt, 'en' => $alt],
        ]);

        return $path;
    }
}
