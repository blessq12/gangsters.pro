import CourierLayout from "./layouts/CourierLayout.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "courier",
    home: "courier-deliveries",
    nav: [
        { label: "Маршрутные листы", routeName: "courier-deliveries", icon: "delivery", order: 1 },
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
                    path: "deliveries/:id",
                    name: "courier-route-sheet",
                    component: () => import("./screens/CourierRouteSheetScreen.vue"),
                    meta: { role: "courier" },
                },
            ],
        },
    ],
};

export default module;
