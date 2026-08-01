<script setup>
import { storeToRefs } from "pinia";
import { computed, ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { resolveGiftSelectionRequired } from "../../features/checkout/giftSelectionGate";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useUiStore } from "../../stores/uiStore";
import GiftCandidateCard from "./GiftCandidateCard.vue";

const chk = useAppDesign().components.checkout;
const c = chk.cart;
const checkoutStore = useCheckoutStore();
const uiStore = useUiStore();
const { orderPreview, promoState, wizardMissingBlocks, cartItems, error } =
    storeToRefs(checkoutStore);

const selectedGiftProductId = ref(null);
const giftApplying = ref(false);

const giftPromotion = computed(() => {
    const state = promoState.value;
    if (!state || typeof state !== "object") return null;
    return state.gift_promotion && typeof state.gift_promotion === "object"
        ? state.gift_promotion
        : null;
});

const isGiftEligible = computed(() => giftPromotion.value?.eligible === true);

const isGiftSelectionMandatory = computed(() =>
    resolveGiftSelectionRequired({
        giftCta: orderPreview.value?.giftCta,
        promoState: promoState.value,
        wizardMissingBlocks: wizardMissingBlocks.value,
        cartItems: cartItems.value,
        giftSummary: orderPreview.value?.giftSummary,
    }),
);

function mapCandidateItem(item) {
    if (!item || typeof item !== "object") {
        return null;
    }

    const id = Number(item.id) || 0;
    if (id <= 0) {
        return null;
    }

    const composition = Array.isArray(item.composition)
        ? item.composition
        : Array.isArray(item.composition_items)
          ? item.composition_items
          : [];

    return {
        id,
        name: item.name ? String(item.name) : "",
        priceRub: Number(item.price_rub ?? item.priceRub) || 0,
        imageUrl:
            (item.image_url ?? item.imageUrl)
                ? String(item.image_url ?? item.imageUrl)
                : null,
        composition: composition.map((part) => String(part)).filter(Boolean),
    };
}

const giftCandidates = computed(() => {
    const previewItems = orderPreview.value?.giftCta?.candidateItems;
    if (Array.isArray(previewItems) && previewItems.length > 0) {
        return previewItems
            .map((item) => ({
                id: Number(item.id) || 0,
                name: item.name ? String(item.name) : "",
                priceRub: Number(item.priceRub) || 0,
                imageUrl: item.imageUrl ? String(item.imageUrl) : null,
                composition: Array.isArray(item.composition)
                    ? item.composition
                          .map((part) => String(part))
                          .filter(Boolean)
                    : [],
            }))
            .filter((item) => item.id > 0);
    }

    const promo = giftPromotion.value;
    if (!promo) return [];

    const candidateItems = Array.isArray(promo.candidate_items)
        ? promo.candidate_items
        : [];
    if (candidateItems.length > 0) {
        return candidateItems.map(mapCandidateItem).filter(Boolean);
    }

    const ids = Array.isArray(promo.candidate_product_ids)
        ? promo.candidate_product_ids
        : [];
    return ids
        .map((id) => Number(id) || 0)
        .filter((id) => id > 0)
        .map((id) => ({
            id,
            name: `Товар #${id}`,
            priceRub: 0,
            imageUrl: null,
            composition: [],
        }));
});

const canApplyGiftSelection = computed(() =>
    giftCandidates.value.some(
        (item) => item.id === Number(selectedGiftProductId.value),
    ),
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

        if (isGiftSelectionMandatory.value) {
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

        const selectedFromPreview =
            Number(orderPreview.value?.giftCta?.selectedProductId) || 0;
        const selectedFromPromo =
            Number(giftPromotion.value?.selected_product_id) || 0;
        const selectedFromStore =
            Number(checkoutStore.promotions?.freeRollGiftProductId) || 0;
        selectedGiftProductId.value =
            selectedFromPreview ||
            selectedFromPromo ||
            selectedFromStore ||
            null;
    },
    { immediate: true },
);

async function applyGiftSelection(productId = selectedGiftProductId.value) {
    const normalizedId = Number(productId) || 0;
    if (
        !giftCandidates.value.some((item) => item.id === normalizedId) ||
        giftApplying.value
    ) {
        return;
    }

    selectedGiftProductId.value = normalizedId;
    giftApplying.value = true;

    try {
        await checkoutStore.setPromotionGift(normalizedId);
        uiStore.closeGiftSelectionModal({ dismissAuto: false });
    } catch {
        // Ошибка уже в checkoutStore.error
    } finally {
        giftApplying.value = false;
    }
}

function handleCandidateSelect(item) {
    if (!item?.id || giftApplying.value) {
        return;
    }

    selectedGiftProductId.value = item.id;
}
</script>

<template>
    <BaseModal v-model="isOpen" :closable="!isGiftSelectionMandatory">
        <template #header>Выбери подарок</template>

        <div
            v-if="isGiftEligible && giftCandidates.length"
            :class="c.giftModalList"
        >
            <GiftCandidateCard
                v-for="item in giftCandidates"
                :key="item.id"
                :item="item"
                :selected="Number(selectedGiftProductId) === item.id"
                :disabled="giftApplying"
                @select="handleCandidateSelect"
            />
        </div>
        <p v-else class="text-sm text-app-muted">
            Сейчас список подарков недоступен.
        </p>

        <p v-if="error" class="mt-3 text-sm text-red-300">
            {{ error }}
        </p>

        <template #footer>
            <div :class="c.giftFooterRow">
                <button
                    type="button"
                    :class="c.giftApplyBtn"
                    :disabled="!canApplyGiftSelection || giftApplying"
                    @click="applyGiftSelection()"
                >
                    {{ giftApplying ? "Выбираем..." : "Выбрать" }}
                </button>
            </div>
        </template>
    </BaseModal>
</template>
