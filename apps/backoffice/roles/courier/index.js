import CourierLayout from "./layouts/CourierLayout.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "courier",
    home: "courier-deliveries",
    nav: [
        { label: "Deliveries", routeName: "courier-deliveries", order: 1 },
        { label: "Shift", routeName: "courier-shift", order: 2 },
    ],
    layout: CourierLayout,
    routes: [
        {
            path: "/courier",
            component: CourierLayout,
            meta: { role: "courier" },
            children: [
                {
                    path: "",
                    redirect: { name: "courier-deliveries" },
                },
                {
                    path: "deliveries",
                    name: "courier-deliveries",
                    component: () => import("./screens/DeliveriesScreen.vue"),
                    meta: { role: "courier" },
                },
                {
                    path: "shift",
                    name: "courier-shift",
                    component: () => import("./screens/ShiftScreen.vue"),
                    meta: { role: "courier" },
                },
            ],
        },
    ],
};

export default module;
