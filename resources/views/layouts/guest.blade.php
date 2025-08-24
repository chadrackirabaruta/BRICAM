<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite('resources/js/app.js')
</head>
<body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 min-h-screen flex items-center justify-center px-4">

    <!-- Centered Form Card -->
    <div class="w-full max-w-xs"> <!-- Narrower width -->
        <div class="bg-gray-50 dark:bg-gray-900 shadow-xl rounded-2xl p-6 sm:p-8 flex flex-col items-center transform transition-transform duration-500 hover:-translate-y-1 hover:shadow-2xl">
            
       

            <!-- Slot (Login/Register Forms) -->
            {{ $slot }}

            <!-- Footer -->
            <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400 animate__animated animate__fadeInUp">
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Optional: Animate.css for smooth animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

</body>
</html>
