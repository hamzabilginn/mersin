<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Saha Görev Yönetim ve Otomasyon Sistemi</title>
    
    <!-- Vite Assets Loader -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100">

    <!-- Vue App Mount Element -->
    <div id="app"></div>

</body>
</html>
