<script setup>
import { computed } from "vue";

const props = defineProps({
    /** @type {import('vue').PropType<Record<string, unknown>>} */
    item: { type: Object, required: true },
    /** true — компактная строка для дропдауна */
    dense: { type: Boolean, default: false },
});

const emit = defineEmits(["select"]);

const kindLabel = computed(() => (props.item?.kind === "set" ? "Набор" : "Товар"));

const priceLabel = computed(() => {
    const price = props.item?.price_rubles;
    return price != null && price !== "" ? `${price} ₽` : "";
});

const imageUrl = computed(() => String(props.item?.image_url || "").trim());

const objectPosition = computed(() => {
    const x = Number(props.item?.image_focus_x);
    const y = Number(props.item?.image_focus_y);
    const fx = Number.isFinite(x) ? x : 50;
    const fy = Number.isFinite(y) ? y : 50;
    return `${fx}% ${fy}%`;
});

function onSelect() {
    emit("select", props.item);
}
</script>

<template>
    <button
        v-if="dense"
        type="button"
        class="flex w-full items-center justify-between gap-3 px-3 py-2.5 text-left text-sm hover:bg-white/[0.04]"
        @click="onSelect"
    >
        <span class="min-w-0 truncate text-bo-fg">
            {{ item.name }}
            <span class="ml-2 text-xs text-bo-muted">
                {{ item.kind === "set" ? "набор" : "товар" }}
            </span>
        </span>
        <span class="shrink-0 text-bo-muted">
            {{ priceLabel }}
        </span>
    </button>

    <button
        v-else
        type="button"
        class="group flex h-full flex-col overflow-hidden border border-bo-border bg-bo-field text-left transition-colors hover:border-bo-border-strong hover:bg-white/[0.04]"
        @click="onSelect"
    >
        <div class="aspect-[4/3] w-full overflow-hidden bg-bo-elevated">
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="String(item.name || '')"
                class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-[1.03]"
                :style="{ objectPosition }"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-xs text-bo-muted"
            >
                Нет фото
            </div>
        </div>
        <div class="flex flex-1 flex-col gap-1 p-3">
            <div class="line-clamp-2 text-sm font-medium text-bo-fg">
                {{ item.name }}
            </div>
            <div class="mt-auto text-xs text-bo-muted">
                {{ kindLabel }}
                <template v-if="priceLabel">· {{ priceLabel }}</template>
            </div>
        </div>
    </button>
</template>
