import ProductionLayout from "./layouts/ProductionLayout.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "kitchen",
    home: "production-queue",
    nav: [
        { label: "Queue", routeName: "production-queue", order: 1 },
        { label: "Ticket", routeName: "production-ticket", order: 2 },
        { label: "Station", routeName: "production-station", order: 3 },
    ],
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
                    path: "ticket",
                    name: "production-ticket",
                    component: () => import("./screens/TicketScreen.vue"),
                    meta: { role: "kitchen" },
                },
                {
                    path: "station",
                    name: "production-station",
                    component: () => import("./screens/StationScreen.vue"),
                    meta: { role: "kitchen" },
                },
            ],
        },
    ],
};

export default module;
