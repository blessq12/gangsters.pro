import RoleOutlet from "../../shell/layouts/RoleOutlet.vue";

/** @type {import('../../shell/roles/contract.js').RoleModule} */
const module = {
    role: "courier",
    home: "courier-deliveries",
    nav: [{ label: "Листы", routeName: "courier-deliveries", icon: "logistics", order: 0 }],
    routes: [
        {
            path: "/courier",
            component: RoleOutlet,
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
