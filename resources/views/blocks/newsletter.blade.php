<div class="container-elc">
    <div class="reveal flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
        <div class="max-w-xl">
            <h2 class="font-heading text-3xl font-bold">{{ tr($d['title'] ?? null) ?: __('site.newsletter.title') }}</h2>
            <p class="elc-lead mt-3">{{ tr($d['text'] ?? null) ?: __('site.newsletter.text') }}</p>
        </div>
        <div class="w-full max-w-lg">
            @include('site.partials.newsletter-form', ['dark' => $dark])
        </div>
    </div>
</div>
