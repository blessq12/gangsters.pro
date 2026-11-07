<script setup>
import { computed, ref, useSlots } from "vue";
import { statusLabel } from "./opsBoard";

defineProps({
    orders: {
        type: Array,
        required: true,
    },
});

const slots = useSlots();
const openIds = ref(new Set());

const columnCount = computed(() => (slots.actions ? 7 : 6));

function isOpen(id) {
    return openIds.value.has(id);
}

function toggle(id) {
    const next = new Set(openIds.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }
    openIds.value = next;
}
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
                <template
                    v-for="order in orders"
                    :key="order.id"
                >
                    <tr
                        class="cursor-pointer border-b border-neutral-100 align-top hover:bg-neutral-50"
                        :class="isOpen(order.id) ? 'bg-neutral-50' : ''"
                        @click="toggle(order.id)"
                    >
                        <td class="px-3 py-2">
                            <button
                                type="button"
                                class="mr-2 text-neutral-500"
                                :aria-expanded="isOpen(order.id)"
                                :aria-label="isOpen(order.id) ? 'Свернуть заказ' : 'Открыть заказ'"
                                @click.stop="toggle(order.id)"
                            >
                                {{ isOpen(order.id) ? "▾" : "▸" }}
                            </button>
                            {{ order.id }}
                        </td>
                        <td class="px-3 py-2">
                            <div>{{ order.clientName }}</div>
                            <div class="text-xs text-neutral-500">{{ order.phone }}</div>
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
                            @click.stop
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <slot
                                    name="actions"
                                    :order="order"
                                />
                            </div>
                        </td>
                    </tr>
                    <tr
                        v-if="isOpen(order.id)"
                        class="border-b border-neutral-100 bg-neutral-50"
                    >
                        <td
                            :colspan="columnCount"
                            class="px-3 py-3"
                        >
                            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs text-neutral-500">Клиент</dt>
                                    <dd>{{ order.clientName }}</dd>
                                    <dd class="text-neutral-600">{{ order.phone }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-neutral-500">Получение</dt>
                                    <dd>
                                        {{ order.deliveryMethod === "pickup" ? "Самовывоз" : "Курьер" }}
                                    </dd>
                                    <dd class="text-neutral-600">{{ order.address }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-neutral-500">Состав</dt>
                                    <dd
                                        v-for="line in order.lines"
                                        :key="`detail-${order.id}-${line.name}`"
                                    >
                                        {{ line.name }} × {{ line.qty }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-neutral-500">Оплата</dt>
                                    <dd>{{ order.total }} ₽</dd>
                                    <dd class="text-neutral-600">{{ order.payment }}</dd>
                                    <dd class="mt-1">{{ statusLabel(order.status) }}</dd>
                                </div>
                            </dl>
                            <p
                                v-if="order.rejectReason"
                                class="mt-3 text-xs text-red-700"
                            >
                                Причина: {{ order.rejectReason }}
                            </p>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>
