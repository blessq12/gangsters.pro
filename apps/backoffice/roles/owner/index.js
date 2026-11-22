import RoleOutlet from "../../shell/layouts/RoleOutlet.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "owner",
    home: "owner-dashboard",
    nav: [
        { label: "Дашборд", routeName: "owner-dashboard", icon: "dashboard", order: 1 },
        { label: "Заказы", routeName: "owner-orders", icon: "orders", order: 2 },
        { label: "Каталог", routeName: "owner-menu", icon: "menu", order: 3 },
        { label: "Компания", routeName: "owner-company", icon: "company", order: 4 },
        { label: "Доставка", routeName: "owner-delivery", icon: "delivery", order: 5 },
        { label: "Маркетинг", routeName: "owner-marketing", icon: "marketing", order: 6 },
        { label: "Сотрудники", routeName: "owner-staff", icon: "staff", order: 7 },
        { label: "Клиенты", routeName: "owner-clients", icon: "clients", order: 8 },
        { label: "Склад", routeName: "owner-warehouse", icon: "warehouse", order: 9 },
        { label: "Финансы", routeName: "owner-finance", icon: "finance", order: 10 },
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
                    component: () => import("./screens/OrdersHubScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "promotion",
                    redirect: { name: "owner-orders", query: { section: "rules" } },
                },
                {
                    path: "orders/:id",
                    name: "owner-order",
                    component: () => import("./screens/OrderCardScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "menu",
                    name: "owner-menu",
                    component: () => import("./screens/CatalogMenuHubScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "catalog",
                    redirect: { name: "owner-menu" },
                },
                {
                    path: "catalog/products/:id",
                    name: "owner-catalog-product",
                    component: () => import("./screens/ProductEditScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "company",
                    name: "owner-company",
                    component: () => import("./screens/CompanyScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "company/branches/:id",
                    name: "owner-company-branch",
                    component: () => import("./screens/BranchEditScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "company/documents/:id",
                    name: "owner-company-document",
                    component: () => import("./screens/DocumentEditScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "delivery",
                    name: "owner-delivery",
                    component: () => import("./screens/DeliveryScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "marketing",
                    name: "owner-marketing",
                    component: () => import("./screens/MarketingScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "marketing/banners/:id",
                    name: "owner-marketing-banner",
                    component: () => import("./screens/BannerEditScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "marketing/promotions/:id",
                    name: "owner-marketing-promotion",
                    component: () => import("./screens/PromotionEditScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "staff",
                    name: "owner-staff",
                    component: () => import("./screens/StaffScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "staff/:id",
                    name: "owner-staff-employee",
                    component: () => import("./screens/StaffEditScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "clients",
                    name: "owner-clients",
                    component: () => import("./screens/ClientsScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "clients/:id",
                    name: "owner-client",
                    component: () => import("./screens/ClientEditScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "warehouse",
                    name: "owner-warehouse",
                    component: () => import("./screens/WarehouseScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "warehouse/ingredients/:id",
                    name: "owner-warehouse-ingredient",
                    component: () => import("./screens/WarehouseIngredientScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "warehouse/branches/:branchId/items/:id",
                    name: "owner-warehouse-branch-item",
                    component: () => import("./screens/WarehouseBranchItemScreen.vue"),
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
