import RoleOutlet from "../../shell/layouts/RoleOutlet.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "owner",
    home: "owner-dashboard",
    nav: [
        { label: "Дашборд", routeName: "owner-dashboard", icon: "dashboard", order: 1 },
        { label: "Заказы", routeName: "owner-orders", icon: "orders", order: 2 },
        { label: "Каталог", routeName: "owner-catalog", icon: "catalog", order: 3 },
        { label: "Компания", routeName: "owner-company", icon: "company", order: 4 },
        { label: "Доставка", routeName: "owner-delivery", icon: "delivery", order: 5 },
        { label: "Акции", routeName: "owner-promotion", icon: "promotion", order: 6 },
        { label: "Маркетинг", routeName: "owner-marketing", icon: "marketing", order: 7 },
        { label: "Сотрудники", routeName: "owner-staff", icon: "staff", order: 8 },
        { label: "Клиенты", routeName: "owner-clients", icon: "clients", order: 9 },
        { label: "Склад", routeName: "owner-warehouse", icon: "warehouse", order: 10 },
        { label: "Финансы", routeName: "owner-finance", icon: "finance", order: 11 },
    ],
    routes: [
        {
            path: "/owner",
            component: RoleOutlet,
            meta: { role: "owner" },
            children: [
                { path: "", redirect: { name: "owner-dashboard" } },
                {
                    path: "dashboard",
                    name: "owner-dashboard",
                    component: () => import("./screens/DashboardScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "orders",
                    name: "owner-orders",
                    component: () => import("./screens/OrdersScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "catalog",
                    name: "owner-catalog",
                    component: () => import("./screens/CatalogScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "company",
                    name: "owner-company",
                    component: () => import("./screens/CompanyScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "delivery",
                    name: "owner-delivery",
                    component: () => import("./screens/DeliveryScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "promotion",
                    name: "owner-promotion",
                    component: () => import("./screens/PromotionScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "marketing",
                    name: "owner-marketing",
                    component: () => import("./screens/MarketingScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "staff",
                    name: "owner-staff",
                    component: () => import("./screens/StaffScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "clients",
                    name: "owner-clients",
                    component: () => import("./screens/ClientsScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "warehouse",
                    name: "owner-warehouse",
                    component: () => import("./screens/WarehouseScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "finance",
                    name: "owner-finance",
                    component: () => import("./screens/FinanceScreen.vue"),
                    meta: { role: "owner" },
                },
            ],
        },
    ],
};

export default module;
