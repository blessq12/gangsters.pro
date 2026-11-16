import RoleOutlet from "../../shell/layouts/RoleOutlet.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "administrator",
    home: "administrator-live-orders",
    nav: [
        { label: "Live orders", routeName: "administrator-live-orders", order: 1 },
        { label: "Dispatch", routeName: "administrator-dispatch", order: 2 },
        { label: "Clients", routeName: "administrator-clients", order: 3 },
        { label: "Menu ops", routeName: "administrator-menu-ops", order: 4 },
        { label: "Support", routeName: "administrator-support", order: 5 },
        { label: "Shift board", routeName: "administrator-shift-board", order: 6 },
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
                    path: "dispatch",
                    name: "administrator-dispatch",
                    component: () => import("./screens/DispatchScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "clients",
                    name: "administrator-clients",
                    component: () => import("./screens/ClientsScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "menu-ops",
                    name: "administrator-menu-ops",
                    component: () => import("./screens/MenuOpsScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "support",
                    name: "administrator-support",
                    component: () => import("./screens/SupportScreen.vue"),
                    meta: { role: "administrator" },
                },
                {
                    path: "shift-board",
                    name: "administrator-shift-board",
                    component: () => import("./screens/ShiftBoardScreen.vue"),
                    meta: { role: "administrator" },
                },
            ],
        },
    ],
};

export default module;
