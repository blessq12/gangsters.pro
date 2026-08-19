import { computed, unref } from "vue";
import { storeToRefs } from "pinia";
import { useCheckoutStore } from "../store";
import { formatKopecksToRub } from "../../../platform/moneyFormat";
import {
    selectedGiftCartLine,
    resolveSelectedGiftSummary,
} from "../domain/normalizeCheckoutCart";
import { useCheckoutSession } from "./session";
import { useCheckoutFlowContext } from "./flowContext";

export function useCheckoutNavTotal() {
    const { checkoutState } = useCheckoutFlowContext();
    const { formatPrice } = checkoutState;
    const { displayGrandTotalRubles, hasCartItems } = useOrderPreview();
    const navTotalLabel = computed(() => {
        if (!unref(hasCartItems)) return "";
        const rubles = unref(displayGrandTotalRubles);
        return Number.isFinite(rubles) ? `${formatPrice(rubles)} ₽` : "";
    });
    return { navTotalLabel };
}

export function useCheckoutBenefitVisibility() {
    const { checkoutState, userStore } = useCheckoutFlowContext();
    const { checkoutIntent, isGuestCheckout } = checkoutState;

    const isDeliveryDataFilled = computed(() => {
        const intent = checkoutIntent?.value ?? checkoutIntent;
        if (!intent || typeof intent !== "object") {
            return false;
        }

        if (intent.serverDelivery?.method) {
            return true;
        }

        const method = intent.deliveryInfo?.method;
        if (!method) {
            return false;
        }

        if (method === "pickup") {
            return true;
        }

        const guestFlow = isGuestCheckout?.value ?? isGuestCheckout;
        if (guestFlow) {
            const address = intent.deliveryInfo?.address;
            return (
                String(address?.street || "").trim() !== "" &&
                String(address?.house || "").trim() !== ""
            );
        }

        return Boolean(userStore.selectedAddress);
    });

    const showGiftProgress = computed(() => {
        const intent = checkoutIntent?.value ?? checkoutIntent;
        if (!intent || typeof intent !== "object") {
            return false;
        }

        return Boolean(intent.serverDelivery?.method);
    });

    return {
        isDeliveryDataFilled,
        showGiftProgress,
    };
}

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

function rollWord(count) {
    const n = Math.abs(Number(count)) || 0;
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod100 >= 11 && mod100 <= 14) {
        return "роллов";
    }
    if (mod10 === 1) {
        return "ролл";
    }
    if (mod10 >= 2 && mod10 <= 4) {
        return "ролла";
    }

    return "роллов";
}

/**
 * Read-model превью заказа из order_preview API + benefits_progress store.
 */
export function useOrderPreview() {
    const checkoutStore = useCheckoutStore();
    const { orderPreview, hasCartItems, benefitsProgress, flushing } =
        storeToRefs(checkoutStore);
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
                baseDeliveryFeeRubles: null,
                outsideZoneSurchargeRubles: null,
                grandTotalRubles: 0,
                isDeliveryFree: false,
                isDeliveryPreview: false,
                inZone: null,
            },
    );

    const delivery = computed(
        () =>
            preview.value?.benefits?.delivery ??
            benefitsProgress.value?.delivery ??
            emptyMoneyBenefit(),
    );
    const gift = computed(
        () =>
            preview.value?.benefits?.gift ??
            benefitsProgress.value?.gift ??
            emptyMoneyBenefit(),
    );
    const complement = computed(
        () =>
            preview.value?.benefits?.complement ??
            benefitsProgress.value?.complement ??
            emptyComplementBenefit(),
    );

    const hasComplementLines = computed(() => complementLines.value.length > 0);
    const hasRollsInCart = computed(
        () => (Number(complement.value.currentRollCount) || 0) > 0,
    );
    const hasAutoLines = computed(() => autoLines.value.length > 0);
    const hasDeliveryPricing = computed(
        () =>
            !totals.value.isDeliveryPreview
            && totals.value.deliveryFeeRubles != null,
    );
    const showOutsideZoneSurcharge = computed(
        () =>
            totals.value.inZone === false
            && (totals.value.outsideZoneSurchargeRubles ?? 0) > 0,
    );
    const showBaseDeliveryFee = computed(() => {
        if (!hasDeliveryPricing.value) {
            return false;
        }

        if (showOutsideZoneSurcharge.value) {
            return (totals.value.baseDeliveryFeeRubles ?? 0) > 0;
        }

        return true;
    });
    const baseDeliveryFeeRubles = computed(() => {
        if (showOutsideZoneSurcharge.value) {
            return totals.value.baseDeliveryFeeRubles ?? 0;
        }

        return totals.value.deliveryFeeRubles ?? 0;
    });
    const isBaseDeliveryFree = computed(() => {
        if (showOutsideZoneSurcharge.value) {
            return (totals.value.baseDeliveryFeeRubles ?? 0) === 0;
        }

        return totals.value.isDeliveryFree;
    });
    const displayGrandTotalRubles = computed(() => totals.value.grandTotalRubles);

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
        const currentRollCount = Number(complement.value.currentRollCount) || 0;
        const remaining = Number(complement.value.remainingRollCount) || 0;

        if (!Number.isFinite(rollsPerSet) || rollsPerSet <= 0) {
            return complement.value.isReached ? 100 : 0;
        }

        if (currentRollCount <= 0) {
            return 0;
        }

        if (remaining <= 0) {
            return 100;
        }

        return Math.min(
            100,
            Math.max(
                0,
                Math.round(((rollsPerSet - remaining) / rollsPerSet) * 100),
            ),
        );
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

    const complementLabel = computed(() => {
        if (!complement.value.isActive) {
            return null;
        }

        const rollsPerSet = Number(complement.value.rollsPerSet) || 2;

        if (complement.value.isReached && complement.value.entitledSetCount > 0) {
            const sets = complement.value.entitledSetCount;
            return `Комплект добавлен · ${sets} ${sets === 1 ? "набор" : "набора"}`;
        }

        const remaining = Number(complement.value.remainingRollCount) || rollsPerSet;

        return `Ещё ${remaining} ${rollWord(remaining)} до комплекта (${rollsPerSet} ролла = 1 комплект, неполная пара тоже считается)`;
    });

    const inZoneLabel = computed(() => {
        if (totals.value.isDeliveryPreview) {
            return null;
        }

        if (totals.value.inZone === true) {
            return "Адрес в зоне доставки";
        }

        if (totals.value.inZone === false) {
            return "Адрес вне зоны — доплата за отдалённый район";
        }

        return null;
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

    const showComplementProgress = computed(
        () =>
            complement.value.isActive
            && Boolean(complementLabel.value)
            && hasRollsInCart.value,
    );

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
        complement,
        hasComplementLines,
        hasRollsInCart,
        hasAutoLines,
        hasDeliveryPricing,
        showOutsideZoneSurcharge,
        showBaseDeliveryFee,
        baseDeliveryFeeRubles,
        isBaseDeliveryFree,
        displayGrandTotalRubles,
        hasCartItems,
        deliveryProgressPercent,
        giftProgressPercent,
        complementProgressPercent,
        deliveryLabel,
        giftLabel,
        complementLabel,
        inZoneLabel,
        canShowBenefits,
        showComplementProgress,
        showGiftProgress,
        isGiftEligible,
        hasGiftSelected,
        giftCtaLabel,
        giftCandidates,
        selectedGiftName,
        previewLoading: flushing,
    };
}

/**
 * Ряды комплекта: free qty из quote + каталог для докупки.
 *
 * Правило бэка: entitledSets = ceil(rollCount / rollsPerSet), по умолчанию
 * rollsPerSet = 2 → 1–2 ролла = 1 набор, 3–4 = 2, 5–6 = 3 (freeQty на каждый комплектный товар).
 * Каталог для докупки подключать только при entitledSets >= 1.
 *
 * @param {unknown} complementLines
 * @param {unknown} complementProducts
 * @param {{ includeCatalogProducts?: boolean }} [options]
 * @returns {Array<{
 *   id: number,
 *   name: string,
 *   freeQty: number,
 *   product: object|null,
 * }>}
 */
export function buildComplementOfferRows(
    complementLines,
    complementProducts,
    options = {},
) {
    const freeById = new Map();
    const lines = Array.isArray(complementLines) ? complementLines : [];

    for (const line of lines) {
        const id = Number(line?.productId);
        const qty = Number(line?.quantity) || 0;
        if (!Number.isFinite(id) || id < 1 || qty < 1) continue;

        const prev = freeById.get(id);
        const name = String(line?.name || "").trim();
        freeById.set(id, {
            freeQty: (prev?.freeQty || 0) + qty,
            name: name || prev?.name || `Товар #${id}`,
        });
    }

    const includeCatalogProducts = options.includeCatalogProducts === true;
    const products =
        includeCatalogProducts && Array.isArray(complementProducts)
            ? complementProducts
            : [];
    const productById = new Map();

    for (const product of products) {
        const id = Number(product?.id);
        if (!Number.isFinite(id) || id < 1) continue;
        productById.set(id, product);
    }

    const ids = new Set([...freeById.keys(), ...productById.keys()]);
    const rows = [];

    for (const id of ids) {
        const free = freeById.get(id);
        const product = productById.get(id) ?? null;
        const name =
            String(product?.name || "").trim()
            || free?.name
            || `Товар #${id}`;

        rows.push({
            id,
            name,
            freeQty: free?.freeQty || 0,
            product,
        });
    }

    return rows.sort((a, b) => a.name.localeCompare(b.name, "ru"));
}

/**
 * @param {{ eligible?: boolean, phase?: string|null, selectedProductId?: number|null }|null|undefined} giftCta
 */
export function isGiftSelectionRequired(giftCta) {
    if (!giftCta || typeof giftCta !== "object" || !giftCta.eligible) {
        return false;
    }

    if (Number(giftCta.selectedProductId) > 0 || giftCta.phase === "selected") {
        return false;
    }

    return true;
}

/**
 * @param {{ gift_promotion?: { eligible?: boolean, phase?: string, selected_product_id?: number|null } }|null|undefined} promoState
 */
export function isGiftSelectionRequiredFromPromoState(promoState) {
    const giftPromotion = promoState?.gift_promotion;
    if (!giftPromotion || typeof giftPromotion !== "object" || giftPromotion.eligible !== true) {
        return false;
    }

    if (
        Number(giftPromotion.selected_product_id) > 0
        || giftPromotion.phase === "selected"
    ) {
        return false;
    }

    return true;
}

/**
 * @param {{
 *   giftCta?: object|null,
 *   promoState?: object|null,
 *   wizardMissingBlocks?: string[],
 *   cartItems?: object[],
 *   giftSummary?: { productId?: number|null }|null,
 * }} input
 */
export function resolveGiftSelectionRequired({
    giftCta = null,
    promoState = null,
    wizardMissingBlocks = [],
    cartItems = [],
    giftSummary = null,
}) {
    if (selectedGiftCartLine(cartItems)) {
        return false;
    }

    if (Number(giftSummary?.productId) > 0) {
        return false;
    }

    const selectedFromCta = Number(giftCta?.selectedProductId) || 0;
    const selectedFromPromo = Number(promoState?.gift_promotion?.selected_product_id) || 0;
    if (selectedFromCta > 0 || selectedFromPromo > 0) {
        return false;
    }

    if (giftCta?.phase === "selected" || promoState?.gift_promotion?.phase === "selected") {
        return false;
    }

    if (wizardMissingBlocks.includes("gift")) {
        return true;
    }

    return isGiftSelectionRequired(giftCta) || isGiftSelectionRequiredFromPromoState(promoState);
}

function resolveGiftNameFromPromo(promoState, productId) {
    const giftPromotion = promoState?.gift_promotion;
    const candidateItems = Array.isArray(giftPromotion?.candidate_items)
        ? giftPromotion.candidate_items
        : [];
    const candidate = candidateItems.find((item) => Number(item?.id) === productId);

    return candidate?.name ? String(candidate.name) : `Товар #${productId}`;
}

export function useSelectedGiftSummary() {
    const checkoutSession = useCheckoutSession();
    const checkoutStore = useCheckoutStore();

    return computed(() => {
        const promoState = checkoutSession.promoState.value;
        const cartItems = checkoutSession.items.value;

        const fromCartOrPromo = resolveSelectedGiftSummary({
            cartItems,
            promoState,
        });
        if (fromCartOrPromo) {
            return fromCartOrPromo;
        }

        const productId = Number(checkoutStore.promotions?.freeRollGiftProductId) || 0;
        if (productId <= 0) {
            return null;
        }

        return {
            productId,
            name: resolveGiftNameFromPromo(promoState, productId),
            qty: 1,
        };
    });
}

/**
 * Upsell-шаг: есть хотя бы один товар/набор в сопутствующих категориях.
 *
 * @param {unknown} accompanyingCategories
 */
export function isCheckoutUpsellStepAvailable(accompanyingCategories) {
    const nodes = Array.isArray(accompanyingCategories)
        ? accompanyingCategories
        : [];

    return nodes.some(
        (entry) =>
            Array.isArray(entry?.products) && entry.products.length > 0,
    );
}