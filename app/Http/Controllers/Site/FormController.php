<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Mail\NewSubmission;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\JobOffer;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function contact(Request $request): RedirectResponse
    {
        if ($this->isSpam($request)) {
            return back()->with('form_success', 'contact');
        }

        $data = $request->validateWithBag('contact', [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
        ], [], $this->attributes());

        $message = ContactMessage::query()->create([
            ...$data,
            'locale' => app()->getLocale(),
            'page_url' => Str::limit((string) $request->headers->get('referer'), 250, ''),
        ]);

        $this->notify('Nouveau message de contact', [
            'Nom' => $message->name, 'E-mail' => $message->email, 'Téléphone' => $message->phone,
            'Organisation' => $message->company, 'Objet' => $message->subject, 'Message' => $message->message,
        ], $message->email, route('admin.inbox.messages'));

        return back()->with('form_success', 'contact')->withFragment('contact-form');
    }

    public function newsletter(Request $request): RedirectResponse
    {
        if ($this->isSpam($request)) {
            return back()->with('form_success', 'newsletter');
        }

        $data = $request->validateWithBag('newsletter', ['email' => ['required', 'email', 'max:180']], [], $this->attributes());

        NewsletterSubscriber::query()->firstOrCreate(['email' => Str::lower($data['email'])], ['locale' => app()->getLocale()]);

        return back()->with('form_success', 'newsletter');
    }

    public function apply(Request $request): RedirectResponse
    {
        if ($this->isSpam($request)) {
            return back()->with('form_success', 'apply');
        }

        $data = $request->validateWithBag('apply', [
            'job_offer_id' => ['nullable', 'integer', 'exists:job_offers,id'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:5000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ], [], $this->attributes());

        // Les CV sont stockés hors du dossier public.
        $path = $request->file('cv')->store('applications', 'local');

        $application = JobApplication::query()->create([
            ...collect($data)->except('cv')->all(),
            'cv_path' => $path,
            'locale' => app()->getLocale(),
        ]);

        $job = $application->job_offer_id ? JobOffer::query()->find($application->job_offer_id) : null;

        $this->notify('Nouvelle candidature', [
            'Poste' => $job?->getTranslation('title', default_locale()) ?? 'Candidature spontanée',
            'Nom' => $application->name, 'E-mail' => $application->email, 'Téléphone' => $application->phone,
            'Message' => $application->message,
        ], $application->email, route('admin.inbox.applications'));

        return back()->with('form_success', 'apply')->withFragment('apply-form');
    }

    /** Champ piège invisible : rempli uniquement par les robots. */
    protected function isSpam(Request $request): bool
    {
        return filled($request->input('website'));
    }

    protected function notify(string $subject, array $lines, string $replyTo, string $adminUrl): void
    {
        $to = setting('contact.notify_email') ?: setting('contact.email');

        if (! $to) {
            return;
        }

        try {
            Mail::to($to)->send(new NewSubmission($subject, $lines, $replyTo, $adminUrl));
        } catch (\Throwable $e) {
            // Le message est enregistré en base même si l'envoi d'e-mail échoue.
            Log::warning('Envoi de la notification impossible : '.$e->getMessage());
        }
    }

    protected function attributes(): array
    {
        return app()->getLocale() === 'fr'
            ? ['name' => 'nom', 'email' => 'e-mail', 'phone' => 'téléphone', 'company' => 'organisation', 'subject' => 'objet', 'message' => 'message', 'cv' => 'CV']
            : ['name' => 'name', 'email' => 'email', 'phone' => 'phone', 'company' => 'organisation', 'subject' => 'subject', 'message' => 'message', 'cv' => 'CV'];
    }
}
