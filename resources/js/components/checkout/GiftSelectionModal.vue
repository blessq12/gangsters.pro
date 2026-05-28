<script setup>
import { computed, ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCartStore } from "../../stores/cartStore";
import { useCheckoutIntentStore } from "../../stores/checkoutIntentStore";
import { useUiStore } from "../../stores/uiStore";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";

const chk = useAppDesign().components.checkout;
const c = chk.cart;
const cartStore = useCartStore();
const checkoutIntent = useCheckoutIntentStore();
const uiStore = useUiStore();

const selectedGiftProductId = ref(null);
const giftApplying = ref(false);

const giftPromotion = computed(() => {
    const state = cartStore.promoState;
    if (!state || typeof state !== "object") return null;
    return state.gift_promotion && typeof state.gift_promotion === "object"
        ? state.gift_promotion
        : null;
});

const isGiftEligible = computed(() => giftPromotion.value?.eligible === true);
const giftCandidates = computed(() => {
    const promo = giftPromotion.value;
    if (!promo) return [];

    const candidateItems = Array.isArray(promo.candidate_items) ? promo.candidate_items : [];
    if (candidateItems.length > 0) {
        return candidateItems
            .map((item) => ({
                id: Number(item?.id) || 0,
                name: item?.name ? String(item.name) : "",
                priceRub: Number(item?.price_rub) || 0,
                imageUrl: item?.image_url ? String(item.image_url) : null,
            }))
            .filter((item) => item.id > 0);
    }

    const ids = Array.isArray(promo.candidate_product_ids) ? promo.candidate_product_ids : [];
    return ids
        .map((id) => Number(id) || 0)
        .filter((id) => id > 0)
        .map((id) => ({
            id,
            name: `Товар #${id}`,
            priceRub: 0,
            imageUrl: null,
        }));
});

const canApplyGiftSelection = computed(() =>
    giftCandidates.value.some((item) => item.id === Number(selectedGiftProductId.value)),
);

const isOpen = computed({
    get() {
        return uiStore.showGiftSelectionModal;
    },
    set(next) {
        if (next) {
            uiStore.openGiftSelectionModal({ source: "manual" });
            return;
        }
        uiStore.closeGiftSelectionModal({
            dismissAuto: uiStore.giftModalSource === "auto",
        });
    },
});

watch(
    () => uiStore.showGiftSelectionModal,
    (opened) => {
        if (!opened) return;
        selectedGiftProductId.value = Number(giftPromotion.value?.selected_product_id) || null;
    },
    { immediate: true },
);

function formatPrice(value) {
    return formatMoneyRublesRu(value);
}

async function applyGiftSelection() {
    if (!canApplyGiftSelection.value || giftApplying.value) return;
    giftApplying.value = true;
    try {
        await checkoutIntent.setPromotionGift(selectedGiftProductId.value);
        uiStore.closeGiftSelectionModal({ dismissAuto: false });
    } finally {
        giftApplying.value = false;
    }
}
</script>

<template>
    <BaseModal v-model="isOpen">
        <template #header>Выбери подарок</template>

        <div
            v-if="isGiftEligible && giftCandidates.length"
            :class="c.giftModalList"
        >
            <label
                v-for="item in giftCandidates"
                :key="item.id"
                :class="c.giftRadioLabel"
            >
                <input
                    v-model="selectedGiftProductId"
                    :value="item.id"
                    type="radio"
                    name="gift-candidate"
                    :class="c.giftRadioInput"
                />
                <div :class="c.giftRadioBody">
                    <p :class="c.giftRadioTitle">
                        {{ item.name || `Товар #${item.id}` }}
                    </p>
                    <p :class="c.giftRadioPrice">
                        Цена в меню: {{ formatPrice(item.priceRub) }} ₽, в корзине - 0 ₽
                    </p>
                </div>
                <img
                    v-if="item.imageUrl"
                    :src="item.imageUrl"
                    :alt="item.name || `Товар #${item.id}`"
                    :class="c.giftThumb"
                />
            </label>
        </div>
        <p
            v-else
            class="text-sm text-app-muted"
        >
            Сейчас список подарков недоступен.
        </p>

        <template #footer>
            <div :class="c.giftFooterRow">
                <button
                    type="button"
                    :class="c.giftApplyBtn"
                    :disabled="!canApplyGiftSelection || giftApplying"
                    @click="applyGiftSelection"
                >
                    {{ giftApplying ? "Применяем..." : "Применить" }}
                </button>
            </div>
        </template>
    </BaseModal>
</template>
