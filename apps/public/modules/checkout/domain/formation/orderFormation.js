/**
 * Порт App\Domain\Core\Order\Policy\OrderFormation.
 * Любое изменение правил — синхронно с PHP и эталонами tests/fixtures/order-formation.
 */

export const LINE_TYPE = Object.freeze({
    REGULAR: "regular",
    SET: "set",
    COMPLEMENT: "complement",
    GIFT: "gift",
});

const FEE_MODE = Object.freeze({
    BASE_TARIFF: "base_tariff",
    FREE: "free",
    OUTSIDE_ZONE_SURCHARGE_ONLY: "outside_zone_surcharge_only",
    BASE_PLUS_SURCHARGE: "base_plus_surcharge",
});

const WEEKDAY_ISO = Object.freeze({ Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6, Sun: 7 });

export function isBillable(line) {
    return line.lineType === LINE_TYPE.REGULAR || line.lineType === LINE_TYPE.SET;
}

export function lineTotalKopecks(line) {
    return line.unitPriceKopecks * line.quantity;
}

function billableSubtotal(lines) {
    return lines.reduce((sum, line) => (isBillable(line) ? sum + lineTotalKopecks(line) : sum), 0);
}

/**
 * Строки без смещения ("2026-11-23T18:00") — настенное время филиала.
 *
 * @param {Date|string} at
 * @param {string} timezone
 */
function isoWeekdayAt(at, timezone) {
    if (typeof at === "string") {
        const wallDate =
            /^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2})(?::\d{2}(?:\.\d+)?)?)?$/.exec(
                at.trim(),
            );
        if (wallDate) {
            const day = new Date(
                Date.UTC(Number(wallDate[1]), Number(wallDate[2]) - 1, Number(wallDate[3])),
            ).getUTCDay();
            return day === 0 ? 7 : day;
        }
    }

    const date = at instanceof Date ? at : new Date(at);
    const short = new Intl.DateTimeFormat("en-US", { timeZone: timezone, weekday: "short" }).format(
        date,
    );

    return WEEKDAY_ISO[short];
}

export function giftRuleForChannel(policy, channel) {
    return policy.giftRules.find((rule) => rule.order_channel === channel) ?? null;
}

/** Пустой allowed_weekdays = без ограничения. Дни ISO-8601 (1 = пн … 7 = вс). */
export function isGiftRuleAllowedOnDate(rule, at, timezone) {
    if (!rule?.is_active) {
        return false;
    }

    const days = (Array.isArray(rule.allowed_weekdays) ? rule.allowed_weekdays : [])
        .map(Number)
        .filter((day) => day >= 1 && day <= 7);
    if (days.length === 0) {
        return true;
    }

    return days.includes(isoWeekdayAt(at, timezone));
}

function complementLines(lines, policy, facts) {
    const benefit = policy.complementSetBenefit;
    if (!benefit?.is_active) {
        return [];
    }

    const rollsPerSet = Math.max(1, Number(benefit.rolls_per_set) || 1);
    const maxSets = Math.max(0, Number(benefit.max_sets) || 0);
    if (maxSets < 1) {
        return [];
    }

    const rollCount = lines.reduce(
        (sum, line) =>
            isBillable(line) ? sum + facts.rollUnitsPerLineUnit(line) * line.quantity : sum,
        0,
    );
    const sets = Math.min(maxSets, Math.floor(rollCount / rollsPerSet));
    if (sets < 1) {
        return [];
    }

    const result = [];
    for (const complement of facts.complements) {
        const quantity = facts.complementQuantity(complement.product_id, sets);
        if (quantity < 1) {
            continue;
        }
        result.push({
            productId: complement.product_id,
            name: complement.name,
            unitPriceKopecks: 0,
            quantity,
            lineType: LINE_TYPE.COMPLEMENT,
        });
    }

    return result;
}

function giftLines(lines, policy, facts, channel, at, timezone) {
    const rule = giftRuleForChannel(policy, channel);
    if (rule === null || !isGiftRuleAllowedOnDate(rule, at, timezone)) {
        return [];
    }

    if (billableSubtotal(lines) < (Number(rule.min_order_amount_kopecks) || 0)) {
        return [];
    }

    if (facts.gift === null) {
        return [];
    }

    return [
        {
            productId: facts.gift.product_id,
            name: facts.gift.name,
            unitPriceKopecks: 0,
            quantity: 1,
            lineType: LINE_TYPE.GIFT,
        },
    ];
}

function feeByMode(mode, baseFeeKopecks, surchargeKopecks) {
    switch (mode) {
        case FEE_MODE.BASE_TARIFF:
            return baseFeeKopecks;
        case FEE_MODE.FREE:
            return 0;
        case FEE_MODE.OUTSIDE_ZONE_SURCHARGE_ONLY:
            return surchargeKopecks;
        case FEE_MODE.BASE_PLUS_SURCHARGE:
            return baseFeeKopecks + surchargeKopecks;
        default:
            throw new Error(`Неизвестный режим тарифа доставки: ${mode}`);
    }
}

function applyDeliveryBenefit(lines, delivery, policy, zoneIsRemote) {
    if (delivery.method !== "courier") {
        return { ...delivery, feeKopecks: 0 };
    }

    const benefit = policy.deliveryBenefit;
    if (!benefit?.is_active) {
        return delivery;
    }

    const threshold = Number(benefit.free_delivery_threshold_kopecks) || 0;
    const atThreshold = billableSubtotal(lines) >= threshold;
    const mode = !atThreshold
        ? String(benefit.below_threshold_fee_mode ?? FEE_MODE.BASE_TARIFF)
        : zoneIsRemote
          ? String(
                benefit.outside_zone_at_threshold_fee_mode ?? FEE_MODE.OUTSIDE_ZONE_SURCHARGE_ONLY,
            )
          : String(benefit.in_zone_at_threshold_fee_mode ?? FEE_MODE.FREE);

    // Надбавка вне зоны — ответственность Delivery, не Promotion Policy.
    return { ...delivery, feeKopecks: feeByMode(mode, delivery.feeKopecks, 0) };
}

/**
 * @param {{
 *   baseLines: Array<{productId: number, name: string, unitPriceKopecks: number, quantity: number, lineType: string}>,
 *   delivery: {method: "courier"|"pickup", feeKopecks: number},
 *   policy: {giftRules: object[], deliveryBenefit: object, complementSetBenefit: object},
 *   facts: {
 *     complements: Array<{product_id: number, name: string}>,
 *     gift: {product_id: number, name: string}|null,
 *     rollUnitsPerLineUnit: (line: object) => number,
 *     complementQuantity: (productId: number, entitledSets: number) => number,
 *   },
 *   channel: string,
 *   at: Date|string,
 *   timezone: string,
 *   zoneIsRemote?: boolean,
 * }} input
 */
export function applyOrderFormation(input) {
    const {
        baseLines,
        delivery,
        policy,
        facts,
        channel,
        at,
        timezone,
        zoneIsRemote = false,
    } = input;

    if (baseLines.length === 0) {
        throw new Error("Заказ нельзя создать с пустой корзиной.");
    }
    for (const line of baseLines) {
        if (!isBillable(line)) {
            throw new Error("Базовые позиции заказа могут быть только regular или set.");
        }
    }

    let lines = [...baseLines];
    lines = [...lines, ...complementLines(lines, policy, facts)];
    lines = [...lines, ...giftLines(lines, policy, facts, channel, at, timezone)];

    return {
        lines,
        delivery: applyDeliveryBenefit(lines, delivery, policy, zoneIsRemote),
    };
}
