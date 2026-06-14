import { computed } from "vue";
import { useCartReadModel } from "./useCartReadModel";
import { formatKopecksToRub } from "../../utils/moneyFormat";

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

export function useBenefitProgress() {
    const cartReadModel = useCartReadModel();

    const delivery = computed(
        () => cartReadModel.benefitsProgress.value?.delivery ?? emptyMoneyBenefit(),
    );
    const gift = computed(() => cartReadModel.benefitsProgress.value?.gift ?? emptyMoneyBenefit());
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

    const benefitLines = computed(() =>
        [deliveryLabel.value, giftLabel.value].filter(Boolean),
    );

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
        benefitLines,
        formatKopecksToRub,
    };
}
