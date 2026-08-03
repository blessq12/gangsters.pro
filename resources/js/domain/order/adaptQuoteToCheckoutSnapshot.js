/**
 * Адаптер ответа POST /api/order/quote → формат checkoutStore.applyFromServer.
 *
 * @param {object|null|undefined} quote
 * @returns {object|null}
 */
export function adaptQuoteToCheckoutSnapshot(quote) {
    if (!quote || typeof quote !== "object") {
        return null;
    }

    const totals = quote.totals && typeof quote.totals === "object" ? quote.totals : {};
    const benefits = quote.benefits && typeof quote.benefits === "object" ? quote.benefits : {};
    const delivery = quote.delivery && typeof quote.delivery === "object" ? quote.delivery : {};
    const cart = quote.cart && typeof quote.cart === "object" ? quote.cart : {};
    const lines = Array.isArray(cart.lines) ? cart.lines : [];

    const itemsRubles = Number(totals.items_rubles) || 0;
    const deliveryFeeRubles = Number(totals.delivery_fee_rubles) || 0;
    const grandTotalRubles =
        Number(totals.grand_total_rubles) || itemsRubles + deliveryFeeRubles;

    const itemsTotalKopecks = Math.round(itemsRubles * 100);
    const deliveryFeeKopecks = Math.round(deliveryFeeRubles * 100);
    const grandTotalKopecks = Math.round(grandTotalRubles * 100);

    const freeThreshold =
        benefits.free_delivery_threshold_kopecks != null
            ? Number(benefits.free_delivery_threshold_kopecks)
            : null;
    const remainingToFree =
        Number(benefits.remaining_to_free_kopecks) ||
        (freeThreshold != null ? Math.max(0, freeThreshold - itemsTotalKopecks) : 0);

    const giftEligible = Boolean(benefits.gift_eligible);
    const giftSelected = Boolean(benefits.gift_selected);
    const giftCandidates = Array.isArray(benefits.gift_candidates)
        ? benefits.gift_candidates
        : Array.isArray(cart.promo_state?.gift_promotion?.candidate_items)
          ? cart.promo_state.gift_promotion.candidate_items
          : [];

    const giftLine = lines.find((line) => line?.payload?.kind === "gift") ?? null;
    const selectedGiftProductId = giftLine
        ? Number(giftLine.product_id) || null
        : Number(cart.promo_state?.gift_promotion?.selected_product_id) || null;

    const giftPhase = cart.promo_state?.gift_promotion?.phase
        ? String(cart.promo_state.gift_promotion.phase)
        : !giftEligible
          ? "locked"
          : giftSelected || selectedGiftProductId
            ? "selected"
            : "choose";

    const promoState =
        cart.promo_state && typeof cart.promo_state === "object"
            ? cart.promo_state
            : {
                  gift_promotion: {
                      eligible: giftEligible,
                      phase: giftPhase,
                      selected_product_id: selectedGiftProductId,
                      candidate_items: giftCandidates,
                  },
              };

    const complementLines = lines
        .filter((line) => line?.payload?.kind === "complement")
        .map((line) => ({
            product_id: Number(line.product_id) || 0,
            name: line.product_name ? String(line.product_name) : "",
            quantity: Number(line.quantity) || 1,
            price_rubles: 0,
            is_free: true,
        }))
        .filter((line) => line.product_id > 0);

    const giftSummary =
        selectedGiftProductId && giftLine
            ? {
                  product_id: selectedGiftProductId,
                  name: giftLine.product_name
                      ? String(giftLine.product_name)
                      : `Товар #${selectedGiftProductId}`,
                  quantity: 1,
              }
            : null;

    const giftThresholdKopecks =
        benefits.gift_threshold_kopecks != null
            ? Number(benefits.gift_threshold_kopecks)
            : null;
    const giftCurrentKopecks =
        Number(benefits.gift_current_kopecks) || itemsTotalKopecks;
    const giftRemainingKopecks = Number(benefits.gift_remaining_kopecks) || 0;

    const rollsPerSet =
        benefits.rolls_per_set != null ? Number(benefits.rolls_per_set) : null;
    const rollCount = Number(benefits.roll_count) || 0;
    const entitledSets = Number(benefits.complement_entitled) || 0;
    const remainingRollCount = Number(benefits.remaining_roll_count) || 0;
    const complementActive = Boolean(benefits.complement_active);

    const deliveryActive = Boolean(benefits.delivery_active);
    const deliveryReached = Boolean(benefits.delivery_reached);
    const inZone =
        delivery.in_zone === true
            ? true
            : delivery.in_zone === false
              ? false
              : null;

    const missingBlocks = [];
    const client = quote.client && typeof quote.client === "object" ? quote.client : {};
    const payment = quote.payment && typeof quote.payment === "object" ? quote.payment : {};
    const phone = String(client.phone || "").trim();
    const placeholderPhone = phone === "" || phone.includes("000) 000-00-00");

    if (placeholderPhone && client.kind !== "registered") {
        missingBlocks.push("client");
    }
    if (!delivery.method) {
        missingBlocks.push("delivery");
    }
    if (
        delivery.method === "courier"
        && !(delivery.address?.street && delivery.address?.house)
    ) {
        missingBlocks.push("delivery");
    }
    if (!payment.method) {
        missingBlocks.push("payment");
    }
    if (giftEligible && !selectedGiftProductId) {
        missingBlocks.push("gift");
    }

    const canConfirm = !missingBlocks.some((block) => block !== "gift");

    const snapshot = {
        cart: {
            lines,
            items: lines,
            items_total_rubles: itemsRubles,
            payable_total_rubles: itemsRubles,
            promo_state: promoState,
        },
        delivery: quote.delivery ?? null,
        payment: quote.payment ?? null,
        delivery_pricing: {
            method: delivery.method ?? null,
            items_total_kopecks: itemsTotalKopecks,
            items_payable_kopecks: itemsTotalKopecks,
            delivery_fee_kopecks: deliveryFeeKopecks,
            grand_total_kopecks: grandTotalKopecks,
            is_free: deliveryFeeKopecks === 0 && delivery.method === "courier",
            is_preview: delivery.method !== "pickup" && inZone == null,
            in_zone: inZone,
            remaining_to_free_kopecks: remainingToFree,
            items_total_rub: itemsRubles,
            delivery_fee_rub: deliveryFeeRubles,
            grand_total_rub: grandTotalRubles,
        },
        benefits_progress: {
            delivery: {
                is_active: deliveryActive,
                is_reached: deliveryReached,
                threshold_kopecks: freeThreshold,
                current_kopecks: itemsTotalKopecks,
                remaining_kopecks: remainingToFree,
                is_preview: delivery.method !== "pickup" && inZone == null,
            },
            gift: {
                is_active: Boolean(benefits.gift_active),
                is_reached: giftEligible,
                threshold_kopecks: giftThresholdKopecks,
                current_kopecks: giftCurrentKopecks,
                remaining_kopecks: giftRemainingKopecks,
                is_preview: false,
            },
            complement: {
                is_active: complementActive,
                is_reached: entitledSets > 0,
                rolls_per_set: rollsPerSet,
                current_roll_count: rollCount,
                entitled_set_count: entitledSets,
                remaining_roll_count: remainingRollCount,
            },
        },
        order_preview: {
            complement_lines: complementLines,
            auto_lines: [],
            gift_summary: giftSummary,
            gift_cta: {
                eligible: giftEligible,
                phase: giftPhase,
                selected_product_id: selectedGiftProductId,
                candidate_items: giftCandidates,
            },
            totals: {
                items_total_rubles: itemsRubles,
                delivery_fee_rubles: deliveryFeeRubles,
                grand_total_rubles: grandTotalRubles,
                is_delivery_free: deliveryFeeKopecks === 0 && delivery.method === "courier",
                is_delivery_preview: delivery.method !== "pickup" && inZone == null,
                in_zone: inZone,
            },
            benefits: {
                delivery: {
                    is_active: deliveryActive,
                    is_reached: deliveryReached,
                    threshold_kopecks: freeThreshold,
                    current_kopecks: itemsTotalKopecks,
                    remaining_kopecks: remainingToFree,
                    is_preview: delivery.method !== "pickup" && inZone == null,
                },
                gift: {
                    is_active: Boolean(benefits.gift_active),
                    is_reached: giftEligible,
                    threshold_kopecks: giftThresholdKopecks,
                    current_kopecks: giftCurrentKopecks,
                    remaining_kopecks: giftRemainingKopecks,
                    is_preview: false,
                },
                complement: {
                    is_active: complementActive,
                    is_reached: entitledSets > 0,
                    rolls_per_set: rollsPerSet,
                    current_roll_count: rollCount,
                    entitled_set_count: entitledSets,
                    remaining_roll_count: remainingRollCount,
                },
            },
        },
        wizard: {
            can_confirm: canConfirm,
            missing_blocks: [...new Set(missingBlocks)],
            suggested_step: null,
        },
    };

    // Плейсхолдер телефона с quote не затирает локальный guestContact.
    if (!placeholderPhone) {
        snapshot.client = quote.client ?? null;
    }

    return snapshot;
}
