@extends('layouts.app')

@section('title', 'Your Secret')

@section('content')
    <div id="secret-page-root" class="max-w-lg mx-auto text-center">

        {{--  Icon  --}}
        <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-indigo-600" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-semibold text-slate-900">Here is your secret</h1>
        <p class="mt-2 text-sm text-slate-500">
            This secret has been permanently deleted from our servers after this single view.
        </p>

        @if ($expiresAt)
            <div id="expiry-banner" class="mt-4 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-left">
                <p class="text-xs font-medium text-amber-800 uppercase tracking-wide">Expiration</p>
                <p class="mt-1 text-sm text-amber-700">
                    This page will automatically redirect when expired.
                </p>
            </div>
        @endif

        {{--  Secret box  --}}
        <div id="secret-card" class="mt-6 bg-white border border-slate-200 rounded-xl p-5 shadow-sm text-left">
            <p class="text-xs font-medium text-slate-500 mb-2 uppercase tracking-wide">Secret value</p>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ $password }}" id="secret-value"
                       class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm
                          text-slate-700 font-mono focus:outline-none">
            </div>
        </div>

        {{--  Deletion notice --}}
        <div class="mt-4 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg p-3 text-left">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mt-0.5 shrink-0 text-red-500" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                <path d="M10 11v6"></path>
                <path d="M14 11v6"></path>
                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
            </svg>
            <p class="text-xs text-red-700">
                This secret has been permanently deleted and cannot be retrieved again. Save it now.
            </p>
        </div>

        <a href="{{ route('home') }}"
           class="mt-6 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Share another secret
        </a>
    </div>


@endsection

@if ($expiresAt)
    @push('scripts')
        <script>
            (function () {
                const expiresAtIso = @json($expiresAt);
                const expiresAtMs = Date.parse(expiresAtIso);

                if (Number.isNaN(expiresAtMs)) {
                    return;
                }

                const redirectIfExpired = () => {
                    if (Date.now() >= expiresAtMs) {
                        window.location.replace(@json(route('secret.expired')));
                    }
                };

                redirectIfExpired();
                window.setInterval(redirectIfExpired, 1000);
            })();
        </script>
    @endpush
@endif

