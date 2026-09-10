<!DOCTYPE html>
<html lang="id" data-community="{{ data_get($page, 'props.community.key', 'public') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ data_get($page, 'props.title', 'Lafagen') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="min-h-screen bg-background font-sans antialiased">
        @inertia
    </body>
</html>
