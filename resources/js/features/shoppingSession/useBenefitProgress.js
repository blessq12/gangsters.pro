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

function emptyComplementBenefit() {
    return {
        isActive: false,
        isReached: false,
        rollsPerSet: null,
        currentRollCount: 0,
        entitledSetCount: 0,
        remainingRollCount: 0,
    };
}

export function useBenefitProgress() {
    const cartReadModel = useCartReadModel();

    const delivery = computed(
        () => cartReadModel.benefitsProgress.value?.delivery ?? emptyMoneyBenefit(),
    );
    const gift = computed(() => cartReadModel.benefitsProgress.value?.gift ?? emptyMoneyBenefit());
    const complement = computed(
        () => cartReadModel.benefitsProgress.value?.complement ?? emptyComplementBenefit(),
    );
    const hasBenefitsProgress = computed(() => cartReadModel.hasBenefitsProgress.value);
    const hasActiveBenefits = computed(
        () =>
            Boolean(
                delivery.value.isActive || gift.value.isActive || complement.value.isActive,
            ),
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

    const complementProgressPercent = computed(() => {
        const rollsPerSet = Number(complement.value.rollsPerSet);
        const currentRollCount = Number(complement.value.currentRollCount);
        if (!Number.isFinite(rollsPerSet) || rollsPerSet <= 0) {
            return complement.value.isReached ? 100 : 0;
        }
        const towardNext = currentRollCount % rollsPerSet;
        const progressRolls = towardNext === 0 && complement.value.isReached
            ? rollsPerSet
            : towardNext;
        return Math.min(
            100,
            Math.max(0, Math.round((progressRolls / rollsPerSet) * 100)),
        );
    });

    const deliveryLabel = computed(() => {
        if (!delivery.value.isActive) {
            return null;
        }
        if (delivery.value.isReached) {
            return delivery.value.isPreview
                ? "Бесплатная доставка курьером — условие выполнено"
                : "Бесплатная доставка активна";
        }
        const remaining = formatKopecksToRub(delivery.value.remainingKopecks);
        return delivery.value.isPreview
            ? `До бесплатной доставки курьером осталось ${remaining} ₽`
            : `До бесплатной доставки осталось ${remaining} ₽`;
    });

    const giftLabel = computed(() => {
        if (!gift.value.isActive) {
            return null;
        }
        if (gift.value.isReached) {
            return "Подарок доступен";
        }
        const remaining = formatKopecksToRub(gift.value.remainingKopecks);
        return `До подарка осталось ${remaining} ₽`;
    });

    const complementLabel = computed(() => {
        if (!complement.value.isActive) {
            return null;
        }
        if (complement.value.isReached) {
            const count = Number(complement.value.entitledSetCount) || 0;
            if (count > 1) {
                return `Комплект дополнений ×${count} добавлен в корзину`;
            }
            return "Комплект дополнений добавлен в корзину";
        }
        const remaining = Number(complement.value.remainingRollCount) || 0;
        const rollsPerSet = Number(complement.value.rollsPerSet) || 2;
        const rollWord = remaining === 1 ? "ролл" : "ролла";
        return `До комплекта осталось ${remaining} ${rollWord} (каждые ${rollsPerSet})`;
    });

    const benefitLines = computed(() =>
        [deliveryLabel.value, giftLabel.value, complementLabel.value].filter(Boolean),
    );

    return {
        hasBenefitsProgress,
        hasActiveBenefits,
        canShowBenefitsBanner,
        delivery,
        gift,
        complement,
        deliveryProgressPercent,
        giftProgressPercent,
        complementProgressPercent,
        deliveryLabel,
        giftLabel,
        complementLabel,
        benefitLines,
        formatKopecksToRub,
    };
}
