<div x-data="caisseManager()" x-init="initCaisse()" class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-gray-800 mb-4 flex items-center justify-between">
                <span>⏳ Tables en Attente (待结桌位)</span>
                <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-bold font-mono" x-text="Object.keys(groupedOrders).length + ' Tables'"></span>
            </h3>

            <div class="space-y-3 max-y-[500px] overflow-y-auto pr-1">
                <template x-for="(data, tableNum) in groupedOrders" :key="tableNum">
                    <button @click="selectTable(tableNum, data)"
                        class="w-full text-left p-4 rounded-xl border-2 transition flex justify-between items-center"
                        :class="selectedTable === tableNum ? 'border-blue-600 bg-blue-50/40 shadow-sm' : 'border-gray-100 bg-gray-50 hover:bg-gray-100/70'">
                        <div>
                            <span class="text-xs font-mono font-bold text-blue-500 bg-blue-100 px-1.5 py-0.5 rounded" x-text="data.orderIds.length + ' Bons (单)'"></span>
                            <h4 class="font-black text-gray-800 text-base mt-1" x-text="tableNum === 'emporter' ? '🛍️ Emporter (外带总账)' : '🌟 Table N° ' + tableNum"></h4>
                        </div>
                        <div class="text-right">
                            <span class="block font-mono font-black text-gray-950 text-base" x-text="data.tableTotalPrice.toFixed(2) + ' €'"></span>
                            <span class="inline-block mt-0.5 text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">À PAYER</span>
                        </div>
                    </button>
                </template>

                <div x-show="Object.keys(groupedOrders).length === 0" class="text-center py-12 text-gray-400 text-sm">
                    🎉 Plus aucune table à encaisser ! (全店已全部结清)
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm flex flex-col justify-between">

            <div x-show="!selectedTable" class="text-center py-24 my-auto text-gray-400">
                <p class="text-4xl mb-2">💳</p>
                <p class="font-bold text-gray-700">Sélectionnez une table pour fusionner l'encaissement</p>
                <p class="text-xs mt-1 text-gray-400">请在左侧选择桌号。系统会自动为你将该桌的所有未付小单全部合二为一！</p>
            </div>

            <div x-show="selectedTable" class="space-y-6" style="display: none;">
                <div class="border-b pb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black text-gray-900" x-text="selectedTable === 'emporter' ? '🧾 Facture Groupée : Emporter' : '🧾 Facture Groupée : Table ' + selectedTable"></h3>
                        <p class="text-xs text-blue-600 font-bold font-mono mt-1 bg-blue-50 px-2 py-1 rounded w-fit" x-text="'Fusion des commandes : #' + groupedOrders[selectedTable]?.orderIds.join(', #')"></p>
                    </div>
                    <button @click="selectedTable = null" class="text-gray-400 hover:text-gray-600 font-bold text-sm">✕ Annuler</button>
                </div>

                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-wide mb-2">1. Récapitulatif Global des Plats (全桌菜品并单总览)</p>
                    <div class="border rounded-xl divide-y overflow-hidden shadow-sm max-h-[260px] overflow-y-auto">
                        <template x-for="(item, index) in editItems" :key="index">
                            <div class="p-3 bg-gray-50/50 flex justify-between items-center text-sm">
                                <span class="font-extrabold text-gray-900 flex-1" x-text="item.name"></span>

                                <div class="flex items-center space-x-3 mr-6">
                                    <button @click="if(item.quantity > 0) { item.quantity--; calculateTotals(); }" class="w-7 h-7 rounded-lg bg-gray-200 hover:bg-gray-300 font-black flex items-center justify-center text-gray-700">-</button>
                                    <span class="w-6 text-center font-mono font-black text-gray-950 text-base" x-text="item.quantity"></span>
                                    <button @click="item.quantity++; calculateTotals();" class="w-7 h-7 rounded-lg bg-gray-200 hover:bg-gray-300 font-black flex items-center justify-center text-gray-700">+</button>
                                </div>

                                <span class="font-mono font-black text-gray-900 w-24 text-right text-sm" x-text="(item.price * item.quantity).toFixed(2) + ' €'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
                    <div>
                        <p class="text-xs font-black text-gray-500 uppercase tracking-wide mb-1.5">2. Réduction sur la table (整桌抹零/优惠)</p>
                        <div class="relative rounded-xl shadow-sm">
                            <input type="number" step="0.01" min="0" x-model="discount" @input="calculateTotals()"
                                class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-300 text-gray-950 font-black font-mono focus:outline-none focus:border-blue-600 text-base shadow-inner">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-bold text-sm">€ 优惠</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-black text-gray-500 uppercase tracking-wide mb-1.5">3. Mode de Paiement (付款渠道)</p>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="method in ['Espèces', 'CB', 'Resto']">
                                <button @click="paymentMethod = method"
                                    class="py-2.5 rounded-xl border-2 text-xs font-black uppercase tracking-wider transition active:scale-95 shadow-sm"
                                    :class="paymentMethod === method ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                                    x-text="method === 'CB' ? '💳 CB' : (method === 'Espèces' ? '💶 Cash' : '🎫 Ticket')">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-950 text-white p-5 rounded-2xl space-y-3 mt-4 shadow-xl">
                    <div class="flex justify-between text-xs opacity-70 font-bold font-mono">
                        <span>Sous-total de la Table (整桌原始小计) :</span>
                        <span x-text="originalTotal.toFixed(2) + ' €'"></span>
                    </div>
                    <div x-show="discount > 0" class="flex justify-between text-xs text-red-400 font-bold font-mono">
                        <span>Remise Table (已减去优惠) :</span>
                        <span x-text="'- ' + parseFloat(discount).toFixed(2) + ' €'"></span>
                    </div>
                    <div class="flex justify-between items-center border-t border-white/10 pt-3">
                        <span class="text-sm font-black">Total Général à Régler (合并最终应付) :</span>
                        <span class="text-2xl font-black font-mono text-green-400" x-text="finalTotal.toFixed(2) + ' €'"></span>
                    </div>

                    <button @click="submitPaiementGrouped()" :disabled="finalTotal <= 0 || !paymentMethod"
                        class="w-full mt-4 bg-green-500 hover:bg-green-600 active:scale-[0.99] disabled:opacity-30 text-gray-950 font-black py-4 rounded-xl transition text-base tracking-wide shadow-lg shadow-green-950/50">
                        💰 FUSIONNER & ENCAISSER LA TABLE (一键合并结账归档)
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
    function caisseManager() {
        return {
            groupedOrders: {}, // 🎯 核心：存放按桌号聚合后的宇宙终极数据结构
            selectedTable: null,
            editItems: [],
            discount: 0,
            paymentMethod: 'CB',
            originalTotal: 0,
            finalTotal: 0,
            loopTimer: null,

            initCaisse() {
                this.fetchUnpaidOrders();
                this.loopTimer = setInterval(() => {
                    this.fetchUnpaidOrders();
                }, 4000);
            },

            fetchUnpaidOrders() {
                fetch('/api/orders?payment_status=unpaid')
                    .then(r => r.json())
                    .then(res => {
                        const rawOrders = res.data || res;
                        if (Array.isArray(rawOrders)) {
                            // 过滤未支付、未取消的真实订单
                            const activeOrders = rawOrders.filter(o => o.payment_status !== 'paid' && o.status !== 'cancelled');
                            // 🛠️ 并单终极魔法：将数组按 table_number 进行高能重组
                            this.runConsolidation(activeOrders);
                        }
                    })
                    .catch(err => console.error(err));
            },

            runConsolidation(orders) {
                const groups = {};
                orders.forEach(order => {
                    // 如果没有桌号，算作外带 'emporter'
                    const key = order.table_number ? order.table_number.toString() : 'emporter';

                    if (!groups[key]) {
                        groups[key] = {
                            orderIds: [],
                            tableTotalPrice: 0,
                            rawItems: []
                        };
                    }

                    groups[key].orderIds.push(order.id);
                    groups[key].tableTotalPrice += parseFloat(order.total_price);
                    // 把这个小订单里的所有菜堆进来
                    if (order.items) {
                        groups[key].rawItems.push(...order.items);
                    }
                });
                this.groupedOrders = groups;
            },

            selectTable(tableNum, data) {
                this.selectedTable = tableNum;
                this.discount = 0;
                this.paymentMethod = 'CB';

                // 🛠️ 菜品去重累加器：如果3个小单里都有“芝士汉堡”，合并后应该把数量加在一起！
                const mergedPlats = {};
                data.rawItems.forEach(item => {
                    const dishId = item.dish_id;
                    const dName = item.dish ? item.dish.name : 'Plat';
                    const dPrice = item.dish ? parseFloat(item.dish.price) : parseFloat(item.price / item.quantity);

                    if (!mergedPlats[dishId]) {
                        mergedPlats[dishId] = {
                            dish_id: dishId,
                            name: dName,
                            price: dPrice,
                            quantity: 0
                        };
                    }
                    mergedPlats[dishId].quantity += item.quantity;
                });

                // 转换成 Alpine 循环需要的干净数组
                this.editItems = Object.values(mergedPlats);
                this.calculateTotals();
            },

            calculateTotals() {
                this.originalTotal = this.editItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                const discValue = parseFloat(this.discount) || 0;
                this.finalTotal = Math.max(0, this.originalTotal - discValue);
            },

            // 🎯 核心大招：一键将这一桌的所有小单同时发送到后端批量结算！
            submitPaiementGrouped() {
                const currentGroup = this.groupedOrders[this.selectedTable];
                if (!currentGroup) return;

                if (!confirm(`💵 Confirmer le règlement de la TABLE [ ${this.selectedTable} ] pour un montant total de ${this.finalTotal.toFixed(2)} € ?`)) return;

                // ⚡ 这里的技巧：我们把这桌合并前的【所有 Order ID】作为一个数组扔给后端
                // 后端只需要对这些 ID 进行批量循环，或者我们在接口里批量处理即可！
                // 为了完全不改动你刚刚辛苦写好的单笔 pay 接口，我们前端采用一个聪明的串行链式调用，
                // 或者直接让后端支持多 ID。这里最稳妥、最不污染你后端代码的做法是：
                // 我们在后端稍微加一个批量循环，让我们用最稳健的方法来实现。

                // 💡 考虑到体验，我们给后端发送一个【批量并单结账指令】
                fetch('/api/orders/pay-multiple', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            order_ids: currentGroup.orderIds, // 👈 这一桌的所有单号
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
                            alert(`🎉 Table [ ${this.selectedTable} ] payée et clôturée avec succès !`);
                            this.selectedTable = null;
                            this.fetchUnpaidOrders();
                        } else {
                            const err = await r.json();
                            alert(err.message || 'Erreur lors du paiement groupé');
                        }
                    })
                    .catch(err => console.error(err));
            }
        }
    }
</script>