<div x-data="ordersManager()" x-init="initOrders()" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex justify-between items-center shadow-sm">
            <div>
                <p class="text-xs font-bold text-amber-700 uppercase tracking-wider">En Attente (新订单)</p>
                <h3 class="text-2xl font-black text-amber-900" x-text="countByStatus('pending')">0</h3>
            </div>
            <span class="text-2xl">⏳</span>
        </div>
        <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl flex justify-between items-center shadow-sm">
            <div>
                <p class="text-xs font-bold text-orange-700 uppercase tracking-wider">En Cuisine (制作中)</p>
                <h3 class="text-2xl font-black text-orange-900" x-text="countByStatus('preparing')">0</h3>
            </div>
            <span class="text-2xl animate-pulse">🔥</span>
        </div>
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl flex justify-between items-center shadow-sm">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Actifs (动态总数)</p>
                <h3 class="text-2xl font-black text-gray-800" x-text="orders.length">0</h3>
            </div>
            <span class="text-2xl">📋</span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm">
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <h2 class="text-xl font-black text-gray-800">📋 Flux de Commandes en Temps Réel (实时订单大厅)</h2>
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span>
                <span class="text-xs text-gray-500 font-bold font-mono">Synchronisation Auto</span>
            </div>
        </div>

        <div x-show="orders.length === 0" class="text-center py-12">
            <p class="text-gray-400 text-lg font-medium">🎉 Calme plat en cuisine ! Aucune commande en cours.</p>
            <p class="text-gray-400 text-xs mt-1">当有桌号提交订单时，这里会自动弹窗并刷新。</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <template x-for="order in orders" :key="order.id">
                <div class="border-2 rounded-2xl overflow-hidden transition-all duration-200"
                    :class="{
                        'border-amber-200 shadow-md shadow-amber-50': order.status === 'pending',
                        'border-orange-200 shadow-md shadow-orange-50': order.status === 'preparing'
                    }">

                    <div class="p-4 flex justify-between items-center text-white"
                        :class="order.status === 'pending' ? 'bg-amber-500' : 'bg-orange-500'">
                        <div>
                            <span class="text-xs font-mono font-bold opacity-90" x-text="'#' + order.id"></span>
                            <h3 class="text-lg font-black" x-text="order.table_number ? '🌟 Table N° ' + order.table_number : '🛍️ Emporter (外带)'"></h3>
                        </div>
                        <span class="text-xs font-bold bg-white/20 px-2.5 py-1 rounded-full uppercase tracking-wider font-mono" x-text="order.status"></span>
                    </div>

                    <div class="p-4 bg-white space-y-3 min-h-[120px]">
                        <div class="border-b pb-2 space-y-1">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="flex justify-between items-center text-sm">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-black text-blue-600 font-mono" x-text="'x' + item.quantity"></span>
                                        <span class="font-bold text-gray-800" x-text="item.dish ? item.dish.name : 'Plat inconnu'"></span>
                                    </div>
                                    <span class="text-xs font-mono font-semibold text-gray-400" x-text="(item.price).toFixed(2) + ' €'"></span>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-between items-center text-xs text-gray-400 font-medium">
                            <span x-text="'Client: ' + (order.customer ? order.customer.name : 'Anonyme')"></span>
                            <span font-mono x-text="formatTime(order.started_at)"></span>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 border-t flex justify-between items-center">
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide">Total à payer</p>
                            <p class="text-lg font-black text-gray-800 font-mono" x-text="parseFloat(order.total_price).toFixed(2) + ' €'"></p>
                        </div>

                        <div>
                            <button x-show="order.status === 'pending'"
                                @click="updateStatus(order.id, 'preparing')"
                                class="bg-amber-500 hover:bg-amber-600 active:scale-95 text-white px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-sm">
                                Accepter & Cuisiner 👨‍🍳
                            </button>

                            <button x-show="order.status === 'preparing'"
                                @click="updateStatus(order.id, 'delivered')"
                                class="bg-green-500 hover:bg-green-600 active:scale-95 text-white px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-sm">
                                Prêt & Servir 🚚
                            </button>
                        </div>
                    </div>

                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function ordersManager() {
        return {
            orders: [],
            timer: null,

            initOrders() {
                this.fetchActiveOrders();
                // 🎯 开启每 4 秒自动化同步轮询（实现真实多端同步）
                this.timer = setInterval(() => {
                    this.fetchActiveOrders();
                }, 4000);
            },

            fetchActiveOrders() {
                fetch('/api/orders') // 👈 对应你控制器里没有 table_number 时的厨房逻辑
                    .then(r => r.json())
                    .then(res => {
                        this.orders = res.data || res;
                    })
                    .catch(err => console.error('Erreur flux orders:', err));
            },

            countByStatus(status) {
                return this.orders.filter(o => o.status === status).length;
            },

            formatTime(dateTimeStr) {
                if (!dateTimeStr) return '';
                try {
                    const date = new Date(dateTimeStr);
                    return date.toLocaleTimeString('fr-FR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    return dateTimeStr;
                }
            },

            updateStatus(orderId, nextStatus) {
                fetch(`/api/orders/${orderId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            status: nextStatus
                        })
                    })
                    .then(async r => {
                        if (!r.ok) {
                            const err = await r.json();
                            alert(err.message || 'Changement de statut impossible');
                        } else {
                            this.fetchActiveOrders(); // 刷新大厅
                        }
                    })
                    .catch(err => console.error(err));
            }
        }
    }
</script>