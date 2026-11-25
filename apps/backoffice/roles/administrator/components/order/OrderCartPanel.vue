<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import UiButton from "../../../../shell/components/ui/UiButton.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";

const props = defineProps({
    /** @type {Array<{ key: string, id: number, kind: string, name: string, quantity: number }>} */
    cart: { type: Array, default: () => [] },
    freeComplementLines: { type: Array, default: () => [] },
    giftLines: { type: Array, default: () => [] },
    /** Плоский список меню для добавления */
    menuItems: {
        type: Array,
        default: () => [],
    },
    menuLoading: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    emptyText: { type: String, default: "Добавь позиции в заказ." },
});

const emit = defineEmits(["change-qty", "remove", "add"]);

const pickerOpen = ref(false);
const searchQuery = ref("");
const rootEl = ref(/** @type {HTMLElement|null} */ (null));

const filteredItems = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    const items = Array.isArray(props.menuItems) ? props.menuItems : [];
    if (!q) {
        return items.slice(0, 40);
    }
    return items
        .filter((item) => String(item.name || "").toLowerCase().includes(q))
        .slice(0, 40);
});

function openPicker() {
    if (props.readonly) {
        return;
    }
    pickerOpen.value = true;
    searchQuery.value = "";
}

function closePicker() {
    pickerOpen.value = false;
    searchQuery.value = "";
}

/**
 * @param {Record<string, unknown>} item
 */
function pickItem(item) {
    emit("add", {
        id: Number(item.id),
        kind: String(item.kind || "product"),
        name: String(item.name || `Товар #${item.id}`),
    });
    closePicker();
}

/**
 * @param {MouseEvent} event
 */
function onDocumentClick(event) {
    if (!pickerOpen.value || !rootEl.value) {
        return;
    }
    const target = event.target;
    if (target instanceof Node && !rootEl.value.contains(target)) {
        closePicker();
    }
}

watch(
    () => props.readonly,
    (value) => {
        if (value) {
            closePicker();
        }
    },
);

onMounted(() => {
    document.addEventListener("click", onDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", onDocumentClick);
});
</script>

<template>
    <div
        ref="rootEl"
        class="space-y-3"
    >
        <div
            v-if="!readonly"
            class="relative"
        >
            <UiButton
                class="!h-11 !bg-white/10 !text-bo-fg"
                @click.stop="openPicker"
            >
                Добавить товар
            </UiButton>

            <div
                v-if="pickerOpen"
                class="absolute left-0 right-0 z-30 mt-2 border border-bo-border bg-bo-elevated shadow-bo-lg"
                @click.stop
            >
                <div class="border-b border-bo-border p-3">
                    <UiInput
                        v-model="searchQuery"
                        type="search"
                        placeholder="Поиск по названию"
                    />
                </div>
                <p
                    v-if="menuLoading"
                    class="px-3 py-4 text-sm text-bo-muted"
                >
                    Загрузка меню…
                </p>
                <p
                    v-else-if="filteredItems.length === 0"
                    class="px-3 py-4 text-sm text-bo-muted"
                >
                    Ничего не найдено
                </p>
                <ul
                    v-else
                    class="max-h-64 overflow-y-auto"
                >
                    <li
                        v-for="item in filteredItems"
                        :key="item.key || `${item.kind}:${item.id}`"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left text-sm hover:bg-white/[0.04]"
                            @click="pickItem(item)"
                        >
                            <span class="min-w-0 truncate text-bo-fg">
                                {{ item.name }}
                                <span class="ml-2 text-xs text-bo-muted">
                                    {{ item.kind === 'set' ? 'набор' : 'товар' }}
                                </span>
                            </span>
                            <span class="shrink-0 text-bo-muted">
                                {{ item.price_rubles != null ? `${item.price_rubles} ₽` : '' }}
                            </span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <p
            v-if="cart.length === 0 && freeComplementLines.length === 0 && giftLines.length === 0"
            class="text-sm text-bo-muted"
        >
            {{ emptyText }}
        </p>
        <div
            v-else
            class="max-h-[36rem] space-y-2 overflow-y-auto"
        >
            <div
                v-for="line in cart"
                :key="line.key"
                class="flex items-center justify-between gap-2 border border-bo-border bg-bo-field px-3 py-2"
            >
                <div class="min-w-0">
                    <div class="truncate text-sm text-bo-fg">
                        {{ line.name }}
                    </div>
                    <div class="text-xs text-bo-muted">
                        {{ line.kind === 'set' ? 'Набор' : 'Товар' }}
                    </div>
                </div>
                <div
                    v-if="!readonly"
                    class="flex shrink-0 items-center gap-1"
                >
                    <button
                        type="button"
                        class="h-8 w-8 border border-bo-border text-bo-fg"
                        @click="$emit('change-qty', line.key, -1)"
                    >
                        −
                    </button>
                    <span class="w-6 text-center text-sm">{{ line.quantity }}</span>
                    <button
                        type="button"
                        class="h-8 w-8 border border-bo-border text-bo-fg"
                        @click="$emit('change-qty', line.key, 1)"
                    >
                        +
                    </button>
                    <button
                        type="button"
                        class="ml-1 h-8 px-2 text-xs text-bo-muted"
                        @click="$emit('remove', line.key)"
                    >
                        ×
                    </button>
                </div>
                <span
                    v-else
                    class="shrink-0 text-sm text-bo-fg"
                >× {{ line.quantity }}</span>
            </div>

            <template v-if="freeComplementLines.length > 0">
                <p class="pt-1 text-[11px] font-medium uppercase tracking-wide text-bo-muted">
                    Бесплатный комплект
                </p>
                <div
                    v-for="(line, idx) in freeComplementLines"
                    :key="`free-c-${line.product_id}-${idx}`"
                    class="flex items-center justify-between gap-2 border border-dashed border-bo-border bg-white/[0.02] px-3 py-2"
                >
                    <div class="min-w-0">
                        <div class="truncate text-sm text-bo-fg">
                            {{ line.product_name || line.name }}
                        </div>
                        <div class="text-xs text-bo-muted">
                            бесплатно × {{ line.quantity }}
                        </div>
                    </div>
                    <span class="shrink-0 text-sm text-bo-muted">0 ₽</span>
                </div>
            </template>

            <template v-if="giftLines.length > 0">
                <p class="pt-1 text-[11px] font-medium uppercase tracking-wide text-bo-muted">
                    Подарок
                </p>
                <div
                    v-for="(line, idx) in giftLines"
                    :key="`gift-${line.product_id}-${idx}`"
                    class="flex items-center justify-between gap-2 border border-dashed border-bo-border bg-white/[0.02] px-3 py-2"
                >
                    <div class="truncate text-sm text-bo-fg">
                        {{ line.product_name || line.name }} × {{ line.quantity }}
                    </div>
                    <span class="shrink-0 text-sm text-bo-muted">0 ₽</span>
                </div>
            </template>
        </div>
    </div>
</template>
