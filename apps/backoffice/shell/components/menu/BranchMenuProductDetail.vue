<script setup>
import { computed } from "vue";

const props = defineProps({
    /** @type {import('vue').PropType<Record<string, unknown>>} */
    item: { type: Object, required: true },
});

const emit = defineEmits(["back"]);

const kindLabel = computed(() => (props.item?.kind === "set" ? "Набор" : "Товар"));

const priceLabel = computed(() => {
    const price = props.item?.price_rubles;
    return price != null && price !== "" ? `${price} ₽` : "—";
});

const imageUrl = computed(() => String(props.item?.image_url || "").trim());

const objectPosition = computed(() => {
    const x = Number(props.item?.image_focus_x);
    const y = Number(props.item?.image_focus_y);
    const fx = Number.isFinite(x) ? x : 50;
    const fy = Number.isFinite(y) ? y : 50;
    return `${fx}% ${fy}%`;
});

const ingredients = computed(() => {
    const rows = Array.isArray(props.item?.ingredients) ? props.item.ingredients : [];
    return rows.map((row) => String(row || "").trim()).filter(Boolean);
});

const techCardParts = computed(() => {
    const raw = String(props.item?.tech_card_text || "").trim();
    if (!raw) {
        return [];
    }
    return raw.split(/\s+/).filter(Boolean);
});

const description = computed(() => String(props.item?.description || "").trim());

const weightLabel = computed(() => {
    const weight = props.item?.weight;
    if (weight == null || weight === "") {
        return "";
    }
    return `${weight} г`;
});
</script>

<template>
    <div class="flex h-full min-h-0 flex-col gap-4">
        <button
            type="button"
            class="inline-flex h-9 w-fit items-center px-2 text-sm text-bo-muted transition-colors hover:bg-white/[0.06] hover:text-bo-fg"
            @click="emit('back')"
        >
            ← Назад к списку
        </button>

        <div class="min-h-0 flex-1 overflow-auto">
            <div class="grid gap-5 sm:grid-cols-[minmax(0,14rem)_minmax(0,1fr)]">
                <div class="aspect-square overflow-hidden border border-bo-border bg-bo-field">
                    <img
                        v-if="imageUrl"
                        :src="imageUrl"
                        :alt="String(item.name || '')"
                        class="h-full w-full object-cover"
                        :style="{ objectPosition }"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-sm text-bo-muted"
                    >
                        Нет фото
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <h4 class="text-base font-semibold text-bo-fg">
                            {{ item.name }}
                        </h4>
                        <p class="mt-1 text-sm text-bo-muted">
                            {{ kindLabel }} · {{ priceLabel }}
                            <template v-if="weightLabel">· {{ weightLabel }}</template>
                        </p>
                    </div>

                    <div v-if="description">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-bo-muted">
                            Описание
                        </p>
                        <p class="mt-1 whitespace-pre-wrap text-sm text-bo-fg">
                            {{ description }}
                        </p>
                    </div>

                    <div v-if="ingredients.length > 0">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-bo-muted">
                            Состав
                        </p>
                        <p class="mt-1 text-sm text-bo-fg">
                            {{ ingredients.join(", ") }}
                        </p>
                    </div>

                    <div v-if="techCardParts.length > 0">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-bo-muted">
                            Техкарта
                        </p>
                        <p class="mt-1 text-sm text-bo-fg">
                            {{ techCardParts.join(", ") }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
