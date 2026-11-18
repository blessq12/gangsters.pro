import RoleOutlet from "../../shell/layouts/RoleOutlet.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "administrator",
    home: "administrator-live-orders",
    nav: [
        { label: "Live orders", routeName: "administrator-live-orders", order: 1 },
        { label: "Manual order", routeName: "administrator-manual-order", order: 2 },
        { label: "Logistics", routeName: "administrator-logistics", order: 3 },
        { label: "Clients", routeName: "administrator-clients", order: 4 },
        { label: "Production", routeName: "administrator-production", order: 5 },
        { label: "Catalog", routeName: "administrator-catalog", order: 6 },
    ],
    routes: [
        {
            path: "/administrator",
            component: RoleOutlet,
            meta: { role: "administrator" },
            children: [
                { path: "", redirect: { name: "administrator-live-orders" } },
                {
                    path: "live-orders",
                    name: "administrator-live-orders",
                    component: () => import("./screens/LiveOrdersScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "manual-order",
                    name: "administrator-manual-order",
                    component: () => import("./screens/ManualOrderScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "logistics",
                    name: "administrator-logistics",
                    component: () => import("./screens/LogisticsScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "clients",
                    name: "administrator-clients",
                    component: () => import("./screens/ClientsScreen.vue"),
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
