@if (session('form_success') === 'newsletter')
    <p class="flex items-center gap-3 font-semibold {{ ! empty($dark) ? 'text-white' : 'text-brand2' }}" role="status"><x-site.icon name="check" class="size-5" :stroke="2.5" /> {{ __('site.newsletter.thanks') }}</p>
@else
    <form method="POST" action="{{ route('site.newsletter', ['locale' => app()->getLocale()]) }}" class="flex flex-col gap-3 sm:flex-row">
        @csrf
        <div class="absolute left-[-9999px]" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <label class="sr-only" for="nl_email_{{ $uid = \Illuminate\Support\Str::random(4) }}">{{ __('site.newsletter.placeholder') }}</label>
        <input id="nl_email_{{ $uid }}" type="email" name="email" required autocomplete="email" placeholder="{{ __('site.newsletter.placeholder') }}"
               class="elc-input flex-1 {{ ! empty($dark) ? '!border-white/25 !bg-white/10 !text-white placeholder:!text-white/60 focus:!border-white' : '' }}">
        <button type="submit" class="btn {{ ! empty($dark) ? 'bg-accent2 text-white hover:bg-white hover:text-ink' : 'btn-primary' }} shrink-0">{{ __('site.newsletter.button') }}</button>
    </form>
    @error('email', 'newsletter')<p class="mt-2 text-sm {{ ! empty($dark) ? 'text-red-200' : 'text-red-600' }}">{{ $message }}</p>@enderror
@endif
