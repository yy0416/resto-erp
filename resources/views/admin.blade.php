<!doctype html>
<html lang="zh">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resto ERP - 总控制后台</title>
    @vite('resources/css/app.css')
    <script defer src="https://unpkg.com/alpinejs"></script>
</head>

<body class="bg-gray-900 text-gray-100 font-sans min-h-screen flex" x-data="adminMenuApp()" x-init="initApp()">

    <aside class="w-64 bg-gray-950 border-r border-gray-800 p-6 flex flex-col justify-between h-screen sticky top-0">

        <div>
            <div class="mb-8 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-black text-white tracking-wider flex items-center space-x-2">
                    <span>⚙️ Resto ERP</span>
                </h2>

                <span class="text-[10px] font-mono uppercase font-black px-2 py-0.5 rounded mt-2 inline-block border"
                    :class="AdminRole === 'admin' ? 'bg-purple-900/40 text-purple-400 border-purple-800' : 'bg-blue-900/40 text-blue-400 border-blue-800'">
                    <span x-text="AdminRole === 'admin' ? '👑 Owner / Admin' : '💰 Caissier / Staff'"></span>
                </span>
            </div>

            <nav class="space-y-1.5">
                @if(auth()->user()->role === 'admin')
                <button @click="currentTab = 'dashboard'" :class="currentTab === 'dashboard' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>📊 运营大厅</span>
                </button>
                <button @click="currentTab = 'dishes'" :class="currentTab === 'dishes' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>🍔 菜单管理</span>
                </button>
                <a href="{{ route('admin.tables.index') }}" class="w-full flex items-center space-x-3 p-3 text-gray-400 hover:bg-gray-800/60 hover:text-white rounded-xl transition font-bold text-left text-sm">
                    <span>⚙️ 桌位配置 (基建)</span>
                </a>
                @endif

                <button @click="currentTab = 'orders'" :class="currentTab === 'orders' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>📑 订单流水</span>
                </button>

                <button @click="currentTab = 'tables'" :class="currentTab === 'tables' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>🪑 桌号管理</span>
                </button>
                <button @click="currentTab = 'caisse'" :class="currentTab === 'caisse' ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'text-gray-400 hover:bg-gray-800/60 hover:text-white'" class="w-full flex items-center space-x-3 p-3 rounded-xl transition font-bold text-left text-sm">
                    <span>💰 结账收银</span>
                </button>
            </nav>
        </div>

        <div class="space-y-4">


            <form action="{{ url('/logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-center bg-gray-900 hover:bg-red-950/40 text-gray-500 hover:text-red-400 border border-gray-800 hover:border-red-900/60 py-2.5 rounded-xl font-bold text-xs transition select-none">
                    Déconnexion (安全退出) ➔
                </button>
            </form>
            <div class="text-[10px] text-gray-600 font-mono font-bold border-t border-gray-800 pt-4">
                System v1.0.0 @ 2026 ERP
            </div>
        </div>

    </aside>

    <main class="flex-1 p-8 overflow-y-auto h-screen">

        @if(auth()->user()->role === 'admin')
        <div x-show="currentTab === 'dashboard'">
            @include('admin.dashboard')
        </div>
        <div x-show="currentTab === 'dishes'">
            @include('admin.dishes')
        </div>
        @endif

        <div x-show="currentTab === 'orders'">
            @include('admin.orders')
        </div>

        <div x-show="currentTab === 'tables'">
            @include('admin.tables', ['tables' => $tables])
        </div>

        <div x-show="currentTab === 'caisse'">
            @include('admin.caisse')
        </div>

    </main>

    <script>
        function adminMenuApp() {
            return {
                // 🎯 核心修正：使用标准 JS 字符串包装，骗过 VS Code 的静态检查，同时保证 Laravel 正常输出
                AdminRole: "" + "{{ auth()->user()->role }}",

                // 🎯 核心修正：同样的方式处理默认 Tab，确保检查器不再误报缺少逗号
                currentTab: "" + "{{ auth()->user()->role === 'admin' ? 'dashboard' : 'orders' }}",

                dishes: [],
                loading: false,
                message: '',
                form: {
                    name: '',
                    price: '',
                    imageFile: null
                },

                // ⚡ 初始化函数
                initApp() {
                    // 先去拿菜单数据
                    this.fetchDishes();

                    // 🎯 前端监视拦截器
                    this.$watch('currentTab', value => {
                        if (this.AdminRole !== 'admin' && (value === 'dashboard' || value === 'dishes')) {
                            this.currentTab = 'orders';
                            alert('🚫 Accès refusé. 您没有权限访问老板专属的机密运营大厅！');
                        }
                    });
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