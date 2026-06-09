<div class="space-y-6" x-data="{ 
    showModal: false, 
    selectedTable: null, 
    inputCustomers: 0,
    
    // 打开弹窗，注入当前点击的桌子信息
    openManageModal(table) {
        this.selectedTable = table;
        this.inputCustomers = table.active_customers;
        this.showModal = true;
    },
    
    // 保存就餐人数并提交给 API
    saveCustomers() {
        fetch(`/api/tables/${this.selectedTable.id}/update-customers`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name=&quot;_token&quot;]') ? document.querySelector('input[name=&quot;_token&quot;]').value : ''
            },
            body: JSON.stringify({ active_customers: this.inputCustomers })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                // 成功后强制刷新当前网页，页面上的灯、人数、颜色会瞬间同步！
                window.location.reload();
            } else {
                alert('操作失败！');
            }
        });
    }
}">
    @csrf

    <div class="flex flex-wrap justify-between items-center bg-gray-950 p-5 rounded-2xl border border-gray-800 shadow-xl">
        <div>
            <h2 class="text-base font-black text-white flex items-center space-x-2">
                <span class="animate-pulse text-emerald-500">📡</span>
                <span>Surveillance des Tables (全场桌况实时雷达)</span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">💡 智能提示：点击下方任意桌位方块，可为该桌安排客人、修改实时就餐人数或释放桌台</p>
        </div>

        <div class="flex items-center space-x-5 text-xs font-black mt-3 sm:mt-0 font-mono">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-blue-500/20 border-2 border-blue-500 block"></span>
                <span class="text-blue-400">大厅 (Salle / S前缀)</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-amber-500/20 border-2 border-amber-500 block"></span>
                <span class="text-amber-400">露天 (Terrasse / T前缀)</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-purple-500/20 border-2 border-purple-500 block"></span>
                <span class="text-purple-400">其它 (Zone)</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
        @foreach($tables as $table)
        @php
        // 🎯 核心黑魔法升级：既支持模糊单字判定，也支持 S1 / T01 这种精简首字母判定
        $numLower = strtolower($table->table_number);

        if (str_contains($numLower, 'salle') || str_starts_with($numLower, 's')) {
        // Salle 大厅系列（S1, S2, Salle-A）：高雅幽蓝色调
        $bgStyle = $table->status === 'empty'
        ? "bg-blue-950/30 border-blue-900/60 hover:border-blue-500 text-blue-100"
        : "bg-red-950/40 border-red-900 text-red-100 animate-pulse"; // 用餐中则亮起红灯警告
        $badgeStyle = "bg-blue-500/10 text-blue-400 border-blue-800/50";
        $zoneLabel = "🪑 Salle";
        } elseif (str_contains($numLower, 'terrasse') || str_contains($numLower, 'terace') || str_starts_with($numLower, 't')) {
        // Terrasse 露天系列（T1, T02, Terrasse-B）：温馨落日橙调
        $bgStyle = $table->status === 'empty'
        ? "bg-amber-950/20 border-amber-900/50 hover:border-amber-500 text-amber-100"
        : "bg-red-950/40 border-red-900 text-red-100 animate-pulse";
        $badgeStyle = "bg-amber-500/10 text-amber-400 border-amber-800/50";
        $zoneLabel = "⛱️ Terrasse";
        } else {
        // 默认无前缀或其他：神秘高贵紫
        $bgStyle = $table->status === 'empty'
        ? "bg-purple-950/20 border-purple-900/40 hover:border-purple-500 text-purple-100"
        : "bg-red-950/40 border-red-900 text-red-100 animate-pulse";
        $badgeStyle = "bg-purple-500/10 text-purple-400 border-purple-800/50";
        $zoneLabel = "📦 Zone";
        }
        @endphp

        <div @click="openManageModal({{ json_encode($table) }})"
            class="border-2 rounded-2xl p-6 flex flex-col justify-between items-center relative transition transform active:scale-95 cursor-pointer shadow-lg group min-h-[160px] {{ $bgStyle }}">

            <span class="absolute top-2.5 left-2.5 text-[9px] font-black uppercase px-2 py-0.5 rounded-lg border font-mono tracking-wider {{ $badgeStyle }}">
                {{ $zoneLabel }}
            </span>

            <div class="absolute top-3 right-3 flex items-center space-x-1">
                <span class="w-2.5 h-2.5 rounded-full {{ $table->status === 'empty' ? 'bg-emerald-500 shadow-md shadow-emerald-500/80 animate-pulse' : 'bg-rose-500 shadow-md shadow-rose-500/80' }}"></span>
            </div>

            <div class="text-2xl font-black tracking-widest text-white font-mono mt-6 group-hover:scale-110 transition-transform">
                {{ $table->table_number }}
            </div>

            <div class="mt-4 w-full border-t border-gray-800/60 pt-3 text-center">
                <p class="text-[10px] text-gray-500 font-black uppercase tracking-wider font-mono">
                    Max: {{ $table->seats_count }} 人座
                    @if($table->active_customers > 0)
                    <span class="text-rose-400 ml-1"> (已坐: {{ $table->active_customers }}人)</span>
                    @endif
                </p>

                @if($table->status === 'empty')
                <span class="inline-block text-[11px] text-emerald-400 font-black mt-1 bg-emerald-500/5 px-2 py-0.5 rounded-md border border-emerald-500/10 uppercase tracking-wide">
                    Libre (空闲)
                </span>
                @else
                <span class="inline-block text-[11px] text-rose-400 font-black mt-1 bg-rose-500/5 px-2 py-0.5 rounded-md border border-rose-500/10 uppercase tracking-wide">
                    Occupé (用餐中)
                </span>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    @if($tables->isEmpty())
    <div class="text-center py-24 bg-gray-950 rounded-2xl border border-gray-800 border-dashed text-gray-500 text-sm font-bold">
        📭 餐厅没有配置任何桌位。
    </div>
    @endif

    <div x-show="showModal"
        class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-transition
        style="display: none;">

        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 w-full max-w-sm shadow-2xl space-y-5" @click.away="showModal = false">
            <div class="flex justify-between items-start border-b border-gray-800 pb-3">
                <div>
                    <h3 class="text-lg font-black text-white flex items-center space-x-2">
                        <span>⚙️ Table:</span>
                        <span class="text-blue-400 font-mono text-xl" x-text="selectedTable ? selectedTable.table_number : ''"></span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="'桌位最大容量：' + (selectedTable ? selectedTable.seats_count : 0) + '人'"></p>
                </div>
                <button @click="showModal = false" class="text-gray-500 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-wider block">Nombre de clients (当前实际就餐人数):</label>
                <div class="flex items-center space-x-3">
                    <button @click="if(inputCustomers > 0) inputCustomers--" class="bg-gray-800 hover:bg-gray-700 text-white font-extrabold w-10 h-10 rounded-xl transition text-lg">-</button>
                    <input type="number"
                        x-model="inputCustomers"
                        class="flex-1 bg-gray-950 border border-gray-800 rounded-xl py-2 text-center text-xl font-mono font-black text-white focus:outline-none focus:border-blue-500"
                        min="0">
                    <button @click="inputCustomers++" class="bg-gray-800 hover:bg-gray-700 text-white font-extrabold w-10 h-10 rounded-xl transition text-lg">+</button>
                </div>
                <p class="text-[11px] text-gray-500 italic mt-1">💡 贴心提示：将就餐人数设为 0，系统将自动清空此桌并将其释放为“空闲”状态。</p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button @click="showModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold py-2.5 rounded-xl text-xs transition">
                    Annuler (取消)
                </button>
                <button @click="saveCustomers()" class="bg-blue-600 hover:bg-blue-500 text-white font-black py-2.5 rounded-xl text-xs shadow-lg shadow-blue-900/30 transition">
                    Valider (确认更新)
                </button>
            </div>
        </div>
    </div>

</div>