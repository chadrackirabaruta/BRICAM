<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | BRICAM INDUSTRY LTD</title>
 @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 min-h-screen flex items-center justify-center">
    
    @yield('content')

    <script>
        // Toggle password visibility
        document.addEventListener("DOMContentLoaded", () => {
            const toggleBtn = document.getElementById("togglePassword");
            const passwordField = document.getElementById("password");

            toggleBtn?.addEventListener("click", () => {
                const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
                passwordField.setAttribute("type", type);
                toggleBtn.innerHTML = type === "password" ? "👁️" : "🙈";
            });
        });
    </script>
</body>
</html>
