function emptyMoneyBenefit() {
    return {
        isActive: false,
        isReached: false,
        thresholdKopecks: null,
        currentKopecks: 0,
        remainingKopecks: 0,
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

function normalizeMoneyBenefit(raw) {
    if (!raw || typeof raw !== "object") {
        return emptyMoneyBenefit();
    }

    return {
        isActive: Boolean(raw.isActive ?? raw.is_active),
        isReached: Boolean(raw.isReached ?? raw.is_reached),
        thresholdKopecks:
            raw.thresholdKopecks ?? raw.threshold_kopecks ?? null,
        currentKopecks: Number(raw.currentKopecks ?? raw.current_kopecks) || 0,
        remainingKopecks: Number(raw.remainingKopecks ?? raw.remaining_kopecks) || 0,
        isPreview: Boolean(raw.isPreview ?? raw.is_preview),
    };
}

function normalizeComplementBenefit(raw) {
    if (!raw || typeof raw !== "object") {
        return emptyComplementBenefit();
    }

    return {
        isActive: Boolean(raw.isActive ?? raw.is_active),
        isReached: Boolean(raw.isReached ?? raw.is_reached),
        rollsPerSet: raw.rollsPerSet ?? raw.rolls_per_set ?? null,
        currentRollCount: Number(raw.currentRollCount ?? raw.current_roll_count) || 0,
        entitledSetCount: Number(raw.entitledSetCount ?? raw.entitled_set_count) || 0,
        remainingRollCount: Number(raw.remainingRollCount ?? raw.remaining_roll_count) || 0,
    };
}

/**
 * @param {object|null|undefined} benefitsProgress
 */
export function normalizeBenefitsProgress(benefitsProgress) {
    if (!benefitsProgress || typeof benefitsProgress !== "object") {
        return null;
    }

    return {
        delivery: normalizeMoneyBenefit(benefitsProgress.delivery),
        gift: normalizeMoneyBenefit(benefitsProgress.gift),
        complement: normalizeComplementBenefit(benefitsProgress.complement),
    };
}
