import { computed } from "vue";
import { useUiStore } from "../../stores/uiStore";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";

/**
 * @param {import("vue").ComputedRef<object|null>|import("vue").Ref<object|null>|(() => object|null)} promoStateSource
 */
export function useGiftPromotionPrompt(promoStateSource) {
    const uiStore = useUiStore();

    const giftPromotion = computed(() => {
        const state =
            typeof promoStateSource === "function"
                ? promoStateSource()
                : promoStateSource?.value ?? promoStateSource;
        if (!state || typeof state !== "object") {
            return null;
        }
        return state.gift_promotion && typeof state.gift_promotion === "object"
            ? state.gift_promotion
            : null;
    });

    const isGiftEligible = computed(() => giftPromotion.value?.eligible === true);
    const hasGiftSelected = computed(
        () => Number(giftPromotion.value?.selected_product_id) > 0,
    );
    const giftCtaLabel = computed(() =>
        hasGiftSelected.value ? "Изменить подарок" : "Выбрать подарок",
    );

    const giftCandidates = computed(() => {
        const promo = giftPromotion.value;
        if (!promo) {
            return [];
        }

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

    const selectedGiftName = computed(() => {
        const productId = Number(giftPromotion.value?.selected_product_id) || 0;
        if (productId <= 0) {
            return null;
        }
        const candidate = giftCandidates.value.find((item) => item.id === productId);
        return candidate?.name || `Товар #${productId}`;
    });

    function openGiftModal() {
        if (!isGiftEligible.value) {
            return;
        }
        emitDomainEvent(DOMAIN_EVENTS.BENEFIT_BANNER_CTA_CLICK, {
            source: "confirm",
            cta: "choose_gift",
        });
        uiStore.openGiftSelectionModal({ source: "manual" });
    }

    return {
        giftPromotion,
        isGiftEligible,
        hasGiftSelected,
        giftCtaLabel,
        giftCandidates,
        selectedGiftName,
        openGiftModal,
    };
}
