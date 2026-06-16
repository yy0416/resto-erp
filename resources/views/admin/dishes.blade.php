<meta name="csrf-token" content="{{ csrf_token() }}">

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
                    <div class="flex items-center justify-between p-3 rounded-xl border transition duration-200"
                        :class="dish.is_available ? 'bg-gray-50 border-gray-100 hover:bg-gray-100/70' : 'bg-gray-100/60 border-gray-200 opacity-75'">

                        <div class="flex items-center space-x-4">
                            <img :src="dish.image_url ? dish.image_url : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=150&auto=format&fit=crop'"
                                class="w-12 h-12 rounded-xl object-cover border"
                                :class="!dish.is_available && 'grayscale opacity-60'">
                            <div>
                                <h4 class="font-bold text-gray-800" x-text="dish.name"></h4>
                                <p class="text-sm font-mono font-bold text-blue-600 mt-0.5" x-text="parseFloat(dish.price).toFixed(2) + ' €'"></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">

                            <div class="flex items-center space-x-2">
                                <button @click="toggleDishAvailable(dish)"
                                    :class="dish.is_available ? 'bg-emerald-500' : 'bg-neutral-300'"
                                    class="w-10 h-5.5 flex items-center rounded-full p-0.5 duration-300 cursor-pointer focus:outline-none shadow-inner relative transition-colors">
                                    <div :class="dish.is_available ? 'translate-x-4 bg-white' : 'translate-x-0 bg-neutral-500'"
                                        class="w-4.5 h-4.5 rounded-full shadow duration-300 transform transition-transform"></div>
                                </button>
                                <span class="text-[10px] font-black tracking-wider px-1.5 py-0.5 rounded uppercase font-sans"
                                    :class="dish.is_available ? 'bg-emerald-50 text-emerald-700' : 'bg-neutral-200 text-neutral-600'"
                                    x-text="dish.is_available ? 'En Vente' : 'Épuisé'">
                                </span>
                            </div>

                            <div class="h-6 w-[1px] bg-gray-200"></div>

                            <div class="flex items-center space-x-1.5">
                                <button @click="openEditModal(dish)" class="bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-black px-3 py-2 rounded-xl transition shadow-sm">
                                    ✏️ Modifier
                                </button>
                                <button @click="deleteDish(dish.id)" class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold p-2 rounded-xl transition">
                                    🗑️
                                </button>
                            </div>

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
                    <input type="text" x-model="editModal.form.name" required class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-300 bg-white text-gray-900 font-bold text-sm shadow-sm focus:outline-none focus:border-blue-600 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1 tracking-wide">Prix (€) (新单价)</label>
                    <input type="number" step="0.01" x-model="editModal.form.price" required class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-300 bg-white text-gray-950 font-mono font-black text-base shadow-sm focus:outline-none focus:border-blue-600 transition">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase mb-1 tracking-wide">Remplacer la Photo</label>
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

            toggleDishAvailable(dish) {
                dish.is_available = !dish.is_available;

                // 💡 顺便对估清划动请求也注入安全 Token，保障操作稳定性
                const tokenTag = document.querySelector('meta[name="csrf-token"]');
                const headers = {
                    'Content-Type': 'application/json'
                };
                if (tokenTag) {
                    headers['X-CSRF-TOKEN'] = tokenTag.content;
                }

                fetch(`/api/dishes/${dish.id}/toggle-available`, {
                        method: 'PATCH',
                        headers: headers
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) {
                            dish.is_available = !dish.is_available;
                            alert('更新失败，请重试');
                        }
                    })
                    .catch(err => {
                        dish.is_available = !dish.is_available;
                        console.error(err);
                    });
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

                // 🛠️ 同步修复：上架菜品也通过 FormData 塞入验证 Token
                const tokenTag = document.querySelector('meta[name="csrf-token"]');
                if (tokenTag) {
                    formData.append('_token', tokenTag.content);
                }

                fetch('/api/dishes', {
                        method: 'POST',
                        body: formData,
                        headers: {}
                    })
                    .then(async r => {
                        if (r.status === 419) throw new Error('安全令牌失效，请刷新页面后重新添加。');
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

            openEditModal(dish) {
                this.editModal.form.id = dish.id;
                this.editModal.form.name = dish.name;
                this.editModal.form.price = parseFloat(dish.price) || 0;
                this.editModal.form.currentImageUrl = dish.image_url;
                this.editModal.form.imageFile = null;
                this.editModal.open = true;
            },

            updateDish() {
                this.editModal.loading = true;

                const formData = new window.FormData();
                formData.append('name', this.editModal.form.name);
                formData.append('price', this.editModal.form.price);
                if (this.editModal.form.imageFile) {
                    formData.append('image', this.editModal.form.imageFile);
                }
                formData.append('_method', 'PUT');

                // 🎯 完美嵌入：不破损文件上传 boundary 的前提下，直接将 Token 编入表单数据
                const tokenTag = document.querySelector('meta[name="csrf-token"]');
                if (tokenTag) {
                    formData.append('_token', tokenTag.content);
                }

                fetch(`/api/dishes/${this.editModal.form.id}`, {
                        method: 'POST',
                        body: formData,
                        headers: {} // 保持为空，由浏览器自动计算 multipart 边界
                    })
                    .then(async r => {
                        if (r.status === 419) {
                            throw new Error('安全令牌(Token)失效或页面过期，请刷新整个网页重新操作。');
                        }

                        const contentType = r.headers.get("content-type");
                        if (!contentType || !contentType.includes("application/json")) {
                            throw new Error('服务器没有正常返回 JSON 数据，请确保服务器逻辑未崩溃。');
                        }

                        const data = await r.json();
                        if (!r.ok) throw new Error(data.error_message || '修改失败');
                        return data;
                    })
                    .then(() => {
                        alert('✅ Modifications enregistrées !');
                        this.editModal.open = false;
                        this.fetchDishes();
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

                // 💡 顺便对删除菜品请求也注入安全 Token
                const tokenTag = document.querySelector('meta[name="csrf-token"]');
                const headers = {};
                if (tokenTag) {
                    headers['X-CSRF-TOKEN'] = tokenTag.content;
                }

                fetch(`/api/dishes/${id}`, {
                        method: 'DELETE',
                        headers: headers
                    })
                    .then(() => {
                        this.fetchDishes();
                    })
                    .catch(err => console.error(err));
            }
        };
    }
</script>