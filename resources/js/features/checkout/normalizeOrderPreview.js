import { normalizeBenefitsProgress } from "./normalizeBenefitsProgress";
import { roundRubles2 } from "../../utils/moneyFormat";

/**
 * @param {object|null|undefined} line
 */
function normalizePreviewLine(line) {
    if (!line || typeof line !== "object") {
        return null;
    }

    const productId = Number(line.product_id) || 0;
    const quantity = Number(line.quantity) || 0;
    if (productId <= 0 || quantity <= 0) {
        return null;
    }

    return {
        productId,
        name: line.name ? String(line.name) : `Товар #${productId}`,
        quantity,
        priceRubles: roundRubles2(Number(line.price_rubles) || 0),
        isFree: Boolean(line.is_free),
    };
}

/**
 * @param {object|null|undefined} giftSummary
 */
function normalizeGiftSummary(giftSummary) {
    if (!giftSummary || typeof giftSummary !== "object") {
        return null;
    }

    const productId = Number(giftSummary.product_id) || 0;
    if (productId <= 0) {
        return null;
    }

    return {
        productId,
        name: giftSummary.name ? String(giftSummary.name) : `Товар #${productId}`,
        quantity: Number(giftSummary.quantity) || 1,
    };
}

/**
 * @param {object|null|undefined} giftCta
 */
function normalizeGiftCta(giftCta) {
    if (!giftCta || typeof giftCta !== "object") {
        return null;
    }

    const candidateItems = Array.isArray(giftCta.candidate_items)
        ? giftCta.candidate_items
              .map((item) => {
                  if (!item || typeof item !== "object") {
                      return null;
                  }

                  const id = Number(item.id) || 0;
                  if (id <= 0) {
                      return null;
                  }

                  return {
                      id,
                      name: item.name ? String(item.name) : `Товар #${id}`,
                      priceRub: Number(item.price_rub) || 0,
                      imageUrl: item.image_url ? String(item.image_url) : null,
                  };
              })
              .filter(Boolean)
        : [];

    return {
        eligible: Boolean(giftCta.eligible),
        selectedProductId: Number(giftCta.selected_product_id) || null,
        candidateItems,
    };
}

/**
 * @param {object|null|undefined} totals
 */
function normalizeTotals(totals) {
    if (!totals || typeof totals !== "object") {
        return {
            itemsTotalRubles: 0,
            deliveryFeeRubles: null,
            baseDeliveryFeeRubles: null,
            outsideZoneSurchargeRubles: null,
            grandTotalRubles: 0,
            isDeliveryFree: false,
            isDeliveryPreview: false,
            inZone: null,
        };
    }

    const itemsTotalRubles = roundRubles2(Number(totals.items_total_rubles) || 0);
    const deliveryFeeRubles =
        totals.delivery_fee_rubles != null
            ? roundRubles2(Number(totals.delivery_fee_rubles))
            : null;
    const baseDeliveryFeeRubles =
        totals.base_delivery_fee_rubles != null
            ? roundRubles2(Number(totals.base_delivery_fee_rubles))
            : null;
    const outsideZoneSurchargeRubles =
        totals.outside_zone_surcharge_rubles != null
            ? roundRubles2(Number(totals.outside_zone_surcharge_rubles))
            : null;
    const grandTotalRubles = roundRubles2(
        Number(totals.grand_total_rubles ?? totals.items_total_rubles) || 0,
    );

    return {
        itemsTotalRubles,
        deliveryFeeRubles,
        baseDeliveryFeeRubles,
        outsideZoneSurchargeRubles,
        grandTotalRubles,
        isDeliveryFree: Boolean(totals.is_delivery_free),
        isDeliveryPreview: Boolean(totals.is_delivery_preview),
        inZone:
            totals.in_zone === true
                ? true
                : totals.in_zone === false
                  ? false
                  : null,
    };
}

/**
 * @param {object|null|undefined} orderPreview
 */
export function normalizeOrderPreview(orderPreview) {
    if (!orderPreview || typeof orderPreview !== "object") {
        return null;
    }

    const complementLines = Array.isArray(orderPreview.complement_lines)
        ? orderPreview.complement_lines.map(normalizePreviewLine).filter(Boolean)
        : [];
    const autoLines = Array.isArray(orderPreview.auto_lines)
        ? orderPreview.auto_lines.map(normalizePreviewLine).filter(Boolean)
        : [];
    const benefits = normalizeBenefitsProgress({
        delivery: orderPreview.benefits?.delivery,
        gift: orderPreview.benefits?.gift,
        complement: null,
    }) ?? {
        delivery: null,
        gift: null,
        complement: null,
    };

    return {
        complementLines,
        autoLines,
        giftSummary: normalizeGiftSummary(orderPreview.gift_summary),
        giftCta: normalizeGiftCta(orderPreview.gift_cta),
        totals: normalizeTotals(orderPreview.totals),
        benefits: {
            delivery: benefits.delivery,
            gift: benefits.gift,
        },
    };
}
