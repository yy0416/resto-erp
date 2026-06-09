<div class="space-y-6" x-data="{ 
    showModal: false, 
    selectedTable: null, 
    inputCustomers: 0,
    
    // 📡 【核心新增】：本地内存中的桌位实时雷达状态数组
    liveTables: @js($tables->toArray()),
    loopTimer: null,

    // 🚀 初始化雷达
    initHallRadar() {
        // 每 4 秒自动执行一次静默数据拉取，不再强制刷新网页！
        this.loopTimer = setInterval(() => {
            this.fetchLiveStatus();
        }, 4000);
    },

    // 📥 静默拉取最新桌况
    fetchLiveStatus() {
        fetch('/api/tables')
            .then(r => r.json())
            .then(res => {
                const fetchedData = res.data || res;
                if (Array.isArray(fetchedData)) {
                    // 把最新的状态和就餐人数默默灌入 Alpine 内存，界面上的红绿灯和人数会秒变，但网页绝不重置！
                    this.liveTables = fetchedData;
                }
            })
            .catch(err => console.error('Radar Sync Error:', err));
    },
    
    // 打开弹窗，从 liveTables 内存中获取最新的状态
    openManageModal(tableId) {
        // 根据 ID 去实时内存里找这桌，防止打开的是旧数据
        const currentTable = this.liveTables.find(t => t.id === tableId);
        if (!currentTable) return;

        this.selectedTable = currentTable;
        this.inputCustomers = currentTable.active_customers;
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
                // 🎯 【完美改动】：关闭弹窗
                this.showModal = false;
                
                // 🎯 【完美改动】：立刻手动让雷达扫描一次最新状态，桌子秒变色，绝不刷新网页！
                this.fetchLiveStatus();
            } else {
                alert('操作失败！');
            }
        });
    }
}" x-init="initHallRadar()">
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
        <template x-for="table in liveTables" :key="table.id">
            <div @click="openManageModal(table.id)"
                class="border-2 rounded-2xl p-6 flex flex-col justify-between items-center relative transition transform active:scale-95 cursor-pointer shadow-lg group min-h-[160px]"
                :class="{
                    // 🪑 Salle 大厅前缀判断
                    'bg-blue-950/30 border-blue-900/60 hover:border-blue-500 text-blue-100': (table.table_number.toLowerCase().includes('salle') || table.table_number.toLowerCase().startsWith('s')) && table.status === 'empty',
                    'bg-red-950/40 border-red-900 text-red-100 animate-pulse': table.status === 'occupied',
                    
                    // ⛱️ Terrasse 露天前缀判断
                    'bg-amber-950/20 border-amber-900/50 hover:border-amber-500 text-amber-100': (table.table_number.toLowerCase().includes('terrasse') || table.table_number.toLowerCase().startsWith('t')) && table.status === 'empty',
                    
                    // 📦 默认Zone前缀判断
                    'bg-purple-950/20 border-purple-900/40 hover:border-purple-500 text-purple-100': !table.table_number.toLowerCase().startsWith('s') && !table.table_number.toLowerCase().includes('salle') && !table.table_number.toLowerCase().startsWith('t') && !table.table_number.toLowerCase().includes('terrasse') && table.status === 'empty'
                }">

                <span class="absolute top-2.5 left-2.5 text-[9px] font-black uppercase px-2 py-0.5 rounded-lg border font-mono tracking-wider"
                    :class="{
                        'bg-blue-500/10 text-blue-400 border-blue-800/50': table.table_number.toLowerCase().includes('salle') || table.table_number.toLowerCase().startsWith('s'),
                        'bg-amber-500/10 text-amber-400 border-amber-800/50': table.table_number.toLowerCase().includes('terrasse') || table.table_number.toLowerCase().startsWith('t'),
                        'bg-purple-500/10 text-purple-400 border-purple-800/50': !table.table_number.toLowerCase().startsWith('s') && !table.table_number.toLowerCase().includes('salle') && !table.table_number.toLowerCase().startsWith('t') && !table.table_number.toLowerCase().includes('terrasse')
                    }"
                    x-text="(table.table_number.toLowerCase().includes('salle') || table.table_number.toLowerCase().startsWith('s')) ? '🪑 Salle' : ((table.table_number.toLowerCase().includes('terrasse') || table.table_number.toLowerCase().startsWith('t')) ? '⛱️ Terrasse' : '📦 Zone')">
                </span>

                <div class="absolute top-3 right-3 flex items-center space-x-1">
                    <span class="w-2.5 h-2.5 rounded-full"
                        :class="table.status === 'empty' ? 'bg-emerald-500 shadow-md shadow-emerald-500/80 animate-pulse' : 'bg-rose-500 shadow-md shadow-rose-500/80'"></span>
                </div>

                <div class="text-2xl font-black tracking-widest text-white font-mono mt-6 group-hover:scale-110 transition-transform" x-text="table.table_number"></div>

                <div class="mt-4 w-full border-t border-gray-800/60 pt-3 text-center">
                    <p class="text-[10px] text-gray-500 font-black uppercase tracking-wider font-mono">
                        <span x-text="'Max: ' + table.seats_count + ' 人座'"></span>
                        <span x-show="table.active_customers > 0" class="text-rose-400 ml-1" x-text="' (已坐: ' + table.active_customers + '人)'"></span>
                    </p>

                    <span class="inline-block text-[11px] font-black mt-1 px-2 py-0.5 rounded-md border uppercase tracking-wide"
                        :class="table.status === 'empty' ? 'text-emerald-400 bg-emerald-500/5 border-emerald-500/10' : 'text-rose-400 bg-rose-500/5 border-rose-500/10'"
                        x-text="table.status === 'empty' ? 'Libre (空闲)' : 'Occupé (用餐中)'">
                    </span>
                </div>

            </div>
        </template>
    </div>

    <div x-show="liveTables.length === 0" class="text-center py-24 bg-gray-950 rounded-2xl border border-gray-800 border-dashed text-gray-500 text-sm font-bold">
        📭 餐厅没有配置任何桌位。
    </div>

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