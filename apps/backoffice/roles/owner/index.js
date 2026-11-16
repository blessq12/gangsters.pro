import RoleOutlet from "../../shell/layouts/RoleOutlet.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "owner",
    home: "owner-dashboard",
    nav: [
        { label: "Dashboard", routeName: "owner-dashboard", order: 1 },
        { label: "Orders", routeName: "owner-orders", order: 2 },
        { label: "Catalog", routeName: "owner-catalog", order: 3 },
        { label: "Marketing", routeName: "owner-marketing", order: 4 },
        { label: "Finance", routeName: "owner-finance", order: 5 },
        { label: "Staff", routeName: "owner-staff", order: 6 },
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
                    path: "marketing",
                    name: "owner-marketing",
                    component: () => import("./screens/MarketingScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "finance",
                    name: "owner-finance",
                    component: () => import("./screens/FinanceScreen.vue"),
                    meta: { role: "owner" },
                },
                {
                    path: "staff",
                    name: "owner-staff",
                    component: () => import("./screens/StaffScreen.vue"),
                    meta: { role: "owner" },
                },
            ],
        },
    ],
};

export default module;
