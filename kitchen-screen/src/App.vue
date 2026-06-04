<template>
    <div class="kitchen-screen">
        <h1>👨‍🍳 厨房一体大屏</h1>
        <div class="orders">
            <div
                v-for="order in orders"
                :key="order.id"
                class="order-card"
                :class="[
                    order.order_type,
                    { late: getMinutes(order.started_at) >= 20 },
                ]"
            >
                <div class="order-header">
                    <span v-if="order.order_type === 'dine_in'"
                        >桌号: {{ order.table_number }}</span
                    >
                    <span v-else>订单ID: {{ order.id }}</span>

                    <span class="status-badge" :class="order.status">
                        {{ order.status === "pending" ? "排队中" : "制作中" }}
                    </span>
                </div>

                <div class="order-items">
                    <div v-for="item in order.items" :key="item.id">
                        {{ item.dish ? item.dish.name : "未知菜品" }} x{{
                            item.quantity
                        }}
                    </div>
                </div>

                <div class="duration">
                    准备时间: {{ getMinutes(order.started_at) }} 分钟
                </div>

                <div class="actions-wrapper">
                    <button
                        v-if="order.status === 'pending'"
                        class="btn-start"
                        @click="updateStatus(order.id, 'preparing')"
                    >
                        🔥 开始制作
                    </button>

                    <button
                        v-if="order.status === 'preparing'"
                        class="btn-complete"
                        @click="updateStatus(order.id, 'delivered')"
                    >
                        ✅ 标记完成
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";

const orders = ref([]);
const now = ref(Date.now());

let timer = null;
let clock = null;

const fetchOrders = async () => {
    try {
        const res = await axios.get("http://127.0.0.1:8000/api/orders");
        // 控制器 index 方法升级后，这里依然过滤 pending 和 preparing
        orders.value = res.data.data.filter(
            (o) => o.status === "pending" || o.status === "preparing",
        );
    } catch (e) {
        console.error(e);
    }
};

// 🎯 升级：通用的状态修改方法
const updateStatus = async (orderId, newStatus) => {
    try {
        await axios.patch(`http://127.0.0.1:8000/api/orders/${orderId}`, {
            status: newStatus,
        });

        if (newStatus === "delivered") {
            // 如果是出餐，直接从大屏移除卡片
            orders.value = orders.value.filter((o) => o.id !== orderId);
        } else {
            // 如果是开始制作，更新本地状态，按钮会自动变成“标记完成”
            const order = orders.value.find((o) => o.id === orderId);
            if (order) order.status = newStatus;
        }
    } catch (e) {
        console.error("更新状态失败:", e);
    }
};

const getMinutes = (startedAt) => {
    return Math.floor((now.value - new Date(startedAt).getTime()) / 60000);
};

onMounted(() => {
    fetchOrders();
    timer = setInterval(fetchOrders, 5000);
    clock = setInterval(() => (now.value = Date.now()), 10000); // 提高时间刷新率到10秒
});

onUnmounted(() => {
    clearInterval(timer);
    clearInterval(clock);
});
</script>

<style>
html,
body,
#app {
    margin: 0;
    padding: 0;
    width: 100vw;
    height: 100vh;
    background: #111;
    color: #fff;
    overflow: hidden;
}
.kitchen-screen {
    display: flex;
    flex-direction: column;
    height: 100vh;
    width: 100vw;
    padding: 16px;
    box-sizing: border-box;
}
.kitchen-screen h1 {
    margin: 0 0 12px;
    font-size: 2rem;
    text-align: left;
}
.orders {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    grid-auto-rows: minmax(220px, auto);
    gap: 16px;
    overflow-y: auto;
    padding-right: 10px;
}
.order-card {
    background: #1e1e1e;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 180px;
    transition: transform 0.2s ease-in-out;
}
.order-card:hover {
    transform: scale(1.03);
}
.order-card.dine_in {
    border-left: 8px solid #4caf50;
}
.order-card.takeaway {
    border-left: 8px solid #ff9800;
}
.order-card.late .order-header {
    background: #b71c1c;
    padding: 4px;
    border-radius: 4px;
}
.order-header {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    align-items: center;
}
.order-items {
    flex: 1;
    margin-top: 12px;
    text-align: left;
    font-size: 1.2rem;
}
.duration {
    font-size: 0.9rem;
    color: #ccc;
    margin-bottom: 8px;
}

/* 🌟 新增按钮与状态样式 */
.status-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
}
.status-badge.pending {
    background: #5d4037;
    color: #ffb74d;
}
.status-badge.preparing {
    background: #1b5e20;
    color: #81c784;
}

.actions-wrapper button {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: bold;
    color: white;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-start {
    background: #ff9800;
}
.btn-start:hover {
    background: #f57c00;
}
.btn-complete {
    background: #4caf50;
}
.btn-complete:hover {
    background: #388e3c;
}
</style>
