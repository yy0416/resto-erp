<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Configuration des Tables - Resto ERP</title>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 text-gray-100 min-h-screen p-6 font-sans">

    <div class="max-w-6xl mx-auto space-y-6">

        <div class="flex justify-between items-center bg-gray-900 p-5 rounded-2xl border border-gray-800 shadow-xl">
            <div>
                <h1 class="text-xl font-black tracking-wide text-white">🪑 Configuration des Tables (餐桌网格档案配置)</h1>
                <p class="text-xs text-gray-400 mt-1">初始化您的餐厅物理空间，快速排布、生成和增删桌位</p>
            </div>
            <a href="/admin" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-xs font-bold rounded-xl transition border border-gray-700">
                ⬅️ Retour au Dashboard (返回大厅)
            </a>
        </div>

        @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl font-bold text-xs">
            🎉 {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl font-bold text-xs">
            ⚠️ {{ session('error') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="space-y-6 lg:col-span-1">

                <div class="bg-gray-900 p-5 rounded-2xl border border-gray-800 space-y-4 shadow-md">
                    <h3 class="text-sm font-black text-blue-400 uppercase tracking-wider">⚡ Génération en Masse (一键批量生成)</h3>
                    <form action="{{ route('admin.tables.batch') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] uppercase font-black text-gray-400 mb-1">Prefix (字母前缀 / 可不填):</label>
                            <input type="text" name="prefix" placeholder="例如 V 代表包厢" class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs font-bold text-white focus:outline-none focus:border-blue-500 font-mono">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] uppercase font-black text-gray-400 mb-1">Numéro Début (起始号):</label>
                                <input type="number" name="start_num" value="1" required class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs font-bold text-white text-center font-mono focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase font-black text-gray-400 mb-1">Numéro Fin (结束号):</label>
                                <input type="number" name="end_num" value="15" required class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs font-bold text-white text-center font-mono focus:outline-none focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-black text-gray-400 mb-1">Places par table (默认容纳客人数):</label>
                            <input type="number" name="default_seats" value="4" required class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs font-bold text-white font-mono focus:outline-none focus:border-blue-500">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white text-xs font-black py-2.5 rounded-xl transition active:scale-95 shadow-lg shadow-blue-900/20">
                            🚀 执行一键极速生成
                        </button>
                    </form>
                </div>

                <div class="bg-gray-900 p-5 rounded-2xl border border-gray-800 space-y-4 shadow-md">
                    <h3 class="text-sm font-black text-purple-400 uppercase tracking-wider">➕ Ajouter une table (单增一张特殊桌)</h3>
                    <form action="{{ route('admin.tables.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] uppercase font-black text-gray-400 mb-1">Nom / Numéro (唯一桌号名):</label>
                            <input type="text" name="table_number" placeholder="例如: Terrasse-1 或 B01" required class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs font-bold text-white focus:outline-none focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-black text-gray-400 mb-1">Nombre de Places (可坐人数):</label>
                            <input type="number" name="seats_count" value="2" required class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs font-bold text-white font-mono focus:outline-none focus:border-purple-500">
                        </div>
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 text-white text-xs font-black py-2.5 rounded-xl transition active:scale-95 shadow-lg shadow-purple-900/20">
                            添加这张单桌
                        </button>
                    </form>
                </div>

            </div>

            <div class="lg:col-span-2 bg-gray-900 p-6 rounded-2xl border border-gray-800 space-y-4 shadow-xl">
                <div class="flex justify-between items-center border-b border-gray-800 pb-3">
                    <h3 class="text-sm font-black text-gray-200">🗺️ Plan de Salle Actuel (当前餐厅桌位沙盘 - 共 {{ $tables->count() }} 表)</h3>
                    <span class="text-[10px] bg-gray-800 text-gray-400 font-bold px-2 py-0.5 rounded">Mode Aperçu</span>
                </div>

                <div class="space-y-3">
                    @foreach($tables as $t)
                    <form action="{{ route('admin.tables.update', $t->id) }}" method="POST" class="flex flex-wrap items-center justify-between bg-gray-900 border border-gray-850 p-3 rounded-xl gap-3 hover:border-gray-750 transition">
                        @csrf
                        @method('PUT') {{-- 💡 使用 PUT 提交更新 --}}

                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500 font-mono font-bold">桌号:</span>
                            <input type="text"
                                name="table_number"
                                value="{{ $t->table_number }}"
                                class="w-20 bg-gray-950 border border-gray-800 rounded-lg px-2 py-1 text-sm font-mono text-center font-black text-white focus:outline-none focus:border-blue-500">
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500 font-mono font-bold">Capacité (容量):</span>
                            <div class="flex items-center bg-gray-950 border border-gray-800 rounded-lg px-1">
                                <input type="number"
                                    name="seats_count"
                                    value="{{ $t->seats_count }}"
                                    min="1"
                                    class="w-12 bg-transparent border-none py-1 text-sm font-mono text-center font-black text-amber-400 focus:outline-none focus:ring-0">
                                <span class="text-[10px] text-gray-600 font-bold pr-1">人座</span>
                            </div>
                        </div>

                        <div class="hidden sm:block">
                            @php $numL = strtolower($t->table_number); @endphp
                            @if(str_starts_with($numL, 's'))
                            <span class="text-[10px] bg-blue-500/10 text-blue-400 border border-blue-900/40 px-2 py-0.5 rounded-md font-bold">大厅区域</span>
                            @elseif(str_starts_with($numL, 't'))
                            <span class="text-[10px] bg-amber-500/10 text-amber-400 border border-amber-900/40 px-2 py-0.5 rounded-md font-bold">露天区域</span>
                            @else
                            <span class="text-[10px] bg-purple-500/10 text-purple-400 border border-purple-900/40 px-2 py-0.5 rounded-md font-bold">未知分区</span>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs px-3 py-1.5 rounded-lg shadow-md shadow-emerald-950/40 transition flex items-center space-x-1">
                                <span>💾 Sauvegarder (保存)</span>
                            </button>

                            <button type="button"
                                onclick="if(confirm('🚨 确定要彻底拆除这盘桌位基建吗？')) { document.getElementById('delete-form-{{ $t->id }}').submit(); }"
                                class="bg-gray-800 hover:bg-red-950/60 text-gray-500 hover:text-red-400 border border-gray-700/60 hover:border-red-900/40 text-xs px-2 py-1.5 rounded-lg transition font-bold">
                                🗑️
                            </button>
                        </div>
                    </form>

                    <form id="delete-form-{{ $t->id }}" action="{{ route('admin.tables.destroy', $t->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endforeach
                </div>

                @if($tables->isEmpty())
                <div class="text-center py-20 text-gray-500 text-xs">
                    📭 Aucun emplacement configuré. Utilisez le panneau de gauche pour peupler votre salle !<br>
                    (当前没有配置任何桌位。请使用左侧面板快速铺设您的餐馆！)
                </div>
                @endif

            </div>

        </div>

    </div>

</body>

</html>