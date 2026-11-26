import { matchDeliveryZone } from "./matchDeliveryZone.js";
import {
    applyOrderFormation,
    giftRuleForChannel,
    isBillable,
    isGiftRuleAllowedOnDate,
    LINE_TYPE,
    lineTotalKopecks,
} from "./orderFormation.js";

/**
 * Локальный аналог QuoteOrderUseCase: контекст филиала + намерение клиента → сформированный заказ.
 * Формат ответа — вход adaptFormationToCheckoutSnapshot.
 */

function toCoord(value) {
    if (value === null || value === undefined || value === "") {
        return null;
    }
    const number = Number(value);
    return Number.isFinite(number) ? number : null;
}

function resolveCoordinates(intent) {
    if (intent.delivery_method !== "courier") {
        return { latitude: null, longitude: null };
    }

    const address = intent.address ?? {};
    const latitude = toCoord(address.lat ?? address.latitude ?? intent.latitude);
    const longitude = toCoord(address.lng ?? address.longitude ?? intent.longitude);
    if (latitude === null || longitude === null) {
        return { latitude: null, longitude: null };
    }

    return { latitude, longitude };
}

function buildBaseLines(context, intentLines) {
    const productsById = new Map(context.menu.products.map((product) => [product.id, product]));
    const setsById = new Map(context.menu.sets.map((set) => [set.id, set]));
    const baseLines = [];
    const unavailableProductIds = [];

    for (const raw of intentLines ?? []) {
        const id = Number(raw.product_id ?? raw.id) || 0;
        const quantity = Number(raw.quantity) || 0;
        if (id < 1 || quantity < 1) {
            continue;
        }

        const isSet = raw.kind === "set";
        const source = isSet ? setsById.get(id) : productsById.get(id);
        if (!source) {
            unavailableProductIds.push(id);
            continue;
        }

        baseLines.push({
            productId: id,
            name: source.name,
            unitPriceKopecks: source.price_kopecks,
            quantity,
            lineType: isSet ? LINE_TYPE.SET : LINE_TYPE.REGULAR,
            rollsPerUnit: Math.max(0, Number(source.rolls_per_unit) || 0),
        });
    }

    return { baseLines, unavailableProductIds };
}

function buildFacts(context, intent) {
    const commercialRules = context.commercial_rules;
    const giftProductId = Number(intent.gift_product_id) || 0;
    const gift =
        giftProductId > 0
            ? (commercialRules.gift_candidates.find(
                  (candidate) => candidate.product_id === giftProductId,
              ) ?? null)
            : null;

    const selections = new Map();
    for (const row of Array.isArray(intent.complement_selections)
        ? intent.complement_selections
        : []) {
        selections.set(Number(row.product_id), Number(row.quantity) || 0);
    }

    return {
        complements: commercialRules.complements,
        giftCandidates: commercialRules.gift_candidates,
        gift,
        rollUnitsPerLineUnit: (line) => line.rollsPerUnit ?? 0,
        complementQuantity: (productId, entitledSets) => {
            if (selections.size === 0) {
                return entitledSets;
            }
            return Math.max(0, Math.min(entitledSets, selections.get(productId) ?? 0));
        },
    };
}

function presentLines(lines) {
    return lines.map((line) => ({
        product_id: line.productId,
        product_name: line.name,
        quantity: line.quantity,
        unit_price_kopecks: line.unitPriceKopecks,
        unit_price_rubles: line.unitPriceKopecks / 100,
        line_total_rubles: lineTotalKopecks(line) / 100,
        payload: {
            kind: line.lineType,
            catalog_kind: line.lineType === LINE_TYPE.SET ? "set" : "product",
        },
    }));
}

function benefits(context, facts, lines, channel, at, itemsTotalKopecks, deliveryFeeKopecks) {
    const commercialRules = context.commercial_rules;
    const giftRule = giftRuleForChannel({ giftRules: commercialRules.gift_rules }, channel);
    const weekdayOk = giftRule !== null && isGiftRuleAllowedOnDate(giftRule, at, context.timezone);
    const giftThreshold = Number(giftRule?.min_order_amount_kopecks) || 0;
    const giftRuleOk =
        weekdayOk && itemsTotalKopecks >= giftThreshold && facts.giftCandidates.length > 0;

    const giftCandidates = giftRuleOk
        ? facts.giftCandidates.map((candidate) => ({
              id: candidate.product_id,
              product_id: candidate.product_id,
              name: candidate.name,
          }))
        : [];

    let giftLine = null;
    let rollCount = 0;
    for (const line of lines) {
        if (line.lineType === LINE_TYPE.GIFT && giftLine === null) {
            giftLine = line;
        }
        if (isBillable(line)) {
            rollCount += facts.rollUnitsPerLineUnit(line) * line.quantity;
        }
    }

    const complement = commercialRules.complement_set_benefit;
    const deliveryBenefit = commercialRules.delivery_benefit;
    const threshold = Number(deliveryBenefit.free_delivery_threshold_kopecks) || 0;
    const rollsPerSet = Math.max(1, Number(complement.rolls_per_set) || 1);
    const maxSets = Number(complement.max_sets) || 0;
    const entitled = complement.is_active
        ? Math.min(Math.floor(rollCount / rollsPerSet), maxSets)
        : 0;

    return {
        gift_active: Boolean(giftRule?.is_active),
        gift_weekday_ok: weekdayOk,
        gift_allowed_weekdays: giftRule?.allowed_weekdays ?? [],
        gift_eligible: giftRuleOk,
        gift_selected: giftLine !== null,
        gift_threshold_kopecks: giftThreshold,
        gift_current_kopecks: itemsTotalKopecks,
        gift_remaining_kopecks: Math.max(0, giftThreshold - itemsTotalKopecks),
        gift_candidates: giftCandidates,
        rolls_per_set: rollsPerSet,
        complement_max_sets: maxSets,
        roll_count: rollCount,
        complement_entitled: entitled,
        remaining_roll_count: Math.max(0, (entitled + 1) * rollsPerSet - rollCount),
        complement_active: Boolean(complement.is_active),
        delivery_active: Boolean(deliveryBenefit.is_active),
        delivery_reached:
            channel === "courier" &&
            Boolean(deliveryBenefit.is_active) &&
            itemsTotalKopecks >= threshold &&
            deliveryFeeKopecks === 0,
        free_delivery_threshold_kopecks: threshold,
        remaining_to_free_kopecks: Math.max(0, threshold - itemsTotalKopecks),
    };
}

function clientSnapshot(client) {
    const raw = client ?? {};
    const isRegistered = raw.kind === "registered" || raw.kind === "client";
    const clientId = Number(raw.client_id ?? raw.id) || null;

    return {
        kind: isRegistered ? "client" : "guest",
        id: isRegistered ? clientId : null,
        client_id: isRegistered ? clientId : null,
        name: String(raw.name ?? "").trim(),
        phone: String(raw.phone ?? "").trim(),
        ...(raw.email ? { email: String(raw.email).trim() } : {}),
    };
}

/**
 * @param {object} context ответ GET /api/public/checkout/context
 * @param {object} intent слепок намерения (тот же body, что уходит в POST /order)
 * @param {Date} [now]
 * @returns {object|null} null — пустая корзина
 */
export function formCheckout(context, intent, now = new Date()) {
    const { baseLines, unavailableProductIds } = buildBaseLines(context, intent.lines);
    if (baseLines.length === 0) {
        return null;
    }

    const deliveryMethod = intent.delivery_method === "pickup" ? "pickup" : "courier";
    const { latitude, longitude } = resolveCoordinates({
        ...intent,
        delivery_method: deliveryMethod,
    });
    const zone =
        deliveryMethod === "courier"
            ? matchDeliveryZone(context.delivery.zones, latitude, longitude)
            : null;
    const hasCoords = latitude !== null && longitude !== null;

    const deliveryAvailable = deliveryMethod === "pickup" ? true : hasCoords ? zone !== null : null;
    const inZone = deliveryMethod === "pickup" ? null : hasCoords ? zone !== null : null;

    const scheduledAt = intent.scheduled_at ? String(intent.scheduled_at).trim() : "";
    const at = scheduledAt !== "" ? scheduledAt : now;

    const delivery =
        deliveryMethod === "courier"
            ? { method: "courier", feeKopecks: zone?.fee_kopecks ?? 0 }
            : { method: "pickup", feeKopecks: 0 };
    const zoneIsRemote =
        deliveryMethod === "courier" ? (zone === null ? hasCoords : zone.is_remote) : false;

    const facts = buildFacts(context, intent);
    const formed = applyOrderFormation({
        baseLines,
        delivery,
        policy: {
            giftRules: context.commercial_rules.gift_rules,
            deliveryBenefit: context.commercial_rules.delivery_benefit,
            complementSetBenefit: context.commercial_rules.complement_set_benefit,
        },
        facts,
        channel: deliveryMethod,
        at,
        timezone: context.timezone,
        zoneIsRemote,
    });

    const itemsTotalKopecks = formed.lines.reduce(
        (sum, line) => (isBillable(line) ? sum + lineTotalKopecks(line) : sum),
        0,
    );
    const deliveryFeeKopecks = formed.delivery.feeKopecks;
    const grandTotalKopecks = itemsTotalKopecks + deliveryFeeKopecks;

    const totals = {
        items_total: itemsTotalKopecks,
        discount: 0,
        delivery_fee: deliveryFeeKopecks,
        grand_total: grandTotalKopecks,
        items_rubles: itemsTotalKopecks / 100,
        items_total_rubles: itemsTotalKopecks / 100,
        delivery_fee_rubles: deliveryFeeKopecks / 100,
        grand_total_rubles: grandTotalKopecks / 100,
    };

    return {
        branch_id: context.branch_id,
        rules_version: context.rules_version,
        unavailable_product_ids: unavailableProductIds,
        cart: {
            lines: presentLines(formed.lines),
            items_total: itemsTotalKopecks,
            totals,
        },
        benefits: benefits(
            context,
            facts,
            formed.lines,
            deliveryMethod,
            at,
            itemsTotalKopecks,
            deliveryFeeKopecks,
        ),
        client: clientSnapshot(intent.client),
        delivery: {
            method: deliveryMethod,
            address:
                deliveryMethod === "courier" && intent.address
                    ? { ...intent.address, lat: latitude, lng: longitude }
                    : null,
            comment: intent.delivery_comment ?? null,
            persons: intent.persons ?? null,
            scheduled_at: intent.scheduled_at ?? null,
            delivery_fee_rubles: deliveryFeeKopecks / 100,
            delivery_fee_kopecks: deliveryFeeKopecks,
            delivery_available: deliveryAvailable,
            in_zone: inZone,
            zone: zone
                ? {
                      id: zone.id,
                      name: zone.name,
                      fee_kopecks: zone.fee_kopecks,
                      is_remote: zone.is_remote,
                  }
                : null,
            zone_id: zone?.id ?? null,
            zone_name: zone?.name ?? null,
            is_remote: zoneIsRemote,
            fee: deliveryFeeKopecks,
            min_order_kopecks: context.delivery.min_order_kopecks,
            branch_id: context.branch_id,
            latitude,
            longitude,
        },
        payment: {
            method: intent.payment_method ?? null,
            change_from_rubles: intent.change_from_rubles ?? null,
        },
        totals,
    };
}
