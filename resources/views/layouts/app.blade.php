<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.app_name')) - {{ __('ui.app_suffix') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme === 'dark' ? 'dark' : 'light';
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
    </script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen flex flex-col text-slate-800 dark:text-slate-100">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="max-w-3xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}"
               class="flex items-center gap-2 text-base font-semibold text-slate-900 dark:text-slate-100 hover:text-indigo-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                {{ __('ui.app_name') }}
            </a>

            <div class="flex items-center gap-3">
                <form action="{{ route('preferences.language') }}" method="POST" class="flex items-center gap-1">
                    @csrf

                    <button type="submit" name="locale" value="nl"
                            class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs transition-colors {{ app()->getLocale() === 'nl' ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300' : 'border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-100' }}">
                        <svg class="h-3.5 w-5 rounded-sm" viewBox="0 0 24 16" aria-hidden="true">
                            <rect width="24" height="16" fill="#21468B"></rect>
                            <rect width="24" height="10.66" y="0" fill="#FFFFFF"></rect>
                            <rect width="24" height="5.33" y="0" fill="#AE1C28"></rect>
                        </svg>
                        <span>{{ __('ui.language_nl') }}</span>
                    </button>

                    <button type="submit" name="locale" value="en"
                            class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs transition-colors {{ app()->getLocale() === 'en' ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300' : 'border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-100' }}">
                        <svg class="h-3.5 w-5 rounded-sm" viewBox="0 0 24 16" aria-hidden="true">
                            <rect width="24" height="16" fill="#012169"></rect>
                            <path d="M0 0L24 16M24 0L0 16" stroke="#FFFFFF" stroke-width="4"></path>
                            <path d="M0 0L24 16M24 0L0 16" stroke="#C8102E" stroke-width="2"></path>
                            <path d="M12 0V16M0 8H24" stroke="#FFFFFF" stroke-width="6"></path>
                            <path d="M12 0V16M0 8H24" stroke="#C8102E" stroke-width="3"></path>
                        </svg>
                        <span>{{ __('ui.language_en') }}</span>
                    </button>
                </form>

                <button type="button" id="theme-toggle" role="switch" aria-checked="false" aria-label="{{ __('ui.theme') }}"
                        class="relative inline-flex h-6 w-11 items-center rounded-full border border-slate-300 dark:border-slate-700 bg-slate-200 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-slate-800">
                    <span class="sr-only">{{ __('ui.theme') }}</span>
                    <span id="theme-toggle-thumb"
                          class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-1 ring-slate-300 transition-transform dark:ring-slate-600"></span>
                </button>
            </div>
        </div>
        <div class="max-w-3xl mx-auto px-6 pb-3 hidden sm:block">
            <span class="text-xs text-slate-400 dark:text-slate-500">{{ __('ui.header_tagline') }}</span>
        </div>
    </header>

    {{-- ── Validation errors ──────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="max-w-3xl mx-auto w-full px-6 pt-6">
            <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg p-4">
                <p class="text-sm font-medium text-red-700 dark:text-red-300 mb-1">{{ __('ui.error_heading') }}</p>
                <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-0.5">
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
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-4 text-center text-xs text-slate-400 dark:text-slate-500">
        {{ __('ui.footer_notice') }} &copy; {{ date('Y') }} {{ __('ui.app_name') }}
    </footer>

    <script>
        (function () {
            const toggleButton = document.getElementById('theme-toggle');
            const thumb = document.getElementById('theme-toggle-thumb');

            if (!toggleButton || !thumb) {
                return;
            }

            const setToggleState = () => {
                const isDark = document.documentElement.classList.contains('dark');

                toggleButton.setAttribute('aria-checked', isDark ? 'true' : 'false');
                toggleButton.classList.toggle('bg-indigo-600', isDark);
                toggleButton.classList.toggle('border-indigo-600', isDark);
                toggleButton.classList.toggle('bg-slate-200', !isDark);
                toggleButton.classList.toggle('border-slate-300', !isDark);
                thumb.classList.toggle('translate-x-5', isDark);
                thumb.classList.toggle('translate-x-0', !isDark);
            };

            setToggleState();

            toggleButton.addEventListener('click', function () {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                setToggleState();
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>

