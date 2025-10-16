<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- SEO Meta Tags -->
        <meta name="description" content="JS Bloom Foundation - Empowering the most vulnerable members of our communities through essential resources, education, and care for children, youth, and the elderly.">
        <meta name="keywords" content="JS Bloom Foundation, children empowerment, youth support, elderly care, water infrastructure, menstrual health, community development, Nigeria, NGO">
        <meta name="author" content="JS Bloom Foundation">

        <!-- Favicon and Logo -->
        <link rel="icon" type="image/jpeg" href="/images/jsbloom.jpg">
        <link rel="shortcut icon" type="image/jpeg" href="/images/jsbloom.jpg">
        <link rel="apple-touch-icon" href="/images/jsbloom.jpg">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:title" content="JS Bloom Foundation - Inspire Lasting Change">
        <meta property="og:description" content="Empowering the most vulnerable members of our communities through essential resources, education, and care for children, youth, and the elderly.">
        <meta property="og:image" content="{{ url('/images/jsbloom.jpg') }}">
        <meta property="og:site_name" content="JS Bloom Foundation">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url('/') }}">
        <meta property="twitter:title" content="JS Bloom Foundation - Inspire Lasting Change">
        <meta property="twitter:description" content="Empowering the most vulnerable members of our communities through essential resources, education, and care for children, youth, and the elderly.">
        <meta property="twitter:image" content="{{ url('/images/jsbloom.jpg') }}">

        <!-- Structured Data for Google -->
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "Organization",
            "name": "JS Bloom Foundation",
            "alternateName": "JS Bloom",
            "url": "{{ url('/') }}",
            "logo": "{{ url('/images/jsbloom.jpg') }}",
            "description": "Empowering the most vulnerable members of our communities through essential resources, education, and care for children, youth, and the elderly.",
            "address": {
                "@@type": "PostalAddress",
                "addressLocality": "Lagos",
                "addressCountry": "NG"
            },
            "sameAs": []
        }
        </script>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased overflow-x-hidden">
        @inertia
    </body>
</html>
