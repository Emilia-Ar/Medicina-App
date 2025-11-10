<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenido a MedicinasApp</title>

    
<link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased flex items-center justify-center min-h-screen bg-gradient-to-br from-blue-100 to-purple-200 dark:from-gray-900 dark:to-black text-white selection:bg-blue-500 selection:text-white">
    <div class="relative flex flex-col items-center justify-center min-h-screen-centered px-6 py-12 lg:px-8">
        <div class="max-w-xl mx-auto text-center bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 sm:p-12 md:p-16 lg:p-20">
            
            <img src="{{ asset('images/logo-medicina.png') }}" alt="MedicinasApp Logo" class="mx-auto h-40 w-auto mb-6 animate-bounce-slow">

            
<h1 class="text-5xl font-extrabold text-gray-900 dark:text-gray-100 leading-tight mb-6">
                ¡Bienvenido a <span class="text-blue-600 dark:text-blue-400">MedicinasApp</span>!
            </h1>

            
<p class="text-2xl text-gray-700 dark:text-gray-300 mb-10 italic">
                "Tu salud es nuestro recordatorio diario."
            </p>

            
<div class="flex flex-col sm:flex-row justify-center gap-4"> @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-lg font-bold text-xl text-white hover:bg-blue-700 dark:hover:bg-blue-400 transition ease-in-out duration-150">
                            Ir al Panel
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-lg font-bold text-xl text-white hover:bg-blue-700 dark:hover:bg-blue-400 transition ease-in-out duration-150">
                            Iniciar Sesión
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center justify-center px-8 py-4 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-lg font-bold text-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition ease-in-out duration-150">
                                Registrarse
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
            </div>
    </div>

    <style>
        /* Animación para el logo */
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-bounce-slow {
            animation: bounce-slow 3s infinite ease-in-out;
        }

        /* Centrar contenido en pantallas pequeñas */
        @media (max-height: 600px) {
            .min-h-screen-centered {
                min-height: auto;
                padding-top: 3rem;
                padding-bottom: 3rem;
            }
        }
    </style>
</body>
</html>
</html>