<!DOCTYPE html>
<html lang="id" data-community="{{ data_get($page, 'props.community.key', 'public') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ data_get($page, 'props.title', 'Lafagen') }}</title>
        @php($ck = data_get($page, 'props.community.key'))
        {{-- Favicon awal per komunitas; setelah itu disinkronkan di
             resources/js/app.js setiap navigasi Inertia (blade tidak di-render
             ulang saat pindah komunitas lewat SPA). --}}
        <link
            id="favicon"
            rel="icon"
            type="image/png"
            href="{{ in_array($ck, ['fad', 'genre'], true) ? "/images/{$ck}.png" : '/favicon.png' }}"
        >
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="min-h-screen bg-background font-sans antialiased">
        @inertia
    </body>
</html>
