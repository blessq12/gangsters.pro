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
                path: "order/:id",
                name: "production-order",
                component: () => import("../production/pages/KitchenOrderPage.vue"),
            },
        ],
    },
    {
        path: "/manager/:pathMatch(.*)*",
        redirect: { name: "owner-order" },
    },
    {
        path: "/owner",
        component: () => import("../owner/layouts/OwnerLayout.vue"),
        meta: { role: "owner" },
        children: [
            {
                path: "",
                redirect: { name: "owner-order" },
            },
            {
                path: "order",
                name: "owner-order",
                component: () => import("../owner/pages/OrderPage.vue"),
            },
            {
                path: "catalog",
                name: "owner-catalog",
                component: () => import("../owner/pages/CatalogPage.vue"),
            },
            {
                path: "marketing",
                name: "owner-marketing",
                component: () => import("../owner/pages/MarketingPage.vue"),
            },
            {
                path: "delivery",
                name: "owner-delivery",
                component: () => import("../owner/pages/DeliveryPage.vue"),
            },
            {
                path: "promotion-policy",
                name: "owner-promotion-policy",
                component: () => import("../owner/pages/PromotionPolicyPage.vue"),
            },
            {
                path: "clients",
                name: "owner-clients",
                component: () => import("../owner/pages/ClientsPage.vue"),
            },
            {
                path: "company",
                name: "owner-company",
                component: () => import("../owner/pages/CompanyPage.vue"),
            },
            {
                path: "documents",
                name: "owner-documents",
                component: () => import("../owner/pages/DocumentsPage.vue"),
            },
            {
                path: "operators",
                name: "owner-operators",
                component: () => import("../owner/pages/OperatorsPage.vue"),
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
