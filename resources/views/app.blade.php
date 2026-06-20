<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kicap Event</title>
    <meta name="theme-color" content="#0f766e">
    <link rel="icon" href="/icons/kicap-lpj.svg" type="image/svg+xml">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body>
    <div id="kicap-lpj-root"></div>
</body>
</html>
