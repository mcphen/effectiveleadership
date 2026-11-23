{{-- Formulaire de candidature (offre précise ou candidature spontanée) --}}
<div id="apply-form" class="border border-slate-200 bg-white p-6 md:p-8" style="border-radius: calc(var(--radius) * 3)">
    @if (session('form_success') === 'apply')
        <div class="flex flex-col items-center py-8 text-center" role="status">
            <span class="flex size-16 items-center justify-center rounded-full bg-brand2/10 text-brand2"><x-site.icon name="check" class="size-8" :stroke="2.5" /></span>
            <p class="mt-6 max-w-md text-lg text-ink">{{ __('site.application_sent') }}</p>
        </div>
    @else
        <form method="POST" action="{{ route('site.apply', ['locale' => app()->getLocale()]) }}" enctype="multipart/form-data" class="grid gap-5 sm:grid-cols-2">
            @csrf
            <div class="absolute left-[-9999px]" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
            @if ($job)
                <input type="hidden" name="job_offer_id" value="{{ $job->id }}">
            @endif

            @foreach ([['name', 'text', true, 'name'], ['email', 'email', true, 'email'], ['phone', 'tel', false, 'tel']] as [$field, $type, $required, $autocomplete])
                <div class="{{ $field === 'phone' ? 'sm:col-span-2' : '' }}">
                    <label for="a_{{ $field }}" class="elc-label">{{ __('site.form.'.$field) }}@if ($required)<span class="text-brand2"> *</span>@endif</label>
                    <input id="a_{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" autocomplete="{{ $autocomplete }}" @if ($required) required @endif class="elc-input @error($field, 'apply') !border-red-500 @enderror">
                    @error($field, 'apply')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach

            <div class="sm:col-span-2">
                <label for="a_cv" class="elc-label">{{ __('site.cv') }}<span class="text-brand2"> *</span></label>
                <input id="a_cv" name="cv" type="file" accept=".pdf,.doc,.docx" required class="elc-input file:mr-4 file:rounded file:border-0 file:bg-brand/10 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-brand">
                @error('cv', 'apply')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="a_message" class="elc-label">{{ __('site.form.message') }}</label>
                <textarea id="a_message" name="message" rows="4" class="elc-input">{{ old('message') }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-primary">{{ __('site.send_application') }} <x-site.icon name="send" /></button>
            </div>
        </form>
    @endif
</div>
