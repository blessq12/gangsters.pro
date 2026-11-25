import RoleOutlet from "../../shell/layouts/RoleOutlet.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "kitchen",
    home: "production-queue",
    nav: [
        { label: "Очередь", routeName: "production-queue", icon: "production", order: 0 },
        { label: "Склад", routeName: "production-warehouse", icon: "warehouse", order: 1 },
    ],
    routes: [
        {
            path: "/production",
            component: RoleOutlet,
            meta: { role: "kitchen" },
            children: [
                {
                    path: "",
                    redirect: { name: "production-queue" },
                },
                {
                    path: "queue",
                    name: "production-queue",
                    component: () => import("./screens/QueueScreen.vue"),
                    meta: { role: "kitchen" },
                },
                {
                    path: "ticket/:orderId",
                    name: "production-ticket",
                    component: () => import("./screens/TicketScreen.vue"),
                    meta: { role: "kitchen" },
                },
                {
                    path: "warehouse",
                    name: "production-warehouse",
                    component: () => import("./screens/WarehouseReadScreen.vue"),
                    meta: { role: "kitchen" },
                },
                {
                    path: "warehouse/items/:branchId/:id",
                    name: "production-warehouse-item",
                    component: () => import("./screens/WarehouseBranchItemReadScreen.vue"),
                    meta: { role: "kitchen" },
                },
            ],
        },
    ],
};

export default module;
