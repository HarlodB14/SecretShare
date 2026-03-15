@extends('layouts.app')

@section('title', 'Link Created')

@section('content')
    <div class="max-w-lg mx-auto text-center">

        {{--  Icon  --}}
        <div class="inline-flex items-center justify-center w-14 h-14 bg-green-100 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-green-600" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>

        <h1 class="text-2xl font-semibold text-slate-900">Your secure link is ready</h1>
        <p class="mt-2 text-sm text-slate-500">
            Share this link once. The secret will be permanently deleted as soon as it is viewed.
        </p>

        {{--  Link box  --}}
        <div class="mt-6 bg-white border border-slate-200 rounded-xl p-5 shadow-sm text-left">
            <p class="text-xs font-medium text-slate-500 mb-2 uppercase tracking-wide">Shareable link</p>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ $link }}" id="secret-link"
                       class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm
                          text-slate-700 font-mono focus:outline-none select-all">
                <button type="button" onclick="copyLink()"
                        id="copy-btn"
                        class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2
                           text-sm font-medium transition-colors focus:outline-none focus:ring-2
                           focus:ring-indigo-500 focus:ring-offset-2">
                    Copy
                </button>
            </div>
        </div>

        {{--  Warning banner  --}}
        <div class="mt-4 flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg p-3 text-left">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mt-0.5 shrink-0 text-amber-500" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <p class="text-xs text-amber-700">
                Copy the link now — once you leave this page you cannot retrieve it again.
            </p>
        </div>

        <a href="{{ route('home') }}"
           class="mt-6 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Create another secret
        </a>
    </div>
@endsection

@push('scripts')
    <script>
        function copyLink() {
            const input = document.getElementById('secret-link');
            const btn = document.getElementById('copy-btn');
            navigator.clipboard.writeText(input.value).then(() => {
                btn.textContent = 'Copied!';
                btn.classList.replace('bg-indigo-600', 'bg-green-600');
                btn.classList.replace('hover:bg-indigo-700', 'hover:bg-green-700');
                setTimeout(() => {
                    btn.textContent = 'Copy';
                    btn.classList.replace('bg-green-600', 'bg-indigo-600');
                    btn.classList.replace('hover:bg-green-700', 'hover:bg-indigo-700');
                }, 2500);
            });
        }
    </script>
@endpush
