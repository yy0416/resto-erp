# 🍽️ Resto-ERP & 厨房大屏一体化系统

这是一个为自己餐厅定制的、集成了**前台点餐网站**、**后台ERP管理**以及**厨房大屏（KDS）**的一体化餐饮SaaS系统。

## 🛠️ 技术栈

- **后端：** PHP 11 (Laravel 框架)
- **前端：** Vite + TailwindCSS 4.0 (现代化样式库) + Axios (异步请求)
- **数据库：** MySQL

## 📌 当前项目基建进度 (4个月前回顾)

- [x] Laravel 框架核心骨架初始化
- [x] 前端构建工具配置 (Vite 联动 TailwindCSS 4.0 核心配置)
- [x] 引入 Axios 依赖，用于前后端异步 API 数据交互
- [ ] 数据库核心表结构设计 (Menus, Orders, Tables)
- [ ] 顾客前台点餐 H5 页面 UI 开发

## 🎯 核心待办开发计划 (TODO)

### 1. 数据库与业务逻辑 (MySQL + Eloquent ORM)

- 创建 `menus` 菜品表（管理菜品分类、法语/中文名称、价格）
- 创建 `orders` 订单表（管理订单状态：已下单 `pending`、制作中 `cooking`、已上菜 `served`、已结账 `paid`）

### 2. 实时厨房大屏 (KDS)

- **技术选型：** 计划使用 Laravel Reverb (Laravel 11自带的 WebSocket 广播) 或 SSE。
- **业务目标：** 顾客在手机端确认下单后，数据异步发送至后台，厨房大屏不刷新页面即时接收弹窗，并触发语音播报。

### 3. ERP 营业额统计

- 编写每日/每周/每月 Chiffre d'affaires (营业额) 统计逻辑。
- _留作后续与法国会计和警察局对接合规收入数据的导出底座。_

## 🚀 本地开发环境启动

1. 确保安装了 PHP 8.x+ 和 Node.js
2. 启动后端：`php artisan serve`
3. 启动前端监听：`npm run dev`
