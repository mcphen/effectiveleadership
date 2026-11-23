@php
    $subjects = collect($d['subjects'] ?? [])->map(fn ($s) => tr($s['label'] ?? null))->filter()->values();
    $phones = collect(setting('contact.phones', []))->pluck('number')->filter();
    $email = setting('contact.email');
    $address = tr(setting('contact.address'));
    $map = setting('contact.map_query') ?: strip_tags($address);
    $showInfo = ! empty($d['show_info']);
@endphp
<div class="container-elc" id="contact-form">
    <x-site.heading :eyebrow="$d['eyebrow'] ?? null" :title="$d['title'] ?? null" :intro="$d['intro'] ?? null" />

    <div class="grid gap-12 {{ $showInfo ? 'lg:grid-cols-[1fr_1.5fr]' : 'max-w-3xl' }}">
        @if ($showInfo)
            <aside class="reveal space-y-8">
                @if ($address)
                    <div class="flex gap-4">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-site.icon name="map-pin" /></span>
                        <div><p class="text-sm font-semibold uppercase tracking-wider text-brand2">{{ __('site.contact.address') }}</p><p class="mt-1 text-ink">{!! nl2br(e($address)) !!}</p></div>
                    </div>
                @endif
                @if ($email)
                    <div class="flex gap-4">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-site.icon name="mail" /></span>
                        <div><p class="text-sm font-semibold uppercase tracking-wider text-brand2">{{ __('site.contact.email') }}</p><a href="mailto:{{ $email }}" class="mt-1 block break-all text-ink hover:text-brand">{{ $email }}</a></div>
                    </div>
                @endif
                @if ($phones->isNotEmpty())
                    <div class="flex gap-4">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-site.icon name="phone" /></span>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-brand2">{{ __('site.contact.phone') }}</p>
                            @foreach ($phones as $phone)
                                <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="mt-1 block text-ink hover:text-brand">{{ $phone }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (tr(setting('contact.hours')))
                    <div class="flex gap-4">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"><x-site.icon name="clock" /></span>
                        <div><p class="text-sm font-semibold uppercase tracking-wider text-brand2">{{ __('site.contact.hours') }}</p><p class="mt-1 text-ink">{{ tr(setting('contact.hours')) }}</p></div>
                    </div>
                @endif
                @include('site.partials.socials', ['class' => 'pt-2'])
            </aside>
        @endif

        <div class="reveal border border-slate-200 bg-white p-6 shadow-[0_18px_50px_-30px_rgb(var(--ink)/0.4)] md:p-10" style="border-radius: calc(var(--radius) * 3)">
            @if (session('form_success') === 'contact')
                <div class="flex flex-col items-center py-10 text-center" role="status">
                    <span class="flex size-16 items-center justify-center rounded-full bg-brand2/10 text-brand2"><x-site.icon name="check" class="size-8" :stroke="2.5" /></span>
                    <p class="mt-6 max-w-md text-lg text-ink">{{ __('site.form.sent') }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('site.contact', ['locale' => app()->getLocale()]) }}" class="grid gap-5 sm:grid-cols-2" novalidate>
                    @csrf
                    {{-- Champ piège anti-robots, invisible pour les humains --}}
                    <div class="absolute left-[-9999px]" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                    @foreach ([['name', 'text', true, 'name'], ['email', 'email', true, 'email'], ['phone', 'tel', false, 'tel'], ['company', 'text', false, 'organization']] as [$field, $type, $required, $autocomplete])
                        <div>
                            <label for="c_{{ $field }}" class="elc-label">{{ __('site.form.'.$field) }}@if ($required)<span class="text-brand2"> *</span>@endif</label>
                            <input id="c_{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" autocomplete="{{ $autocomplete }}" @if ($required) required @endif class="elc-input @error($field, 'contact') !border-red-500 @enderror" @error($field, 'contact') aria-invalid="true" aria-describedby="c_{{ $field }}_err" @enderror>
                            @error($field, 'contact')<p id="c_{{ $field }}_err" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach

                    @if ($subjects->isNotEmpty())
                        <div class="sm:col-span-2">
                            <label for="c_subject" class="elc-label">{{ __('site.form.subject') }}</label>
                            <select id="c_subject" name="subject" class="elc-input">
                                <option value="">{{ __('site.form.choose_subject') }}</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject }}" @selected(old('subject') === $subject)>{{ $subject }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label for="c_message" class="elc-label">{{ __('site.form.message') }}<span class="text-brand2"> *</span></label>
                        <textarea id="c_message" name="message" rows="6" required class="elc-input @error('message', 'contact') !border-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message', 'contact')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col gap-4 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-slate-500"><span class="text-brand2">*</span> {{ __('site.form.required') }}. {{ __('site.form.privacy') }}</p>
                        <button type="submit" class="btn btn-primary shrink-0">{{ __('site.form.send') }} <x-site.icon name="send" /></button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @if (! empty($d['show_map']) && $map)
        <div class="reveal mt-14 overflow-hidden border border-slate-200" style="border-radius: calc(var(--radius) * 3)">
            <iframe title="{{ __('site.contact.address') }}" src="https://www.google.com/maps?q={{ urlencode($map) }}&output=embed&hl={{ app()->getLocale() }}" class="h-80 w-full md:h-[26rem]" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    @endif
</div>
