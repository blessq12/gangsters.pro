/**
 * Backoffice SPA routes.
 * Base path: /backoffice
 */

export const routeRecords = [
    {
        path: "/",
        redirect: { name: "administrator-order" },
    },
    {
        path: "/courier",
        component: () => import("../courier/layouts/CourierLayout.vue"),
        meta: { role: "courier" },
        children: [
            {
                path: "",
                redirect: { name: "courier-delivery" },
            },
            {
                path: "delivery",
                name: "courier-delivery",
                component: () => import("../courier/pages/DeliveryPage.vue"),
            },
        ],
    },
    {
        path: "/production",
        component: () => import("../production/layouts/ProductionLayout.vue"),
        meta: { role: "production" },
        children: [
            {
                path: "",
                redirect: { name: "production-production" },
            },
            {
                path: "catalog",
                name: "production-catalog",
                component: () => import("../production/pages/CatalogPage.vue"),
            },
            {
                path: "production",
                name: "production-production",
                component: () => import("../production/pages/ProductionPage.vue"),
            },
            {
                path: "order",
                name: "production-order",
                component: () => import("../production/pages/OrderPage.vue"),
            },
        ],
    },
    {
        path: "/manager",
        component: () => import("../manager/layouts/ManagerLayout.vue"),
        meta: { role: "manager" },
        children: [
            {
                path: "",
                redirect: { name: "manager-order" },
            },
            {
                path: "order",
                name: "manager-order",
                component: () => import("../manager/pages/OrderPage.vue"),
            },
            {
                path: "catalog",
                name: "manager-catalog",
                component: () => import("../manager/pages/CatalogPage.vue"),
            },
            {
                path: "crm",
                name: "manager-crm",
                component: () => import("../manager/pages/CrmPage.vue"),
            },
            {
                path: "production",
                name: "manager-production",
                component: () => import("../manager/pages/ProductionPage.vue"),
            },
            {
                path: "delivery",
                name: "manager-delivery",
                component: () => import("../manager/pages/DeliveryPage.vue"),
            },
        ],
    },
    {
        path: "/administrator",
        component: () => import("../administrator/layouts/AdministratorLayout.vue"),
        meta: { role: "administrator" },
        children: [
            {
                path: "",
                redirect: { name: "administrator-order" },
            },
            {
                path: "catalog",
                name: "administrator-catalog",
                component: () => import("../administrator/pages/CatalogPage.vue"),
            },
            {
                path: "content",
                name: "administrator-content",
                component: () => import("../administrator/pages/ContentPage.vue"),
            },
            {
                path: "crm",
                name: "administrator-crm",
                component: () => import("../administrator/pages/CrmPage.vue"),
            },
            {
                path: "delivery",
                name: "administrator-delivery",
                component: () => import("../administrator/pages/DeliveryPage.vue"),
            },
            {
                path: "order",
                name: "administrator-order",
                component: () => import("../administrator/pages/OrderPage.vue"),
            },
            {
                path: "clients",
                name: "administrator-clients",
                component: () => import("../administrator/pages/ClientsPage.vue"),
            },
            {
                path: "logistics",
                name: "administrator-logistics",
                component: () => import("../administrator/pages/LogisticsPage.vue"),
            },
            {
                path: "production",
                name: "administrator-production",
                component: () => import("../administrator/pages/ProductionPage.vue"),
            },
            {
                path: "staff",
                name: "administrator-staff",
                component: () => import("../administrator/pages/StaffPage.vue"),
            },
            {
                path: "warehouse",
                name: "administrator-warehouse",
                component: () => import("../administrator/pages/WarehousePage.vue"),
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        name: "backoffice-not-found",
        component: () => import("../shell/pages/NotFoundPage.vue"),
    },
];
