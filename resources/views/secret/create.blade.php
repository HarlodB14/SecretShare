@extends('layouts.app')

@section('title', 'Share a Secret')

@section('content')
    <div class="max-w-lg mx-auto">

        {{--  Hero  --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-full mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-indigo-600" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900">Share a password securely</h1>
            <p class="mt-2 text-sm text-slate-500">Generate a one-time link. The secret is encrypted and self-destructs
                after viewing.</p>
        </div>

        {{--  Form  --}}
        <form action="{{ route('secret.store') }}" method="POST"
              class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
            @csrf

            {{-- Password / Secret --}}
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                    Secret / Password <span class="text-red-500">*</span>
                </label>
                <textarea id="password" name="password" rows="4" required
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900
                             placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500
                             focus:border-transparent resize-none @error('password') border-red-400 @enderror"
                          placeholder="Enter the password or secret you want to share…">{{ old('password') }}</textarea>
            </div>

            {{-- Optional fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Max views --}}
                <div>
                    <label for="max_views" class="block text-sm font-medium text-slate-700 mb-1">
                        Max views
                        <span class="text-slate-400 font-normal">(optional)</span>
                    </label>
                    <input type="number" id="max_views" name="max_views"
                           min="1" max="100" value="{{ old('max_views') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                              @error('max_views') border-red-400 @enderror"
                           placeholder="e.g. 3">
                    <p class="mt-1 text-xs text-slate-400">Default: deleted after 1 view</p>
                </div>

                {{-- Expiry --}}
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-slate-700 mb-1">
                        Expires at
                        <span class="text-slate-400 font-normal">(optional)</span>
                    </label>
                    <input type="datetime-local" id="expires_at" name="expires_at"
                           value="{{ old('expires_at') }}"
                           min="{{ now()->format('Y-m-d\TH:i') }}"
                           data-min-offset-minutes="1"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                              @error('expires_at') border-red-400 @enderror">
                    <p class="mt-1 text-xs text-slate-400">Leave blank for no expiry</p>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-medium
                       rounded-lg px-4 py-2.5 text-sm transition-colors focus:outline-none
                       focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Generate secure link
            </button>
        </form>

        {{-- Info banner --}}
        <div class="mt-5 flex gap-3 bg-blue-50 border border-blue-100 rounded-lg p-4 text-sm text-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 shrink-0 text-blue-500" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>
                <p class="font-medium">Your data is protected</p>
                <p class="mt-0.5 text-blue-600 text-xs">
                    Secrets are AES-256 encrypted before storage. The plaintext is never saved to disk
                    and is permanently deleted the moment the link is opened.
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const input = document.getElementById('expires_at');

            if (!input) {
                return;
            }

            // Use client time to avoid drift between browser and server clocks.
            const offsetMinutes = Number(input.dataset.minOffsetMinutes || '1');
            const minDate = new Date(Date.now() + (offsetMinutes * 60 * 1000));
            minDate.setSeconds(0, 0);

            const yyyy = minDate.getFullYear();
            const mm = String(minDate.getMonth() + 1).padStart(2, '0');
            const dd = String(minDate.getDate()).padStart(2, '0');
            const hh = String(minDate.getHours()).padStart(2, '0');
            const min = String(minDate.getMinutes()).padStart(2, '0');

            input.min = `${yyyy}-${mm}-${dd}T${hh}:${min}`;
        })();
    </script>
@endpush

