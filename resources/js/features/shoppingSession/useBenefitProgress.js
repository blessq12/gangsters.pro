import { computed } from "vue";
import { useCartReadModel } from "./useCartReadModel";

function emptyBenefit() {
    return {
        isActive: false,
        isReached: false,
        remainingKopecks: 0,
        thresholdKopecks: null,
        currentKopecks: 0,
        status: "empty",
        messageKey: null,
        phase: "none",
        selectedProductId: null,
    };
}

export function useBenefitProgress() {
    const cartReadModel = useCartReadModel();

    const delivery = computed(
        () => cartReadModel.benefitsProgress.value?.delivery ?? emptyBenefit(),
    );
    const gift = computed(() => cartReadModel.benefitsProgress.value?.gift ?? emptyBenefit());
    const hasBenefitsProgress = computed(() => cartReadModel.hasBenefitsProgress.value);
    const hasActiveBenefits = computed(
        () => Boolean(delivery.value.isActive || gift.value.isActive),
    );
    const canShowBenefitsBanner = computed(
        () =>
            cartReadModel.totalItems.value > 0 &&
            hasBenefitsProgress.value &&
            hasActiveBenefits.value,
    );

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
            return "Бесплатная доставка активна";
        }
        const remaining = Math.ceil((Number(delivery.value.remainingKopecks) || 0) / 100);
        return `До бесплатной доставки осталось ${remaining} ₽`;
    });

    const giftLabel = computed(() => {
        if (!gift.value.isActive) {
            return null;
        }
        if (gift.value.isReached) {
            return "Подарок доступен";
        }
        const remaining = Math.ceil((Number(gift.value.remainingKopecks) || 0) / 100);
        return `До подарка осталось ${remaining} ₽`;
    });

    return {
        hasBenefitsProgress,
        hasActiveBenefits,
        canShowBenefitsBanner,
        delivery,
        gift,
        deliveryProgressPercent,
        giftProgressPercent,
        deliveryLabel,
        giftLabel,
    };
}
