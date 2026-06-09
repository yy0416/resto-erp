<!doctype html>
<html lang="zh">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resto ERP - 安全登录</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-gray-950 border border-gray-800 p-8 rounded-2xl shadow-2xl">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-white tracking-wider">⚙️ Resto ERP</h1>
            <p class="text-xs text-gray-500 mt-2 uppercase tracking-widest font-bold">Système d'authentification</p>
        </div>

        @if ($errors->any())
        <div class="mb-6 p-4 bg-red-900/30 border border-red-800 text-red-400 rounded-xl text-xs font-bold space-y-1">
            @foreach ($errors->all() as $error)
            <p>⚠️ {{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form action="{{ url('/login') }}" method="POST" class="space-y-5">
            @csrf <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Identifiant (邮箱账号)</label>
                <input type="email" name="email" required value="{{ old('email') }}"
                    placeholder="admin@test.com"
                    class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-blue-600 transition font-mono">
            </div>

            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Mot de passe (密码)</label>
                <input type="password" name="password" required
                    placeholder="••••••••"
                    class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-blue-600 transition font-mono">
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white py-3.5 rounded-xl font-black text-sm transition tracking-wider uppercase shadow-lg shadow-blue-900/30">
                    Se connecter (安全登录) 🚀
                </button>
            </div>
        </form>

    </div>

</body>

</html>