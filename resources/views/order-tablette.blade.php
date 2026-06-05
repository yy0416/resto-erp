<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande Tablette - Resto ERP</title>
    @vite('resources/css/app.css')
    <script defer src="https://unpkg.com/alpinejs"></script>
</head>

<body class="bg-gray-100 p-4 font-sans pb-32">
    <div x-data="orderApp()" x-init="initTable()" class="max-w-4xl mx-auto bg-white p-6 rounded-2xl shadow-lg">

        <div class="mb-6 p-4 bg-blue-600 text-white rounded-xl flex justify-between items-center shadow-sm">
            <div>
                <p class="text-xs opacity-80 uppercase tracking-wider font-semibold">Table Coordonnées</p>
                <h1 class="text-2xl font-black">🌟 Table N° <span x-text="table_number || 'N/A'"></span></h1>
            </div>
            <div class="text-right">
                <span class="bg-blue-500 text-xs px-3 py-1 rounded-full border border-blue-400 font-bold">Mode Tablette</span>
            </div>
        </div>

        <div x-show="currentOrderStatus" class="mb-6 p-4 rounded-xl border transition-all duration-300"
            :class="{
                'bg-amber-50 border-amber-200 text-amber-800': currentOrderStatus === 'pending',
                'bg-orange-50 border-orange-200 text-orange-800 animate-pulse': currentOrderStatus === 'preparing',
                'bg-green-50 border-green-200 text-green-800': currentOrderStatus === 'delivered'
            }">
            <div class="flex items-center space-x-3">
                <span class="text-2xl" x-text="currentOrderStatus === 'pending' ? '⏳' : (currentOrderStatus === 'preparing' ? '🔥' : '🎉')"></span>
                <div>
                    <h4 class="font-black text-sm" x-text="currentOrderStatus === 'pending' ? 'Commande reçue !' : (currentOrderStatus === 'preparing' ? 'En préparation !' : 'Plat servi !')"></h4>
                    <p class="text-xs opacity-90" x-text="currentOrderStatus === 'pending' ? '您的订单已送达厨房，正在排队中...' : (currentOrderStatus === 'preparing' ? '大厨正在疯狂制作您的订单，请稍候...' : '您的菜品已出餐，服务员正在全速送往您的餐桌！')"></p>
                </div>
            </div>
        </div>

        <h3 class="font-black border-b pb-2 mb-4 text-gray-700 flex justify-between text-lg">
            <span>Menu du jour (选择菜品)</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <template x-for="dish in dishes" :key="dish.id">
                <div class="flex flex-col justify-between bg-white rounded-2xl border-2 border-gray-100 shadow-sm transition hover:border-blue-100 overflow-hidden">

                    <div class="w-full h-40 bg-gray-50 relative overflow-hidden border-b border-gray-100">
                        <img :src="dish.image_url ? dish.image_url : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&auto=format&fit=crop'"
                            :alt="dish.name"
                            class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                    </div>

                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="font-extrabold text-gray-800 block text-lg" x-text="dish.name"></span>
                            <span class="text-sm text-gray-500 font-semibold block mt-0.5" x-text="dish.price.toFixed(2) + ' €'"></span>
                        </div>

                        <div class="flex items-center justify-between mt-4 border-t pt-3 border-gray-50">
                            <button @click="if(dish.qty > 0) dish.qty--"
                                :class="dish.qty > 0 ? 'bg-red-500 text-white hover:bg-red-600 active:scale-95 shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                class="w-10 h-10 rounded-xl font-black text-xl flex items-center justify-center transition-all duration-150 border border-transparent select-none">-</button>

                            <span :class="dish.qty > 0 ? 'text-blue-600 font-black text-xl' : 'text-gray-400 font-bold text-lg'"
                                class="w-8 text-center font-mono"
                                x-text="dish.qty"></span>

                            <button @click="dish.qty++"
                                class="bg-green-500 hover:bg-green-600 active:scale-95 text-white w-10 h-10 rounded-xl font-black text-xl flex items-center justify-center transition-all duration-150 shadow-md shadow-green-100 select-none">+</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-8">
            <button @click="submit"
                :disabled="!hasItemsSelected()"
                :class="hasItemsSelected() ? 'bg-blue-600 hover:bg-blue-700 active:scale-[0.99] shadow-lg shadow-blue-200 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                class="w-full py-4 rounded-2xl font-black text-xl transition-all duration-150 uppercase tracking-wide flex justify-center items-center space-x-2">
                <span>Envoyer en cuisine 👨‍🍳</span>
                <span x-show="hasItemsSelected()" class="bg-blue-700 px-2 py-0.5 rounded-lg text-sm font-mono" x-text="calculateCurrentTotal() + ' €'"></span>
            </button>
        </div>

        <div x-show="message"
            :class="message.includes('✅') ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'"
            class="mt-4 p-3 rounded-lg border text-center font-bold text-sm"
            x-text="message">
        </div>

        <div class="mt-8 border-t pt-6">
            <h3 class="font-black text-gray-700 text-lg mb-3 flex justify-between items-center">
                <span>Historique (历史已点账单)</span>
                <span class="text-xs bg-gray-800 text-white px-2 py-1 rounded font-mono font-bold" x-text="'Total Table: ' + calculateHistoryTotal() + ' €'"></span>
            </h3>

            <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                <template x-for="hOrder in historyOrders" :key="hOrder.id">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs flex justify-between items-center">
                        <div>
                            <p class="font-bold text-gray-500 font-mono">Order ID: #<span x-text="hOrder.id"></span></p>
                            <div class="mt-1 space-y-0.5 text-gray-700 font-medium">
                                <template x-for="item in hOrder.items">
                                    <p>
                                        <span x-text="item.dish ? item.dish.name : 'Plat'"></span>
                                        x<span x-text="item.quantity"></span>
                                        <span class="text-gray-400 font-mono ml-1" x-text="'(' + (item.dish ? (item.dish.price * item.quantity).toFixed(2) : '0') + ' €)'"></span>
                                    </p>
                                </template>
                            </div>
                        </div>
                        <div class="text-right flex flex-col items-end justify-between h-full space-y-2">
                            <span class="px-2 py-1 rounded-md font-bold uppercase tracking-wider text-[10px]"
                                :class="{
                                    'bg-amber-100 text-amber-700': hOrder.status === 'pending',
                                    'bg-orange-100 text-orange-700': hOrder.status === 'preparing',
                                    'bg-green-100 text-green-700': hOrder.status === 'delivered'
                                }" x-text="hOrder.status"></span>
                            <span class="font-bold text-gray-800 font-mono" x-text="calculateSingleOrderTotal(hOrder) + ' €'"></span>
                        </div>
                    </div>
                </template>

                <p x-show="historyOrders.length === 0" class="text-center text-gray-400 text-sm py-4">暂无历史下单记录</p>
            </div>
        </div>

    </div>

    <script>
        function orderApp() {
            return {
                table_number: '',
                message: '',
                currentOrderStatus: null,
                historyOrders: [],
                statusTimer: null,
                dishes: [],

                initTable() {
                    const urlParams = new window.URLSearchParams(window.location.search);
                    this.table_number = urlParams.get('table') || '1';

                    this.fetchMenu();
                    this.fetchHistory();

                    this.statusTimer = setInterval(() => {
                        this.fetchHistory();
                    }, 4000);
                },

                fetchMenu() {
                    fetch('/api/dishes')
                        .then(r => {
                            if (!r.ok) throw new Error('无法获取菜单');
                            return r.json();
                        })
                        .then(res => {
                            const menuItems = res.data || res;

                            if (Array.isArray(menuItems)) {
                                // 🎯 新增位置 2：在 map 映射中，把数据库的 image_url 接住传给前端
                                this.dishes = menuItems.map(dish => ({
                                    id: dish.id,
                                    name: dish.name,
                                    price: parseFloat(dish.price) || 0.00,
                                    image_url: dish.image_url, // 👈 核心：就是这行抓到了图片路径
                                    qty: 0
                                }));
                            }
                        })
                        .catch(err => {
                            console.error('Menu load error:', err);
                            this.message = '❌ 菜单加载失败，请检查后端 API (/api/dishes)';
                        });
                },

                hasItemsSelected() {
                    return this.dishes.some(d => d.qty > 0);
                },

                calculateCurrentTotal() {
                    return this.dishes
                        .reduce((sum, d) => sum + (d.price * d.qty), 0)
                        .toFixed(2);
                },

                calculateSingleOrderTotal(order) {
                    if (!order.items) return '0.00';
                    return order.items
                        .reduce((sum, item) => {
                            const price = item.dish ? parseFloat(item.dish.price) : 0;
                            return sum + (price * item.quantity);
                        }, 0)
                        .toFixed(2);
                },

                calculateHistoryTotal() {
                    return this.historyOrders
                        .reduce((sum, order) => sum + parseFloat(this.calculateSingleOrderTotal(order)), 0)
                        .toFixed(2);
                },

                fetchHistory() {
                    fetch(`/api/orders?table_number=${this.table_number}`)
                        .then(r => r.json())
                        .then(res => {
                            const allOrders = res.data || res;
                            if (Array.isArray(allOrders)) {
                                this.historyOrders = allOrders;

                                const incomplete = allOrders.filter(o => o.status === 'pending' || o.status === 'preparing');
                                if (incomplete.length > 0) {
                                    this.currentOrderStatus = incomplete[0].status; // 修复了原代码中的一个小打字错误 $this
                                } else {
                                    const delivered = allOrders.filter(o => o.status === 'delivered');
                                    if (delivered.length > 0) {
                                        this.currentOrderStatus = 'delivered';
                                    } else {
                                        this.currentOrderStatus = null;
                                    }
                                }
                            }
                        })
                        .catch(err => console.error('Error syncing status:', err));
                },

                submit() {
                    this.message = '🔄 Envoi en cours...';

                    fetch('/api/orders', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_type: 'dine_in',
                                table_number: this.table_number,
                                restaurant_id: 1,
                                name: 'Table ' + this.table_number,
                                phone: '0000000000',
                                items: this.dishes
                                    .filter(d => d.qty > 0)
                                    .map(d => ({
                                        dish_id: d.id,
                                        quantity: d.qty
                                    }))
                            })
                        })
                        .then(r => {
                            if (!r.ok) return r.json().then(err => {
                                throw err;
                            });
                            return r.json();
                        })
                        .then(() => {
                            this.message = '✅ Commande envoyée en cuisine !';
                            this.dishes.forEach(d => d.qty = 0);
                            this.fetchHistory();
                        })
                        .catch((error) => {
                            console.error('Backend Error:', error);
                            this.message = '❌ Échec: ' + (error.message || 'Veuillez vérifier les champs.');
                        });
                }
            }
        }
    </script>
</body>

</html>