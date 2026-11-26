import RoleOutlet from "../../shell/layouts/RoleOutlet.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "administrator",
    home: "administrator-live-orders",
    nav: [
        { label: "Дашборд", routeName: "administrator-dashboard", icon: "dashboard", order: 0 },
        { label: "Заказы", routeName: "administrator-live-orders", icon: "live", order: 1 },
        {
            label: "Маршрутные листы",
            routeName: "administrator-logistics",
            icon: "logistics",
            order: 2,
        },
        { label: "Клиенты", routeName: "administrator-clients", icon: "clients", order: 3 },
        { label: "Финансы", routeName: "administrator-finance", icon: "finance", order: 4 },
        { label: "Тикеты", routeName: "administrator-production", icon: "production", order: 5 },
        { label: "Каталог", routeName: "administrator-catalog", icon: "catalog", order: 6 },
    ],
    routes: [
        {
            path: "/administrator",
            component: RoleOutlet,
            meta: { role: "administrator" },
            children: [
                { path: "", redirect: { name: "administrator-live-orders" } },
                {
                    path: "dashboard",
                    name: "administrator-dashboard",
                    component: () => import("./screens/DashboardScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "live-orders",
                    name: "administrator-live-orders",
                    component: () => import("./screens/LiveOrdersScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "orders/new",
                    name: "administrator-order-new",
                    component: () => import("./screens/OrderWorkspaceScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "orders/:id",
                    name: "administrator-order",
                    component: () => import("./screens/OrderWorkspaceScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "manual-order",
                    redirect: { name: "administrator-order-new" },
                },
                {
                    path: "logistics",
                    name: "administrator-logistics",
                    component: () => import("./screens/LogisticsScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "logistics/assemble",
                    name: "administrator-logistics-assemble",
                    component: () => import("./screens/RouteSheetAssembleScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "clients",
                    name: "administrator-clients",
                    component: () => import("./screens/ClientsScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "clients/:id",
                    name: "administrator-client",
                    component: () => import("./screens/ClientCardScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "finance",
                    name: "administrator-finance",
                    component: () => import("./screens/FinanceScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "production",
                    name: "administrator-production",
                    component: () => import("./screens/ProductionReadScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "catalog",
                    name: "administrator-catalog",
                    component: () => import("./screens/CatalogReadScreen.vue"),
                    meta: { role: "administrator" },
                },
            ],
        },
    ],
};

export default module;
