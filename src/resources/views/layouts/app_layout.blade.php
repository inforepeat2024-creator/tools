<!doctype html>
<html lang="sr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Title')</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('head')
</head>
<body class="layout-fluid">
<main>@yield('content')</main>

@stack('scripts')
</body>
</html>
