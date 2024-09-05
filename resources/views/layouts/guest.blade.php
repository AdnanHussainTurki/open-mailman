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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div>
            <a href="/">
                <svg class=" drop-shadow-xl w-20 h-20 fill-current text-gray-900" fill="#000000" height="800px"
                    width="800px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 491.518 491.518" xml:space="preserve">
                    <g>
                        <g>
                            <polygon points="167.411,295.164 223.846,316.25 431.429,118.427 		" />
                        </g>
                    </g>
                    <g>
                        <g>
                            <polygon points="183.926,319.81 157.227,309.833 157.227,408.219 212.335,330.427 		" />
                        </g>
                    </g>
                    <g>
                        <g>
                            <polygon points="0,232.612 147.446,287.708 452.804,83.299 		" />
                        </g>
                    </g>
                    <g>
                        <g>
                            <polygon points="232.597,337.995 229.1,336.688 185.062,398.857 257.571,347.333 		" />
                        </g>
                    </g>
                    <g>
                        <g>
                            <polygon points="241.858,322.984 395.735,380.484 491.518,85.062 		" />
                        </g>
                    </g>
                </svg>

            </a>

        </div>
        <a href="/">
            <h4 class="text-2xl">Open Mailman</h4>
        </a>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
