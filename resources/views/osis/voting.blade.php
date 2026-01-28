<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting - Pemilihan Ketua OSIS</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-indigo-600">OSIS Voting</div>
            <a href="{{ route('osis.landing') }}" class="text-gray-600 hover:text-indigo-600 transition">
                ← Kembali ke Landing Page
            </a>
        </div>
    </nav>

    <!-- Voting Component -->
    <livewire:voting-interface />

    @vite('resources/js/app.js')
</body>

</html>
