import { defineStore } from "pinia";
import { ref } from "vue";
import { backofficeRequest, formatDate, formatDateTime } from "./api";

function mapAddress(address) {
    return {
        title: address.title ?? "",
        type: address.type ?? null,
        street: address.street ?? "",
        house: address.house ?? "",
        entrance: address.entrance ?? "",
        floor: address.floor ?? "",
        apartment: address.apartment ?? "",
        comment: address.comment ?? "",
        isDefault: Boolean(address.is_default),
    };
}

function mapClient(row) {
    return {
        id: row.id,
        name: row.name ?? "",
        phone: row.phone ?? "",
        email: row.email ?? null,
        birthDate: formatDate(row.birth_date),
        consentPersonalData: Boolean(row.consent_personal_data),
        consentMarketing: Boolean(row.consent_marketing),
        createdAt: formatDateTime(row.created_at),
        addresses: Array.isArray(row.addresses) ? row.addresses.map(mapAddress) : [],
        favoriteProductIds: Array.isArray(row.favorite_product_ids) ? row.favorite_product_ids : [],
        orderHistory: Array.isArray(row.order_history)
            ? row.order_history.map((entry) => ({
                id: entry.id,
                placedAt: formatDateTime(entry.placed_at),
                summary: entry.summary ?? "",
            }))
            : [],
    };
}

export const useClients = defineStore("clients", () => {
    const clients = ref([]);
    const clientsError = ref("");
    const clientsReady = ref(false);
    const clientsPage = ref(1);
    const clientsPerPage = 20;
    const clientsTotal = ref(0);

    async function loadClients(page = clientsPage.value) {
        const params = new URLSearchParams({
            page: String(page),
            per_page: String(clientsPerPage),
        });
        try {
            const payload = await backofficeRequest("GET", `/api/backoffice/clients?${params}`);
            clients.value = Array.isArray(payload.clients) ? payload.clients.map(mapClient) : [];
            clientsPage.value = Number(payload.page) || 1;
            clientsTotal.value = Number(payload.total) || 0;
            clientsError.value = "";
        } catch (error) {
            clientsError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
        } finally {
            clientsReady.value = true;
        }
    }

    loadClients(1);

    return {
        clients,
        clientsError,
        clientsReady,
        clientsPage,
        clientsPerPage,
        clientsTotal,
        loadClients,
    };
});
