import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { formatKopecksToRub } from "../../utils/moneyFormat";
import { useCheckoutBenefitVisibility } from "./useCheckoutBenefitVisibility";

function emptyMoneyBenefit() {
    return {
        isActive: false,
        isReached: false,
        remainingKopecks: 0,
        thresholdKopecks: null,
        currentKopecks: 0,
        isPreview: false,
    };
}

/**
 * Read-model превью заказа из блока order_preview API.
 */
export function useOrderPreview() {
    const checkoutStore = useCheckoutStore();
    const { orderPreview, hasCartItems } = storeToRefs(checkoutStore);
    const { showGiftProgress } = useCheckoutBenefitVisibility();

    const preview = computed(() => orderPreview.value);
    const complementLines = computed(() => preview.value?.complementLines ?? []);
    const autoLines = computed(() => preview.value?.autoLines ?? []);
    const giftSummary = computed(() => preview.value?.giftSummary ?? null);
    const giftCta = computed(() => preview.value?.giftCta ?? null);
    const totals = computed(
        () =>
            preview.value?.totals ?? {
                itemsTotalRubles: 0,
                deliveryFeeRubles: null,
                grandTotalRubles: 0,
                isDeliveryFree: false,
                isDeliveryPreview: false,
            },
    );

    const delivery = computed(
        () => preview.value?.benefits?.delivery ?? emptyMoneyBenefit(),
    );
    const gift = computed(() => preview.value?.benefits?.gift ?? emptyMoneyBenefit());

    const hasComplementLines = computed(() => complementLines.value.length > 0);
    const hasAutoLines = computed(() => autoLines.value.length > 0);
    const hasDeliveryPricing = computed(() => totals.value.deliveryFeeRubles != null);

    const deliveryProgressPercent = computed(() => {
        const threshold = Number(delivery.value.thresholdKopecks);
        const current = Number(delivery.value.currentKopecks);
        if (!Number.isFinite(threshold) || threshold <= 0) {
            return delivery.value.isReached ? 100 : 0;
        }
        return Math.min(100, Math.max(0, Math.round((current / threshold) * 100)));
    });

    const giftProgressPercent = computed(() => {
        const threshold = Number(gift.value.thresholdKopecks);
        const current = Number(gift.value.currentKopecks);
        if (!Number.isFinite(threshold) || threshold <= 0) {
            return gift.value.isReached ? 100 : 0;
        }
        return Math.min(100, Math.max(0, Math.round((current / threshold) * 100)));
    });

    const deliveryLabel = computed(() => {
        if (!delivery.value.isActive) {
            return null;
        }
        if (delivery.value.isReached) {
            return delivery.value.isPreview
                ? "Бесплатная доставка курьером"
                : "Бесплатная доставка";
        }
        const remaining = formatKopecksToRub(delivery.value.remainingKopecks);
        return `Ещё ${remaining} ₽ до бесплатной доставки`;
    });

    const giftLabel = computed(() => {
        if (!gift.value.isActive) {
            return null;
        }
        if (gift.value.isReached) {
            return "Подарок доступен";
        }
        const remaining = formatKopecksToRub(gift.value.remainingKopecks);
        return `Ещё ${remaining} ₽ до подарка`;
    });

    const canShowBenefits = computed(() => {
        if (!hasCartItems.value || !preview.value) {
            return false;
        }

        const deliveryVisible = delivery.value.isActive && deliveryLabel.value;
        const giftVisible =
            showGiftProgress.value && gift.value.isActive && giftLabel.value;

        return deliveryVisible || giftVisible;
    });

    const isGiftEligible = computed(() => giftCta.value?.eligible === true);
    const hasGiftSelected = computed(
        () => Number(giftCta.value?.selectedProductId) > 0,
    );
    const giftCtaLabel = computed(() =>
        hasGiftSelected.value ? "Изменить подарок" : "Выбрать подарок",
    );
    const giftCandidates = computed(() => giftCta.value?.candidateItems ?? []);
    const selectedGiftName = computed(() => {
        const productId = Number(giftCta.value?.selectedProductId) || 0;
        if (productId <= 0) {
            return null;
        }

        const candidate = giftCandidates.value.find((item) => item.id === productId);
        return candidate?.name || `Товар #${productId}`;
    });

    return {
        preview,
        complementLines,
        autoLines,
        giftSummary,
        giftCta,
        totals,
        delivery,
        gift,
        hasComplementLines,
        hasAutoLines,
        hasDeliveryPricing,
        hasCartItems,
        deliveryProgressPercent,
        giftProgressPercent,
        deliveryLabel,
        giftLabel,
        canShowBenefits,
        showGiftProgress,
        isGiftEligible,
        hasGiftSelected,
        giftCtaLabel,
        giftCandidates,
        selectedGiftName,
    };
}
