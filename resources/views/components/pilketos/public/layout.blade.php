<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'PILKETOS SMP Al Musyaffa' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main>{{ $slot }}</main>
    <footer class="border-t border-slate-200 bg-white"><div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8"><p class="font-semibold text-slate-700">SMP Al Musyaffa · Portal PILKETOS</p><p>&copy; {{ now()->year }} Pemilihan Ketua OSIS</p></div></footer>
</body>
</html>
