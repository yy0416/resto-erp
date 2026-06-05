<div>
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-black text-white tracking-wide">🍔 Menu Actuel (菜单库管理)</h2>
            <p class="text-xs text-gray-400 mt-1">实时增删改查餐厅菜单库，数据无缝同步平板端</p>
        </div>
        <span class="bg-gray-950 px-3 py-1.5 rounded-xl text-xs font-mono font-bold text-gray-400 border border-gray-800"
            x-text="'Total: ' + dishes.length + ' 道菜'"></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="bg-gray-800/60 p-6 rounded-2xl border border-gray-700/60 shadow-xl h-fit">
            <h3 class="text-sm font-black text-gray-300 uppercase tracking-wider mb-4">➕ 上架新菜品</h3>

            <form @submit.prevent="submitDish" class="space-y-4">
                <div>
                    <label class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">菜品名称 *</label>
                    <input x-model="form.name" type="text" required placeholder="例如：🍔 Classic Cheese Burger"
                        class="w-full bg-gray-900 border-2 border-gray-700 rounded-xl p-3 text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 transition text-sm">
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">标准单价 (€) *</label>
                    <input x-model="form.price" type="number" step="0.01" required placeholder="0.00"
                        class="w-full bg-gray-900 border-2 border-gray-700 rounded-xl p-3 text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 transition font-mono text-sm">
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">菜品图片 (选填)</label>
                    <input @change="handleFileUpload" type="file" accept="image/*"
                        class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-700 file:text-white hover:file:bg-gray-600 cursor-pointer">
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-xl font-bold transition active:scale-[0.99] disabled:opacity-50 text-sm flex justify-center items-center">
                    <span x-show="!loading">确认上架菜品 🚀</span>
                    <span x-show="loading">🔄 正在发送数据...</span>
                </button>
            </form>

            <div x-show="message" :class="message.includes('✅') ? 'bg-green-950/80 border-green-800 text-green-400' : 'bg-red-950/80 border-red-800 text-red-400'"
                class="mt-4 p-3 rounded-xl border text-xs font-bold text-center" x-text="message">
            </div>
        </div>

        <div class="lg:col-span-2 bg-gray-800/60 p-6 rounded-2xl border border-gray-700/60 shadow-xl">
            <div class="overflow-x-auto rounded-xl border border-gray-700">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-900 text-gray-400 uppercase text-[10px] tracking-wider font-bold border-b border-gray-700">
                            <th class="p-4">ID</th>
                            <th class="p-4">预览图</th>
                            <th class="p-4">菜品名称</th>
                            <th class="p-4">标准单价</th>
                            <th class="p-4 text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50 text-sm">
                        <template x-for="dish in dishes" :key="dish.id">
                            <tr class="hover:bg-gray-700/30 transition">
                                <td class="p-4 font-mono text-gray-500 font-bold" x-text="'#' + dish.id"></td>
                                <td class="p-4">
                                    <template x-if="dish.image_url">
                                        <img :src="dish.image_url" class="w-12 h-12 object-cover rounded-xl border border-gray-600 shadow-sm">
                                    </template>
                                    <template x-if="!dish.image_url">
                                        <div class="w-12 h-12 bg-gray-950 rounded-xl flex items-center justify-center text-xs text-gray-600 font-bold">无图</div>
                                    </template>
                                </td>
                                <td class="p-4 font-extrabold text-white text-base" x-text="dish.name"></td>
                                <td class="p-4 font-mono font-bold text-green-400 text-base" x-text="formatPrice(dish.price)"></td>
                                <td class="p-4 text-center">
                                    <button @click="deleteDish(dish.id)"
                                        class="bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white px-3 py-1.5 rounded-xl font-bold text-xs transition border border-red-500/20 active:scale-95">
                                        🗑️ 下架
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>