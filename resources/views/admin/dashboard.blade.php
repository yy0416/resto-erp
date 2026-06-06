<div x-data="superDashboardManager()" x-init="initDashboard()" class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border-2 border-gray-100 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-black text-gray-900">📊 Centre de Données (数据与运营大厅)</h2>
            <p class="text-xs text-gray-400 mt-0.5">实时财务盘点、爆款统计及历史账单穿透查询</p>
        </div>

        <div class="flex items-center space-x-3 bg-gray-50 p-2 rounded-xl border border-gray-200">
            <label class="text-xs font-black text-gray-500 uppercase">Observer la Date (查看日期):</label>
            <input type="date" x-model="selectedDate" @change="refreshAllData()"
                class="bg-white px-3 py-1.5 rounded-lg border border-gray-300 text-gray-900 font-bold font-mono text-sm focus:outline-none focus:border-blue-600">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 p-6 rounded-2xl text-white shadow-lg shadow-green-100">
            <p class="text-xs font-black uppercase tracking-wider opacity-80">Chiffre d'Affaires (营业净收入)</p>
            <h3 class="text-3xl font-black font-mono mt-2" x-text="summary.revenue.toFixed(2) + ' €'">0.00 €</h3>
            <p class="text-[10px] mt-4 font-bold bg-white/20 px-2 py-0.5 rounded w-fit">Encaissements réels</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-wider">Total des Remises (打折让利汇总)</p>
                <h3 class="text-3xl font-black font-mono text-red-600 mt-2" x-text="summary.discount.toFixed(2) + ' €'">0.00 €</h3>
            </div>
            <p class="text-[10px] font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded w-fit mt-4">Remises accordées</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-wider">Factures Clôturées (已封账单数)</p>
                <h3 class="text-3xl font-black font-mono text-gray-900 mt-2" x-text="summary.orders_count + ' Tables'">0</h3>
            </div>
            <p class="text-[10px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded w-fit mt-4">Volume des transactions</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm">
            <h3 class="text-base font-black text-gray-900 mb-4">🔥 Palmarès des Plats (当日爆款畅销榜)</h3>
            <div class="space-y-4">
                <template x-for="(dish, index) in topDishes" :key="index">
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center text-sm font-bold text-gray-800">
                            <div class="flex items-center space-x-2">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-xs font-black"
                                    :class="{'bg-amber-100 text-amber-800': index === 0, 'bg-gray-100 text-gray-800': index === 1, 'bg-orange-100 text-orange-800': index === 2, 'bg-gray-50 text-gray-400': index > 2}" x-text="index + 1"></span>
                                <span x-text="dish.dish_name"></span>
                            </div>
                            <div class="font-mono">
                                <span class="text-blue-600 font-black" x-text="dish.total_quantity + ' 份'"></span>
                                <span class="text-gray-400 text-xs ml-2" x-text="'(' + parseFloat(dish.total_sales).toFixed(2) + ' €)'"></span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-blue-600 h-full transition-all duration-500" :style="'width: ' + (dish.total_quantity / topDishes[0].total_quantity * 100) + '%'"></div>
                        </div>
                    </div>
                </template>
                <p x-show="topDishes.length === 0" class="text-center text-gray-400 text-sm py-8">Aucune vente. (该日期没有任何销售记录)</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-black text-gray-900 mb-4">💳 Modes de Règlement (收银渠道明细)</h3>
                <div class="space-y-3">
                    <template x-for="pay in payments" :key="pay.payment_method">
                        <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <span x-text="pay.payment_method === 'CB' ? '💳' : (pay.payment_method === 'Espèces' ? '💶' : '🎫')"></span>
                                <span class="font-bold text-gray-700" x-text="pay.payment_method === 'CB' ? 'Carte Bancaire' : (pay.payment_method === 'Espèces' ? 'Espèces' : 'Ticket Resto')"></span>
                            </div>
                            <div class="text-right font-mono">
                                <p class="font-black text-gray-900 text-sm" x-text="parseFloat(pay.amount).toFixed(2) + ' €'"></p>
                                <p class="text-[10px] text-gray-400 font-bold" x-text="pay.count + ' 笔交易'"></p>
                            </div>
                        </div>
                    </template>
                    <p x-show="payments.length === 0" class="text-center text-gray-400 text-sm py-8">Aucun encaissement.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm space-y-4">
        <h3 class="text-base font-black text-gray-900 border-b pb-3">📜 Journaux des Ventes Détaillés (该日解剖级账单流水明细)</h3>
        <div class="overflow-hidden rounded-xl border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                            <th class="p-4 w-24">单号/时间</th>
                            <th class="p-4">桌号/类型</th>
                            <th class="p-4">所点菜品明细</th>
                            <th class="p-4 text-right">已扣优惠</th>
                            <th class="p-4 text-right">最终实收</th>
                            <th class="p-4 text-center">支付渠道</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs font-bold">
                        <template x-for="order in historyOrders" :key="order.id">
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="p-4">
                                    <span class="font-mono text-gray-400 block" x-text="'#' + order.id"></span>
                                    <span class="text-[10px] text-gray-400 block mt-0.5 font-mono font-medium" x-text="formatTime(order.started_at)"></span>
                                </td>
                                <td class="p-4 text-gray-900" x-text="order.table_number ? '🌟 Table ' + order.table_number : '🛍️ Emporter'"></td>
                                <td class="p-4">
                                    <div class="space-y-0.5">
                                        <template x-for="item in order.items" :key="item.id">
                                            <div class="text-gray-700">
                                                <span class="text-blue-600 font-mono" x-text="'x' + item.quantity"></span>
                                                <span x-text="item.dish ? item.dish.name : 'Plat'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <td class="p-4 text-right text-red-500 font-mono" x-text="order.discount > 0 ? '-' + parseFloat(order.discount).toFixed(2) + ' €' : '-'"></td>
                                <td class="p-4 text-right font-mono text-sm text-gray-950 font-black" x-text="parseFloat(order.total_price).toFixed(2) + ' €'"></td>
                                <td class="p-4 text-center">
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-black uppercase rounded shadow-sm"
                                        :class="{'bg-blue-100 text-blue-800': order.payment_method === 'CB', 'bg-green-100 text-green-800': order.payment_method === 'Espèces', 'bg-amber-100 text-amber-800': order.payment_method === 'Resto'}"
                                        x-text="order.payment_method"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div x-show="historyOrders.length === 0" class="text-center py-12 text-gray-400 text-sm bg-gray-50/30">
                📭 Aucune transaction pour ce jour. (当前日期无任何封账流水)
            </div>
        </div>
    </div>

</div>

<script>
    function superDashboardManager() {
        return {
            selectedDate: new window.Date().toISOString().split('T')[0], // 默认今天
            summary: {
                revenue: 0,
                discount: 0,
                orders_count: 0
            },
            payments: [],
            topDishes: [],
            historyOrders: [],

            initDashboard() {
                this.refreshAllData();
            },

            // 🧠 高能联动：只要日期一改，顺藤摸瓜把四个接口的数据全部按新日期重捞一遍！
            refreshAllData() {
                // 1. 捞取大牌和排行统计（改写后端让其支持带日期传参，或者我们先请求原本的）
                // 为了完美配合你的 ReportController，我们稍后去后端小修一下，让 index 也能接收日期！
                fetch(`/api/reports/dashboard?date=${this.selectedDate}`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            this.summary = res.summary;
                            this.payments = res.payment_distribution;
                            this.topDishes = res.top_dishes;
                        }
                    });

                // 2. 捞取下半部分的历史流水详情表格
                fetch(`/api/reports/history?date=${this.selectedDate}`)
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            this.historyOrders = res.data;
                        }
                    });
            },

            formatTime(dateTimeStr) {
                if (!dateTimeStr) return '';
                try {
                    return new window.Date(dateTimeStr.replace(' ', 'T')).toLocaleTimeString('fr-FR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    return dateTimeStr;
                }
            }
        }
    }
</script>