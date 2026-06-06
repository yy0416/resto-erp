<!doctype html>
<html lang="zh">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resto ERP - 总控制后台</title>
    @vite('resources/css/app.css')
    <script defer src="https://unpkg.com/alpinejs"></script>
</head>

<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex" x-data="adminMenuApp()" x-init="fetchDishes()">

    <aside class="w-64 bg-gray-950 border-r border-gray-800 p-6 flex flex-col justify-between h-screen sticky top-0">
        <div>
            <div class="mb-8 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-black text-white tracking-wider flex items-center space-x-2">
                    <span>⚙️ Resto ERP</span>
                </h2>
                <span class="text-[10px] font-mono uppercase bg-blue-900/50 text-blue-400 font-bold px-2 py-0.5 rounded border border-blue-800 mt-2 inline-block">
                    Super Admin
                </span>
            </div>

            <nav class="space-y-1.5">
                <button @click="currentTab = 'dashboard'" :class="currentTab === 'dashboard' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>📊 运营大厅</span>
                </button>
                <button @click="currentTab = 'orders'" :class="currentTab === 'orders' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>📑 订单流水</span>
                </button>
                <button @click="currentTab = 'dishes'" :class="currentTab === 'dishes' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>🍔 菜单管理</span>
                </button>
                <button @click="currentTab = 'tables'" :class="currentTab === 'tables' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>🪑 桌号管理</span>
                </button>
                <button @click="currentTab = 'caisse'" :class="currentTab === 'caisse' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>💰 结账收银</span>
                </button>
            </nav>
        </div>
        <div class="text-[10px] text-gray-600 font-mono font-bold border-t border-gray-800 pt-4">
            System v1.0.0 @ 2026 ERP
        </div>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto h-screen">

        <div x-show="currentTab === 'dashboard'">
            @include('admin.dashboard')
        </div>

        <div x-show="currentTab === 'orders'">
            @include('admin.orders')
        </div>

        <div x-show="currentTab === 'dishes'">
            @include('admin.dishes')
        </div>

        <div x-show="currentTab === 'tables'">
            @include('admin.tables')
        </div>

        <div x-show="currentTab === 'caisse'">
            @include('admin.caisse')
        </div>

    </main>

    <script>
        function adminMenuApp() {
            return {
                // 🎯 优化点：让后台一打开，默认停在最具有成就感的 'dashboard' 报表大厅！
                currentTab: 'dashboard',

                dishes: [],
                loading: false,
                message: '',
                form: {
                    name: '',
                    price: '',
                    imageFile: null
                },

                formatPrice(price) {
                    return parseFloat(price).toFixed(2) + ' €';
                },

                fetchDishes() {
                    fetch('/api/dishes')
                        .then(r => r.json())
                        .then(data => {
                            this.dishes = data;
                        })
                        .catch(err => console.error('读取失败:', err));
                },

                handleFileUpload(e) {
                    this.form.imageFile = e.target.files[0];
                },

                submitDish() {
                    this.loading = true;
                    this.message = '🔄 上传中...';

                    const formData = new window.FormData();
                    formData.append('name', this.form.name);
                    formData.append('price', this.form.price);
                    if (this.form.imageFile) {
                        formData.append('image', this.form.imageFile);
                    }

                    fetch('/api/dishes', {
                            method: 'POST',
                            body: formData
                        })
                        .then(async r => {
                            const data = await r.json();
                            if (!r.ok) {
                                throw new Error(data.error_message || '未知服务器错误');
                            }
                            return data;
                        })
                        .then(() => {
                            this.message = '✅ 菜品成功上架并存入数据库！';
                            this.form.name = '';
                            this.form.price = '';
                            this.form.imageFile = null;
                            document.querySelector('input[type="file"]').value = '';
                            this.fetchDishes();
                        })
                        .catch(err => {
                            this.message = '❌ ' + err.message;
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                deleteDish(id) {
                    if (!confirm('下架后平板端将同步消失，确定？')) return;
                    fetch(`/api/dishes/${id}`, {
                            method: 'DELETE'
                        })
                        .then(() => {
                            this.message = '🗑️ 菜品已成功移除';
                            this.fetchDishes();
                        });
                }
            }
        }
    </script>
</body>

</html>