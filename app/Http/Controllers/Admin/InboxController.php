<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Messages de contact, candidatures et abonnés newsletter. */
class InboxController extends Controller
{
    public function messages(Request $request): Response
    {
        $paginator = ContactMessage::query()->latest()
            ->when($request->query('q'), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('message', 'like', "%$s%")))
            ->paginate(30)->withQueryString();

        return Inertia::render('admin/inbox/Messages', [
            'items' => collect($paginator->items())->map(fn (ContactMessage $m) => [
                ...$m->only(['id', 'name', 'email', 'phone', 'company', 'subject', 'message', 'locale', 'page_url']),
                'read' => $m->read_at !== null,
                'date' => $m->created_at->format('d/m/Y H:i'),
            ]),
            'links' => $paginator->linkCollection()->toArray(),
            'query' => ['q' => $request->query('q')],
        ]);
    }

    public function markMessage(ContactMessage $message): RedirectResponse
    {
        $message->update(['read_at' => $message->read_at ? null : now()]);

        return back();
    }

    public function destroyMessage(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return back()->with('success', 'Message supprimé.');
    }

    public function applications(): Response
    {
        $paginator = JobApplication::query()->with('jobOffer')->latest()->paginate(30);

        return Inertia::render('admin/inbox/Applications', [
            'items' => collect($paginator->items())->map(fn (JobApplication $a) => [
                ...$a->only(['id', 'name', 'email', 'phone', 'message', 'locale']),
                'job' => $a->jobOffer?->getTranslation('title', default_locale()) ?? 'Candidature spontanée',
                'has_cv' => (bool) $a->cv_path,
                'read' => $a->read_at !== null,
                'date' => $a->created_at->format('d/m/Y H:i'),
            ]),
            'links' => $paginator->linkCollection()->toArray(),
        ]);
    }

    public function markApplication(JobApplication $application): RedirectResponse
    {
        $application->update(['read_at' => $application->read_at ? null : now()]);

        return back();
    }

    public function downloadCv(JobApplication $application): StreamedResponse
    {
        abort_unless($application->cv_path && Storage::disk('local')->exists($application->cv_path), 404);
        $application->update(['read_at' => $application->read_at ?? now()]);

        return Storage::disk('local')->download($application->cv_path, 'CV-'.str($application->name)->slug().'.'.pathinfo($application->cv_path, PATHINFO_EXTENSION));
    }

    public function destroyApplication(JobApplication $application): RedirectResponse
    {
        if ($application->cv_path) {
            Storage::disk('local')->delete($application->cv_path);
        }
        $application->delete();

        return back()->with('success', 'Candidature supprimée.');
    }

    public function subscribers(): Response
    {
        $paginator = NewsletterSubscriber::query()->latest()->paginate(50);

        return Inertia::render('admin/inbox/Subscribers', [
            'items' => collect($paginator->items())->map(fn ($s) => [...$s->only(['id', 'email', 'name', 'locale']), 'date' => $s->created_at->format('d/m/Y')]),
            'total' => $paginator->total(),
            'links' => $paginator->linkCollection()->toArray(),
        ]);
    }

    public function destroySubscriber(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Abonné supprimé.');
    }

    public function export(string $type): StreamedResponse
    {
        [$query, $columns] = match ($type) {
            'messages' => [ContactMessage::query()->latest(), ['created_at' => 'Date', 'name' => 'Nom', 'email' => 'E-mail', 'phone' => 'Téléphone', 'company' => 'Organisation', 'subject' => 'Objet', 'message' => 'Message']],
            'subscribers' => [NewsletterSubscriber::query()->latest(), ['created_at' => 'Date', 'email' => 'E-mail', 'name' => 'Nom', 'locale' => 'Langue']],
            default => abort(404),
        };

        return response()->streamDownload(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM pour Excel
            fputcsv($out, array_values($columns), ';');
            $query->chunk(500, function ($rows) use ($out, $columns) {
                foreach ($rows as $row) {
                    fputcsv($out, collect(array_keys($columns))->map(fn ($c) => (string) $row->{$c})->all(), ';');
                }
            });
            fclose($out);
        }, "elc-$type-".now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
