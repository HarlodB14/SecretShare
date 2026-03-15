<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SecretShare') — Password Sharing Tool</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-50 min-h-screen flex flex-col text-slate-800">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <header class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-3xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}"
               class="flex items-center gap-2 text-base font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                SecretShare
            </a>
            <span class="text-xs text-slate-400 hidden sm:block">AES-256 encrypted · self-destructing links</span>
        </div>
    </header>

    {{-- ── Validation errors ──────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="max-w-3xl mx-auto w-full px-6 pt-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-sm font-medium text-red-700 mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ── Main content ────────────────────────────────────────────── --}}
    <main class="flex-1 max-w-3xl mx-auto w-full px-6 py-12">
        @yield('content')
    </main>

    {{-- ── Footer ─────────────────────────────────────────────────── --}}
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-400">
        Secrets are encrypted at rest and permanently deleted after viewing. &copy; {{ date('Y') }} SecretShare
    </footer>

    @stack('scripts')
</body>
</html>

