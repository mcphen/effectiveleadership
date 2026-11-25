<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\ClientReference;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Post;
use App\Models\Solution;
use App\Models\Testimonial;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $main = default_locale();
        $other = translation_locale();

        // Contenus dont la traduction du titre manque dans la langue secondaire.
        $missing = collect([
            ['pages', 'Page', Page::query()->get()],
            ['solutions', 'Offre', Solution::query()->get()],
            ['posts', 'Publication', Post::query()->get()],
            ['case-studies', 'Réalisation', CaseStudy::query()->get()],
        ])->flatMap(fn ($set) => $set[2]
            ->filter(fn ($m) => blank($m->getTranslations('title')[$other] ?? null))
            ->map(fn ($m) => ['resource' => $set[0], 'type' => $set[1], 'id' => $m->id, 'title' => $m->getTranslation('title', $main)]))
            ->take(8)->values();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                ['label' => 'Pages', 'value' => Page::query()->count(), 'href' => route('admin.resources.index', 'pages'), 'icon' => 'file-text'],
                ['label' => 'Offres', 'value' => Solution::query()->count(), 'href' => route('admin.resources.index', 'solutions'), 'icon' => 'briefcase'],
                ['label' => 'Publications', 'value' => Post::query()->count(), 'href' => route('admin.resources.index', 'posts'), 'icon' => 'newspaper'],
                ['label' => 'Références', 'value' => ClientReference::query()->count(), 'href' => route('admin.resources.index', 'references'), 'icon' => 'building-2'],
                ['label' => 'Témoignages', 'value' => Testimonial::query()->count(), 'href' => route('admin.resources.index', 'testimonials'), 'icon' => 'message-square-quote'],
                ['label' => 'Abonnés newsletter', 'value' => NewsletterSubscriber::query()->count(), 'href' => route('admin.inbox.subscribers'), 'icon' => 'send'],
            ],
            'messages' => ContactMessage::query()->latest()->limit(5)->get()->map(fn ($m) => [
                'id' => $m->id, 'name' => $m->name, 'subject' => $m->subject, 'excerpt' => str($m->message)->limit(90)->toString(),
                'date' => $m->created_at->diffForHumans(), 'read' => (bool) $m->read_at,
            ]),
            'applications' => JobApplication::query()->whereNull('read_at')->count(),
            'missingTranslations' => $missing,
        ]);
    }
}
