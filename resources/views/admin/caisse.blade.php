<div x-data="caisseManager()" x-init="initCaisse()" class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-gray-800 mb-4 flex items-center justify-between">
                <span>⏳ Tables Actives (待结账堂食)</span>
                <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-bold font-mono" x-text="unpaidOrders.length + ' En cours'"></span>
            </h3>

            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                <template x-for="order in unpaidOrders" :key="order.id">
                    <button @click="selectOrder(order)"
                        class="w-full text-left p-4 rounded-xl border-2 transition flex justify-between items-center"
                        :class="selectedOrder && selectedOrder.id === order.id ? 'border-blue-600 bg-blue-50/40 shadow-sm' : 'border-gray-100 bg-gray-50 hover:bg-gray-100/70'">
                        <div>
                            <span class="text-xs font-mono font-bold text-gray-400" x-text="'Order #' + order.id"></span>
                            <h4 class="font-black text-gray-800 text-base" x-text="order.table_number ? '🌟 Table N° ' + order.table_number : '🛍️ Emporter'"></h4>
                        </div>
                        <div class="text-right">
                            <span class="block font-mono font-black text-gray-900 text-sm" x-text="parseFloat(order.total_price).toFixed(2) + ' €'"></span>
                            <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-700 uppercase" x-text="order.status"></span>
                        </div>
                    </button>
                </template>

                <div x-show="unpaidOrders.length === 0" class="text-center py-12 text-gray-400 text-sm">
                    🎉 Tous les clients ont payé ! (暂无未结账单)
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm flex flex-col justify-between">

            <div x-show="!selectedOrder" class="text-center py-24 my-auto text-gray-400">
                <p class="text-3xl mb-2">💳</p>
                <p class="font-bold">Veuillez sélectionner une table à gauche pour procéder au paiement.</p>
                <p class="text-xs mt-1">请在左侧选择需要结账的桌号，进行临场改单、添加优惠和结算。</p>
            </div>

            <div x-show="selectedOrder" class="space-y-6" style="display: none;">
                <div class="border-b pb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black text-gray-900" x-text="selectedOrder?.table_number ? '🧾 Détails Facture : Table ' + selectedOrder.table_number : '🧾 Détails Facture : Emporter'"></h3>
                        <p class="text-xs text-gray-400 font-mono mt-0.5" x-text="'ID Unique de Commande: #' + selectedOrder?.id"></p>
                    </div>
                    <button @click="selectedOrder = null" class="text-gray-400 hover:text-gray-600 font-bold">✕ Fermer</button>
                </div>

                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-wide mb-2">1. Vérification des Plats (临场核对与改单)</p>
                    <div class="border rounded-xl divide-y overflow-hidden shadow-sm">
                        <template x-for="(item, index) in editItems" :key="index">
                            <div class="p-3 bg-gray-50/50 flex justify-between items-center text-sm">
                                <span class="font-bold text-gray-800 flex-1" x-text="item.name"></span>

                                <div class="flex items-center space-x-3 mr-6">
                                    <button @click="if(item.quantity > 0) { item.quantity--; calculateTotals(); }" class="w-7 h-7 rounded-lg bg-gray-200 hover:bg-gray-300 font-black flex items-center justify-center text-gray-600">-</button>
                                    <span class="w-6 text-center font-mono font-black text-gray-900" x-text="item.quantity"></span>
                                    <button @click="item.quantity++; calculateTotals();" class="w-7 h-7 rounded-lg bg-gray-200 hover:bg-gray-300 font-black flex items-center justify-center text-gray-600">+</button>
                                </div>

                                <span class="font-mono font-bold text-gray-600 w-20 text-right" x-text="(item.price * item.quantity).toFixed(2) + ' €'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
                    <div>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-wide mb-1.5">2. Réduction / Remise (优惠折扣)</p>
                        <div class="relative rounded-xl shadow-sm">
                            <input type="number" step="0.01" min="0" x-model="discount" @input="calculateTotals()"
                                class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 text-gray-900 font-bold font-mono focus:outline-none focus:border-blue-600 text-sm" placeholder="0.00">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <span class="text-gray-400 font-bold text-sm">€ En Moins</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-black text-gray-400 uppercase tracking-wide mb-1.5">3. Mode de Paiement (付款方式)</p>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="method in ['Espèces', 'CB', 'Resto']">
                                <button @click="paymentMethod = method"
                                    class="py-2.5 rounded-xl border-2 text-xs font-black uppercase tracking-wider transition active:scale-95 shadow-sm"
                                    :class="paymentMethod === method ? 'bg-blue-600 border-blue-600 text-white shadow-blue-100' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                                    x-text="method === 'CB' ? '💳 CB' : (method === 'Espèces' ? '💶 Cash' : '🎫 Ticket')">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900 text-white p-5 rounded-2xl space-y-3 mt-6 shadow-xl">
                    <div class="flex justify-between text-xs opacity-70 font-semibold font-mono">
                        <span>Sous-total Original (原始小计) :</span>
                        <span x-text="originalTotal.toFixed(2) + ' €'"></span>
                    </div>
                    <div x-show="discount > 0" class="flex justify-between text-xs text-red-400 font-semibold font-mono" style="display: none;">
                        <span>Remise Appliquée (已扣减优惠) :</span>
                        <span x-text="'- ' + parseFloat(discount).toFixed(2) + ' €'"></span>
                    </div>
                    <div class="flex justify-between items-center border-t border-white/10 pt-3">
                        <span class="text-sm font-black">Net à Payer (最终实收金额) :</span>
                        <span class="text-2xl font-black font-mono text-green-400" x-text="finalTotal.toFixed(2) + ' €'"></span>
                    </div>

                    <button @click="submitPaiement()" :disabled="finalTotal <= 0 || !paymentMethod"
                        class="w-full mt-4 bg-green-500 hover:bg-green-600 active:scale-[0.99] disabled:opacity-40 text-gray-950 font-black py-3.5 rounded-xl transition text-base tracking-wide shadow-md shadow-green-900/20">
                        💲 Confirmer l'Encaissement & Clôturer (确认结账封账)
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
    function caisseManager() {
        return {
            unpaidOrders: [],
            selectedOrder: null,
            editItems: [],
            discount: 0,
            paymentMethod: 'CB', // 默认刷卡
            originalTotal: 0,
            finalTotal: 0,
            loopTimer: null,

            initCaisse() {
                this.fetchUnpaidOrders();
                // 每 5 秒自动捞一次最新堂食，看有没有新加桌子
                this.loopTimer = setInterval(() => {
                    this.fetchUnpaidOrders();
                }, 5000);
            },

            fetchUnpaidOrders() {
                // 💡 知识点：这里呼叫后端，并带上我们要查的未付账状态暗号
                fetch('/api/orders?payment_status=unpaid')
                    .then(r => r.json())
                    .then(res => {
                        const data = res.data || res;
                        // 过滤出所有还没在后台标记为彻底完结支付的记录
                        if (Array.isArray(data)) {
                            // 如果你后端还没写 payment_status 字段，我们暂时先拿 status !== 'delivered' 顶替做假数据，
                            // 但建议按标准的接口来，我们先假设后端能返回全部活动订单：
                            this.unpaidOrders = data.filter(o => o.payment_status !== 'paid' && o.status !== 'cancelled');
                        }
                    })
                    .catch(err => console.error(err));
            },

            selectOrder(order) {
                this.selectedOrder = order;
                this.discount = 0;
                this.paymentMethod = 'CB';

                // 把订单里的菜品深拷贝一份出来到编辑区，供收银员临场增删改加减
                this.editItems = order.items.map(item => ({
                    order_item_id: item.id,
                    dish_id: item.dish_id,
                    name: item.dish ? item.dish.name : 'Plat',
                    price: item.dish ? parseFloat(item.dish.price) : parseFloat(item.price / item.quantity),
                    quantity: item.quantity
                }));

                this.calculateTotals();
            },

            calculateTotals() {
                // 计算修改完数量后的原始总价
                this.originalTotal = this.editItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                // 计算扣除优惠后的实收金额
                const discValue = parseFloat(this.discount) || 0;
                this.finalTotal = Math.max(0, this.originalTotal - discValue);
            },

            submitPaiement() {
                if (!confirm(`Confirmer le règlement de ${this.finalTotal.toFixed(2)} € via [${this.paymentMethod}] ?`)) return;

                // 🎯 核心逻辑：把修改后的数量、优惠、以及付款渠道一并打包砸向后端
                fetch(`/api/orders/${this.selectedOrder.id}/pay`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            payment_method: this.paymentMethod,
                            discount: parseFloat(this.discount) || 0,
                            total_price: this.finalTotal,
                            items: this.editItems.map(i => ({
                                dish_id: i.dish_id,
                                quantity: i.quantity
                            }))
                        })
                    })
                    .then(async r => {
                        if (r.ok) {
                            alert('💰 Encaissement réussi ! Table clôturée. (结账成功，桌位已清空解锁！)');
                            this.selectedOrder = null;
                            this.fetchUnpaidOrders(); // 刷新左侧待付列表
                        } else {
                            alert('Erreur lors du paiement');
                        }
                    })
                    .catch(err => console.error(err));
            }
        }
    }
</script>