<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BRICAM</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
 @vite('resources/js/app.js')

</head>
<body class="antialiased font-sans">
    <div class="bg-gray-50 dark:bg-black text-black dark:text-white min-h-screen flex flex-col items-center justify-center relative">
        <!-- Background image -->
     <!--   <img id="background" class="absolute -left-20 top-0 max-w-[877px] opacity-20" 
             src="https://example.com/brick-background.jpg" alt="Brick Industry Background" /> -->

        <!-- Content -->
        <div class="relative z-10 text-center p-6 max-w-lg">
            <h1 class="text-4xl font-bold mb-4 text-[#FF2D20]">Welcome to BRICAM  Industry</h1>
            <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">
                We specialize in producing high-quality bricks for construction projects of all sizes.
            </p>

            <!-- Buttons -->
            <div class="flex justify-center gap-4">
                <a href="{{ route('login') }}" 
                   class="px-6 py-3 rounded-lg bg-[#FF2D20] text-white font-semibold hover:bg-red-700 transition">
                    Login
                </a>
             
            </div>
        </div>
    </div>
</body>
</html>
