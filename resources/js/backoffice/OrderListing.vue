<script setup>
import { statusLabel } from "./opsBoard";

defineProps({
    orders: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="overflow-x-auto rounded border border-neutral-200 bg-white">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                <tr>
                    <th class="px-3 py-2 font-medium">#</th>
                    <th class="px-3 py-2 font-medium">Клиент</th>
                    <th class="px-3 py-2 font-medium">Статус</th>
                    <th class="px-3 py-2 font-medium">Получение</th>
                    <th class="px-3 py-2 font-medium">Состав</th>
                    <th class="px-3 py-2 font-medium">Сумма</th>
                    <th
                        v-if="$slots.actions"
                        class="px-3 py-2 font-medium"
                    />
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="order in orders"
                    :key="order.id"
                    class="border-b border-neutral-100 align-top last:border-0"
                >
                    <td class="px-3 py-2">{{ order.id }}</td>
                    <td class="px-3 py-2">
                        <div>{{ order.clientName }}</div>
                        <div class="text-xs text-neutral-500">{{ order.phone }}</div>
                        <div
                            v-if="order.rejectReason"
                            class="mt-1 text-xs text-red-700"
                        >
                            Причина: {{ order.rejectReason }}
                        </div>
                    </td>
                    <td class="px-3 py-2">{{ statusLabel(order.status) }}</td>
                    <td class="px-3 py-2">
                        {{ order.deliveryMethod === "pickup" ? "Самовывоз" : order.address }}
                    </td>
                    <td class="px-3 py-2 text-neutral-700">
                        <div
                            v-for="line in order.lines"
                            :key="line.name"
                        >
                            {{ line.name }} × {{ line.qty }}
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">
                        <div>{{ order.total }} ₽</div>
                        <div class="text-xs text-neutral-500">{{ order.payment }}</div>
                    </td>
                    <td
                        v-if="$slots.actions"
                        class="px-3 py-2"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <slot
                                name="actions"
                                :order="order"
                            />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
