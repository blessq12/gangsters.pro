<script setup>
import { computed, ref } from "vue";
import { storeToRefs } from "pinia";
import { useCatalog } from "../../catalog";
import { useOpsBoard } from "../../opsBoard";

const emit = defineEmits(["created"]);

const catalog = useCatalog();
const board = useOpsBoard();
const { categories, products, sets, catalogError, catalogReady } = storeToRefs(catalog);
const { ordersError } = storeToRefs(board);

const query = ref("");
const categoryId = ref(null);
const cartOpen = ref(false);
const lines = ref([]);
const submitting = ref(false);
const clientName = ref("");
const phone = ref("");
const deliveryMethod = ref("pickup");
const address = ref("");
const payment = ref("наличные");

const sellableProducts = computed(() =>
    products.value.filter((product) => product.status === "active" && !product.isSystem),
);

const sellableSets = computed(() =>
    sets.value
        .filter((set) => set.status === "active")
        .map((set) => ({
            id: set.id,
            name: set.name,
            price: set.price,
            categoryId: "sets",
        })),
);

const groups = computed(() => {
    const rows = [];
    for (const category of categories.value) {
        if (!category.isActive) {
            continue;
        }
        const items = sellableProducts.value.filter((product) => product.categoryId === category.id);
        if (items.length > 0) {
            rows.push({ id: category.id, name: category.name, items });
        }
    }
    const loose = sellableProducts.value.filter((product) => product.categoryId == null);
    if (loose.length > 0) {
        rows.push({ id: "other", name: "Прочее", items: loose });
    }
    if (sellableSets.value.length > 0) {
        rows.push({ id: "sets", name: "Наборы", items: sellableSets.value });
    }
    return rows;
});

const activeCategoryId = computed(() => {
    if (groups.value.some((group) => group.id === categoryId.value)) {
        return categoryId.value;
    }
    return groups.value[0]?.id ?? null;
});

const visibleItems = computed(() => {
    const needle = query.value.trim().toLowerCase();
    const source = needle === ""
        ? (groups.value.find((group) => group.id === activeCategoryId.value)?.items ?? [])
        : groups.value.flatMap((group) => group.items);
    if (needle === "") {
        return source;
    }
    return source.filter((item) => item.name.toLowerCase().includes(needle));
});

const total = computed(() =>
    lines.value.reduce((sum, line) => sum + line.price * line.qty, 0),
);

const pieceCount = computed(() =>
    lines.value.reduce((sum, line) => sum + line.qty, 0),
);

function qtyOf(productId) {
    return lines.value.find((line) => line.productId === productId)?.qty ?? 0;
}

function addItem(item) {
    const existing = lines.value.find((line) => line.productId === item.id);
    if (existing) {
        existing.qty += 1;
        return;
    }
    lines.value.push({
        productId: item.id,
        name: item.name,
        price: item.price,
        qty: 1,
    });
}

function changeQty(productId, delta) {
    const line = lines.value.find((entry) => entry.productId === productId);
    if (!line) {
        return;
    }
    line.qty += delta;
    if (line.qty < 1) {
        lines.value = lines.value.filter((entry) => entry.productId !== productId);
    }
}

function clearTicket() {
    lines.value = [];
}

function resetForm() {
    lines.value = [];
    clientName.value = "";
    phone.value = "";
    deliveryMethod.value = "pickup";
    address.value = "";
    payment.value = "наличные";
    cartOpen.value = false;
}

async function submitOrder() {
    if (submitting.value || lines.value.length === 0) {
        return;
    }
    submitting.value = true;
    const id = await board.addOrder({
        clientName: clientName.value,
        phone: phone.value,
        deliveryMethod: deliveryMethod.value,
        address: address.value,
        payment: payment.value,
        lines: lines.value.map((line) => ({
            productId: line.productId,
            qty: line.qty,
        })),
    });
    submitting.value = false;
    if (id == null) {
        return;
    }
    resetForm();
    emit("created");
}
</script>

<template>
    <div class="flex h-[calc(100dvh-11rem)] min-h-[28rem] flex-col">
        <div class="min-h-0 flex-1 overflow-y-auto">
            <p
                v-if="catalogError"
                class="text-sm text-red-700"
            >
                {{ catalogError }}
            </p>
            <p
                v-else-if="!catalogReady"
                class="text-sm text-neutral-500"
            >
                Загрузка…
            </p>
            <div
                v-else
                class="flex items-start gap-2"
            >
                <div class="flex w-44 shrink-0 flex-col gap-1">
                    <button
                        v-for="group in groups"
                        :key="group.id"
                        type="button"
                        class="rounded px-3 py-2 text-left text-base"
                        :class="activeCategoryId === group.id && query.trim() === '' ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700'"
                        @click="categoryId = group.id; query = ''"
                    >
                        {{ group.name }}
                    </button>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex gap-2">
                        <input
                            v-model="query"
                            type="search"
                            placeholder="Поиск"
                            class="min-w-0 flex-1 rounded border border-neutral-300 bg-white px-3 py-2 text-base"
                        >
                        <button
                            type="button"
                            class="shrink-0 rounded bg-neutral-900 px-3 py-2 text-base text-white"
                            @click="cartOpen = true"
                        >
                            Корзина<span v-if="pieceCount"> · {{ pieceCount }} · {{ total }} ₽</span>
                        </button>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4">
                        <button
                            v-for="item in visibleItems"
                            :key="item.id"
                            type="button"
                            class="relative rounded border border-neutral-200 bg-white p-3 text-left hover:border-neutral-400"
                            @click="addItem(item)"
                        >
                            <span
                                v-if="qtyOf(item.id) > 0"
                                class="absolute right-2 top-2 rounded bg-neutral-900 px-1.5 text-sm text-white"
                            >
                                {{ qtyOf(item.id) }}
                            </span>
                            <span class="block pr-6 text-base font-medium">{{ item.name }}</span>
                            <span class="mt-1 block text-base text-neutral-500">{{ item.price }} ₽</span>
                        </button>
                        <p
                            v-if="visibleItems.length === 0"
                            class="col-span-full text-base text-neutral-500"
                        >
                            Ничего не нашлось.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="fixed inset-0 z-40 bg-black/30 transition-opacity"
            :class="cartOpen ? 'opacity-100' : 'pointer-events-none opacity-0'"
            @click="cartOpen = false"
        />
        <aside
            aria-label="Корзина"
            class="fixed inset-y-0 right-0 z-50 flex w-96 max-w-full flex-col bg-white shadow-xl transition-transform duration-200"
            :class="cartOpen ? 'translate-x-0' : 'translate-x-full'"
        >
            <form
                class="flex min-h-0 flex-1 flex-col"
                @submit.prevent="submitOrder"
            >
                <div class="flex items-center justify-between border-b border-neutral-200 px-4 py-3">
                    <h2 class="text-base font-medium">Корзина</h2>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            class="text-base text-neutral-500"
                            @click="clearTicket"
                        >
                            Очистить
                        </button>
                        <button
                            type="button"
                            class="text-base text-neutral-500"
                            @click="cartOpen = false"
                        >
                            Закрыть
                        </button>
                    </div>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto">
                    <p
                        v-if="lines.length === 0"
                        class="px-4 py-3 text-base text-neutral-500"
                    >
                        Корзина пуста.
                    </p>
                    <div
                        v-for="line in lines"
                        :key="line.productId"
                        class="flex items-center gap-3 border-b border-neutral-100 px-4 py-3 text-base"
                    >
                        <div class="min-w-0 flex-1">
                            <p>{{ line.name }}</p>
                            <p class="text-neutral-500">{{ line.price * line.qty }} ₽</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                class="h-8 w-8 rounded border border-neutral-300"
                                @click="changeQty(line.productId, -1)"
                            >
                                −
                            </button>
                            <span class="w-6 text-center">{{ line.qty }}</span>
                            <button
                                type="button"
                                class="h-8 w-8 rounded border border-neutral-300"
                                @click="changeQty(line.productId, 1)"
                            >
                                +
                            </button>
                        </div>
                    </div>
                </div>
                <div class="space-y-2 border-t border-neutral-200 px-4 py-3">
                    <input
                        v-model="clientName"
                        required
                        type="text"
                        placeholder="Клиент"
                        class="w-full rounded border border-neutral-300 px-3 py-2 text-base"
                    >
                    <input
                        v-model="phone"
                        required
                        type="text"
                        placeholder="Телефон"
                        class="w-full rounded border border-neutral-300 px-3 py-2 text-base"
                    >
                    <select
                        v-model="deliveryMethod"
                        class="w-full rounded border border-neutral-300 px-3 py-2 text-base"
                    >
                        <option value="pickup">Самовывоз</option>
                        <option value="courier">Курьер</option>
                    </select>
                    <input
                        v-if="deliveryMethod === 'courier'"
                        v-model="address"
                        required
                        type="text"
                        placeholder="Адрес"
                        class="w-full rounded border border-neutral-300 px-3 py-2 text-base"
                    >
                    <select
                        v-model="payment"
                        class="w-full rounded border border-neutral-300 px-3 py-2 text-base"
                    >
                        <option value="наличные">Наличные</option>
                        <option value="карта">Карта</option>
                        <option value="карта курьеру">Карта курьеру</option>
                    </select>
                    <p
                        v-if="ordersError"
                        class="text-base text-red-700"
                    >
                        {{ ordersError }}
                    </p>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-base text-neutral-500">Итого</span>
                        <span class="text-base font-medium">{{ total }} ₽</span>
                    </div>
                    <button
                        type="submit"
                        class="w-full rounded bg-neutral-900 py-2.5 text-base text-white disabled:opacity-40"
                        :disabled="submitting || lines.length === 0"
                    >
                        Создать заказ
                    </button>
                </div>
            </form>
        </aside>
    </div>
</template>
