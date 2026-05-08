<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>@yield('title')</title>
    <link rel="icon" href="@settings('favicon', '/assets/core/img/logo.png')">

    {{-- meta tags --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Meta Description Tag: Affects click-through rates from search results -->
    <meta name="description" content="Manage your orders with an easy-to-use Dashboard">
    <meta name="theme-color" content="#4f46e5">
    <meta name="keywords" content="">

    <!-- Meta Robots Tag: Controls search engine crawling and indexing -->
    <meta name="robots" content="@settings('seo::robots', 'index, follow')">

    <!-- Open Graph Tags: Enhances visibility and engagement on social media platforms -->
    <meta property="og:title" content="{{ trim($__env->yieldContent('title')) }} - @settings('seo::title', 'WemX')">
    <meta property="og:description" content="Manage your orders with an easy-to-use Dashboard">
    <meta property="og:image" content="@settings('seo::image', '/static/wemx.png')">

    <!-- Custom CSS -->
    @vite(['resources/client_area/default/assets/css/app.css','resources/client_area/default/assets/js/app.js'])

    @yield('header')

    <!-- Dark Mode -->
    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        setTheme();

        document.addEventListener('livewire:navigated', (event) => {
            // On page load or when changing themes, best to add inline in `head` to avoid FOUC
            setTheme();
        });

        function toggleDarkmode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            }
        }

        function setTheme()
        {
            // On page load or when changing themes, best to add inline in `head` to avoid FOUC
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark')
            }
        }
    </script>
</head>

<body class="dark:bg-gray-900 bg-white">
    <section class="bg-gray-50 dark:bg-gray-900">
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <a href="/" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
                <img class="w-10 h-10 mr-2 rounded" src="{{ settings('app_logo', '/assets/common/img/app-logo.png') }}" alt="logo">
                {{ settings('app_name', 'My Application') }}
            </a>
            <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-lg xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    @yield('content')
                </div>
            </div>
        </div>
    </section>
</body>
</html>
