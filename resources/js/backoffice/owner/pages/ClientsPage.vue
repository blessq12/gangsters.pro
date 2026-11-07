<script setup>
import { storeToRefs } from "pinia";
import ClientListing from "../../ClientListing.vue";
import Pager from "../../Pager.vue";
import { useClients } from "../../clients";

const clientsStore = useClients();
const { clients, clientsError, clientsReady, clientsPage, clientsTotal } = storeToRefs(clientsStore);
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Клиенты</h1>
        <p
            v-if="clientsError"
            class="text-sm text-red-700"
        >
            {{ clientsError }}
        </p>
        <p
            v-else-if="!clientsReady"
            class="text-sm text-neutral-500"
        >
            Загрузка…
        </p>
        <p
            v-else-if="clients.length === 0"
            class="text-sm text-neutral-500"
        >
            Клиентов нет.
        </p>
        <ClientListing
            v-else
            :clients="clients"
        />
        <Pager
            v-if="clientsReady"
            :page="clientsPage"
            :total="clientsTotal"
            :per-page="clientsStore.clientsPerPage"
            @update:page="clientsStore.loadClients"
        />
    </section>
</template>
