<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Guru - Pemilihan Ketua OSIS</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gradient-to-br from-indigo-600 to-purple-600 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Logo/Title -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Login Guru</h1>
                <p class="text-gray-600">Dashboard Pemilihan Ketua OSIS</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('guru.login.post') }}" class="space-y-6">
                @csrf

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required
                        autofocus
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Masukkan username Anda">
                    @error('username')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Masukkan password Anda">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition duration-200">
                    Masuk
                </button>
            </form>

            <!-- Back Link -->
            <div class="text-center mt-6">
                <a href="{{ route('osis.landing') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                    ← Kembali ke Halaman Utama
                </a>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-8 bg-white/10 backdrop-blur-md rounded-lg p-4 text-white text-center">
            <p class="text-sm">Halaman ini hanya untuk guru/admin</p>
        </div>
    </div>
</body>

</html>
