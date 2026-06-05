<div x-data="dishesManager()" x-init="fetchDishes()" class="space-y-6 relative">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm h-fit">
            <h3 class="text-lg font-black text-gray-800 mb-4 flex items-center space-x-2">
                <span>➕ Ajouter un Plat (上架新菜)</span>
            </h3>

            <form @submit.prevent="submitDish" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nom du plat (菜品名称)</label>
                    <input type="text" x-model="form.name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-blue-500 font-medium text-sm" placeholder="Ex: Tiramisu Maison">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Prix (€) (单价)</label>
                    <input type="number" step="0.01" x-model="form.price" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:border-blue-500 font-mono font-semibold text-sm" placeholder="0.00">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Photo (菜品图片)</label>
                    <input type="file" @change="form.imageFile = $event.target.files[0]" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <button type="submit" :disabled="loading" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3 rounded-xl transition active:scale-[0.98] disabled:opacity-50 text-sm shadow-md shadow-blue-100">
                    <span x-text="loading ? 'Envoi...' : 'Confirmer et Publier 🚀'"></span>
                </button>
            </form>

            <div x-show="message" :class="message.includes('✅') ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'" class="mt-4 p-3 rounded-xl border text-center font-bold text-xs" x-text="message"></div>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border-2 border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-gray-800 mb-4">📋 Menu Actuel (当前菜单流水线)</h3>

            <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                <template x-for="dish in dishes" :key="dish.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100/70 rounded-xl border border-gray-100 transition">
                        <div class="flex items-center space-x-4">
                            <img :src="dish.image_url ? dish.image_url : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=150&auto=format&fit=crop'" class="w-12 h-12 rounded-xl object-cover border">
                            <div>
                                <h4 class="font-bold text-gray-800" x-text="dish.name"></h4>
                                <p class="text-sm font-mono font-bold text-blue-600 mt-0.5" x-text="parseFloat(dish.price).toFixed(2) + ' €'"></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button @click="openEditModal(dish)" class="bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-black px-3 py-2 rounded-xl transition shadow-sm">
                                ✏️ Modifier
                            </button>
                            <button @click="deleteDish(dish.id)" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold p-2 rounded-xl transition">
                                🗑️
                            </button>
                        </div>
                    </div>
                </template>

                <p x-show="dishes.length === 0" class="text-center text-gray-400 text-sm py-8">暂无菜品数据，请在左侧添加。</p>
            </div>
        </div>
    </div>

    <div x-show="editModal.open"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-opacity duration-300"
        style="display: none;">

        <div class="bg-white w-full max-w-md p-6 rounded-2xl shadow-2xl border transform transition-all m-4">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-lg font-black text-gray-800">✏️ Modifier le Plat (修改菜品信息)</h3>
                <button @click="editModal.open = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
            </div>

            <form @submit.prevent="updateDish" class="space-y-5">
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1 tracking-wide">Nom du plat (新菜名)</label>
                    <input type="text"
                        x-model="editModal.form.name"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-300 bg-white text-gray-900 font-bold text-sm shadow-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1 tracking-wide">Prix (€) (新单价)</label>
                    <input type="number"
                        step="0.01"
                        x-model="editModal.form.price"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-300 bg-white text-gray-950 font-mono font-black text-base shadow-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1 tracking-wide">Remplacer la Photo (更换新图片 - 可选)</label>
                    <div class="mb-2 flex items-center space-x-3 bg-gray-50 p-2 rounded-lg border-2 border-dashed border-gray-200">
                        <img :src="editModal.form.currentImageUrl ? editModal.form.currentImageUrl : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=150&auto=format&fit=crop'" class="w-10 h-10 rounded object-cover shadow-sm">
                        <span class="text-xs text-gray-600 font-bold">当前线上展示图</span>
                    </div>
                    <input type="file" @change="editModal.form.imageFile = $event.target.files[0]" class="w-full text-xs text-gray-600 font-medium file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 cursor-pointer">
                </div>

                <div class="flex space-x-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="editModal.open = false" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold py-2.5 rounded-xl transition text-sm">
                        Annuler (取消)
                    </button>
                    <button type="submit" :disabled="editModal.loading" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-black py-2.5 rounded-xl transition active:scale-[0.98] disabled:opacity-50 text-sm shadow-md shadow-amber-100">
                        <span x-text="editModal.loading ? 'Enregistrement...' : 'Sauvegarder (保存) 💾'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function dishesManager() {
        return {
            dishes: [],
            loading: false,
            message: '',
            form: {
                name: '',
                price: '',
                imageFile: null
            },

            // 🎯 核心新增 3：在 Alpine 数据仓库里，管理编辑模态框的独立状态域
            editModal: {
                open: false,
                loading: false,
                form: {
                    id: null,
                    name: '',
                    price: '',
                    currentImageUrl: '',
                    imageFile: null
                }
            },

            fetchDishes() {
                fetch('/api/dishes')
                    .then(r => r.json())
                    .then(res => {
                        this.dishes = res.data || res;
                    })
                    .catch(err => console.error(err));
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
                        if (!r.ok) throw new Error(data.error_message || '未知服务器错误');
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

            // 🎯 核心新增 4：点击编辑按钮，把当前这行菜的数据强行灌入弹窗表单中，并打开弹窗
            openEditModal(dish) {
                this.editModal.form.id = dish.id;
                this.editModal.form.name = dish.name;
                this.editModal.form.price = dish.price;
                this.editModal.form.currentImageUrl = dish.image_url;
                this.editModal.form.imageFile = null; // 重置文件选择器
                this.editModal.open = true; // 唤醒弹窗！
            },

            // 🎯 核心新增 5：提交修改后的表单（呼叫后端 PUT 请求替换数据）
            updateDish() {
                this.editModal.loading = true;

                const formData = new window.FormData();
                formData.append('name', this.editModal.form.name);
                formData.append('price', this.editModal.form.price);
                if (this.editModal.form.imageFile) {
                    formData.append('image', this.editModal.form.imageFile);
                }
                // 💡 知识点：由于浏览器原生 FormData 不支持直接发 PUT 请求带文件上传，
                // 外部必须发送 POST，并带上 Laravel 独有的 _method=PUT 假冒伪装标志！
                formData.append('_method', 'PUT');

                fetch(`/api/dishes/${this.editModal.form.id}`, {
                        method: 'POST', // 👈 伪装成 POST 绕过文件封锁，Laravel 底层会识别成 PUT
                        body: formData
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok) throw new Error(data.error_message || '修改失败');
                        return data;
                    })
                    .then(() => {
                        alert('✅ Modifications enregistrées ! (修改已成功保存)');
                        this.editModal.open = false; // 关闭弹窗
                        this.fetchDishes(); // 无刷新刷新列表！
                    })
                    .catch(err => {
                        alert('❌ ' + err.message);
                    })
                    .finally(() => {
                        this.editModal.loading = false;
                    });
            },

            deleteDish(id) {
                if (!confirm('Voulez-vous vraiment supprimer ce plat ?')) return;

                fetch(`/api/dishes/${id}`, {
                        method: 'DELETE'
                    })
                    .then(() => {
                        this.fetchDishes();
                    })
                    .catch(err => console.error(err));
            }
        };
    }
</script>