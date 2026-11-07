<script setup>
import { ref } from "vue";

defineProps({
    clients: {
        type: Array,
        required: true,
    },
});

const openIds = ref(new Set());
const detailTab = ref("profile");

const detailTabs = [
    { id: "profile", label: "Профиль" },
    { id: "addresses", label: "Адреса" },
    { id: "favorites", label: "Избранное" },
    { id: "history", label: "История заказов" },
];

function isOpen(id) {
    return openIds.value.has(id);
}

function toggle(id) {
    const next = new Set(openIds.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
        detailTab.value = "profile";
    }
    openIds.value = next;
}

function addressLine(address) {
    const parts = [address.street, address.house].filter(Boolean);
    if (address.apartment) {
        parts.push(`кв. ${address.apartment}`);
    }
    return parts.join(", ");
}
</script>

<template>
    <div class="overflow-x-auto rounded border border-neutral-200 bg-white">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                <tr>
                    <th class="px-3 py-2 font-medium">ID</th>
                    <th class="px-3 py-2 font-medium">Имя</th>
                    <th class="px-3 py-2 font-medium">Телефон</th>
                    <th class="px-3 py-2 font-medium">Email</th>
                    <th class="px-3 py-2 font-medium">Маркетинг</th>
                    <th class="px-3 py-2 font-medium">Адреса</th>
                    <th class="px-3 py-2 font-medium">Регистрация</th>
                </tr>
            </thead>
            <tbody>
                <template
                    v-for="client in clients"
                    :key="client.id"
                >
                    <tr
                        class="cursor-pointer border-b border-neutral-100 align-top hover:bg-neutral-50"
                        :class="isOpen(client.id) ? 'bg-neutral-50' : ''"
                        @click="toggle(client.id)"
                    >
                        <td class="px-3 py-2">
                            <button
                                type="button"
                                class="mr-2 text-neutral-500"
                                :aria-expanded="isOpen(client.id)"
                                @click.stop="toggle(client.id)"
                            >
                                {{ isOpen(client.id) ? "▾" : "▸" }}
                            </button>
                            {{ client.id }}
                        </td>
                        <td class="px-3 py-2">{{ client.name }}</td>
                        <td class="px-3 py-2">{{ client.phone }}</td>
                        <td class="px-3 py-2">{{ client.email || "—" }}</td>
                        <td class="px-3 py-2">{{ client.consentMarketing ? "Да" : "Нет" }}</td>
                        <td class="px-3 py-2">{{ client.addresses.length }}</td>
                        <td class="px-3 py-2">{{ client.createdAt }}</td>
                    </tr>
                    <tr
                        v-if="isOpen(client.id)"
                        class="border-b border-neutral-100 bg-neutral-50"
                    >
                        <td
                            colspan="7"
                            class="px-3 py-3"
                        >
                            <div class="mb-3 flex flex-wrap gap-1">
                                <button
                                    v-for="item in detailTabs"
                                    :key="item.id"
                                    type="button"
                                    class="rounded px-2 py-1 text-xs"
                                    :class="detailTab === item.id ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
                                    @click="detailTab = item.id"
                                >
                                    {{ item.label }}
                                </button>
                            </div>
                            <dl
                                v-if="detailTab === 'profile'"
                                class="grid gap-3 text-sm sm:grid-cols-2"
                            >
                                <div>
                                    <dt class="text-xs text-neutral-500">Имя</dt>
                                    <dd>{{ client.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-neutral-500">Телефон</dt>
                                    <dd>{{ client.phone }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-neutral-500">Email</dt>
                                    <dd>{{ client.email || "—" }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-neutral-500">Дата рождения</dt>
                                    <dd>{{ client.birthDate || "—" }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-neutral-500">Согласие на ПДн</dt>
                                    <dd>{{ client.consentPersonalData ? "Да" : "Нет" }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-neutral-500">Согласие на маркетинг</dt>
                                    <dd>{{ client.consentMarketing ? "Да" : "Нет" }}</dd>
                                </div>
                            </dl>
                            <div v-else-if="detailTab === 'addresses'">
                                <p
                                    v-if="client.addresses.length === 0"
                                    class="text-sm"
                                >
                                    Адресов нет.
                                </p>
                                <ul
                                    v-else
                                    class="space-y-2 text-sm"
                                >
                                    <li
                                        v-for="address in client.addresses"
                                        :key="`${address.street}-${address.house}`"
                                    >
                                        <p>{{ address.title || "Адрес" }}<span v-if="address.isDefault"> · основной</span></p>
                                        <p class="text-neutral-600">{{ addressLine(address) }}</p>
                                        <p
                                            v-if="address.entrance || address.floor"
                                            class="text-neutral-500"
                                        >
                                            Подъезд {{ address.entrance || "—" }}, этаж {{ address.floor || "—" }}
                                        </p>
                                        <p
                                            v-if="address.comment"
                                            class="text-neutral-500"
                                        >
                                            {{ address.comment }}
                                        </p>
                                    </li>
                                </ul>
                            </div>
                            <p
                                v-else-if="detailTab === 'favorites'"
                                class="text-sm"
                            >
                                {{ client.favoriteProductIds.length ? client.favoriteProductIds.join(", ") : "—" }}
                            </p>
                            <div v-else>
                                <p
                                    v-if="client.orderHistory.length === 0"
                                    class="text-sm"
                                >
                                    История пуста.
                                </p>
                                <table
                                    v-else
                                    class="w-full text-left text-sm"
                                >
                                    <thead class="text-xs text-neutral-500">
                                        <tr>
                                            <th class="py-1 pr-3 font-medium">ID</th>
                                            <th class="py-1 pr-3 font-medium">Оформлен</th>
                                            <th class="py-1 font-medium">Слепок</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="entry in client.orderHistory"
                                            :key="entry.id"
                                        >
                                            <td class="py-1 pr-3">{{ entry.id }}</td>
                                            <td class="py-1 pr-3">{{ entry.placedAt }}</td>
                                            <td class="py-1">{{ entry.summary }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>
