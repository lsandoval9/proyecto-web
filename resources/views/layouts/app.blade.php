<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Importante para Axios --}}

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Inicio')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite('resources/css/app.css')

</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800">
    <div id="app" class="min-h-screen flex flex-col"> {{-- Contenedor principal Vue --}}

        {{-- Barra de Navegación --}}
        <nav class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            {{-- Logo o Nombre App --}}
                            <a href="{{ url('/') }}" class="text-xl font-bold text-indigo-600">
                                Gestión Académica
                            </a>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            {{-- Enlaces de Navegación --}}
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('students.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('students.index') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                                Estudiantes
                            </a>
                            <a href="{{ route('subjects.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('subjects.index') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                                Asignaturas
                            </a>
                            <a href="{{ route('enrollments.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('enrollments.index') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                                Matrículas
                            </a>
                            <a href="{{ route('grades.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('grades.index') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium">
                                Calificaciones
                            </a>
                        </div>
                    </div>
                    {{-- Aquí podrías añadir menú de usuario si implementas login --}}
                </div>
            </div>
        </nav>

        {{-- Contenido Principal --}}
        <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content') {{-- Aquí se cargará el contenido específico de cada vista --}}
        </main>

         {{-- Footer Opcional --}}
        <footer class="bg-white mt-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
            © {{ date('Y') }} Universidad de Carabobo - Facultad de Ciencias y Tecnología
        </footer>
    </div>

    <!-- Scripts -->
    @vite('resources/js/app.js')
</body>
</html>