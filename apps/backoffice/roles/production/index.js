import ProductionLayout from "./layouts/ProductionLayout.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "kitchen",
    home: "production-queue",
    nav: [],
    layout: ProductionLayout,
    routes: [
        {
            path: "/production",
            component: ProductionLayout,
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
            ],
        },
    ],
};

export default module;
