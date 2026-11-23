import { computed } from "vue";
import {
    formatKopecksToRub,
    roundRubles2,
} from "../../../platform/moneyFormat";
import { useUserStore } from "../../client/store/userStore";
import { useContentStore } from "../../content/store";
import {
    fetchCheckoutContextRequest,
    placeOrderRequest,
} from "../../../api/order";
import {
    buildClientPayload,
    buildDeliveryPayload,
    buildPaymentPayload,
    normalizeDeliveryComment,
    normalizePersonsCount,
} from "../domain/checkoutServerMappers";
import { formCheckout } from "../domain/formation/formCheckout.js";
import { giftCourierWeekdaysLabel } from "../domain/giftWeekdaysLabel";
import { adaptFormationToCheckoutSnapshot } from "../domain/normalizeOrderPreview";
import { useCheckoutStore } from "../store";

const HTTP_CONFLICT = 409;

export const CHECKOUT_LOADING_LABELS = Object.freeze({
    zoneCheck: "Проверяем адрес…",
    orderRecalc: "Считаем заказ…",
    orderSubmit: "Отправляем заказ…",
    addressSave: "Сохраняем…",
});

export const CHECKOUT_WIZARD_GROUPS = {
    cart: "Корзина",
    upsell: "Добавьте ещё",
    guest: "Как с тобой связаться",
    fulfillment: "Куда и как",
    confirm: "Почти готово",
    success: "Готово",
};

export const CHECKOUT_WAITER_LINES = Object.freeze({
    cart: "",
    upsell: "Удобно добавить к заказу сейчас — всё приедет вместе",
    guest: "Вход сохраняет контакты и адреса — следующее оформление быстрее",
    fulfillment: "Способ оплаты и получение заказа",
    confirm: "Проверьте состав, получение и оплату",
    success: "Заказ принят. Готовим",
});

export const CHECKOUT_STEP_HINTS = CHECKOUT_WAITER_LINES;

export const CHECKOUT_NAV_LABELS = Object.freeze({
    back: "Назад",
    cartPrimary: "Далее",
    next: "Далее",
    upsellPrimary: "Продолжить",
    upsellSkip: "Без дополнений",
    guestPrimary: "Продолжить как гость",
    confirm: "Отправить заказ",
    success: "В меню",
    authLink: "Уже есть аккаунт? Войти",
    authRegisterCta: "Войти или зарегистрироваться",
    authRegisterEyebrow: "Удобство сервиса",
    authRegisterPitch: "Контакты и адреса сохраняются для следующих заказов",
    authRegisterBenefits: Object.freeze([
        "Быстрее оформление без повторного ввода",
        "Адреса доставки под рукой",
        "История заказов в профиле",
    ]),
    editFulfillment: "Изменить доставку и оплату",
});

export const CHECKOUT_SESSION_KEY = "gangsters_order_draft_v1";

export const CHECKOUT_WIZARD_STEPS = [
    "cart",
    "upsell",
    "guest",
    "fulfillment",
    "confirm",
    "success",
];

export function readCheckoutSessionPayload() {
    if (typeof window === "undefined") {
        return null;
    }

    try {
        const raw = window.sessionStorage.getItem(CHECKOUT_SESSION_KEY);
        if (!raw) {
            return null;
        }
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === "object" ? parsed : null;
    } catch {
        return null;
    }
}

export function writeCheckoutSessionPayload(payload) {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.setItem(
        CHECKOUT_SESSION_KEY,
        JSON.stringify(payload),
    );
}

export function clearCheckoutSessionPayload() {
    if (typeof window === "undefined") {
        return;
    }

    window.sessionStorage.removeItem(CHECKOUT_SESSION_KEY);
}

/**
 * Миграция старых сессий: customerComment → deliveryInfo.comment.
 *
 * @param {Record<string, unknown>} forms
 * @returns {Record<string, unknown>}
 */
export function normalizeCheckoutSessionForms(forms) {
    if (!forms || typeof forms !== "object") {
        return {};
    }

    const normalized = { ...forms };
    const legacyComment =
        typeof normalized.customerComment === "string"
            ? normalized.customerComment.trim()
            : "";

    if (legacyComment) {
        const delivery =
            normalized.deliveryInfo &&
            typeof normalized.deliveryInfo === "object"
                ? { ...normalized.deliveryInfo }
                : {};
        const existing = String(delivery.comment || "").trim();

        if (!existing) {
            delivery.comment = legacyComment;
        } else if (!existing.includes(legacyComment)) {
            delivery.comment = `${existing}\n\n${legacyComment}`;
        }

        normalized.deliveryInfo = delivery;
    }

    delete normalized.customerComment;

    const deliveryInfo =
        normalized.deliveryInfo && typeof normalized.deliveryInfo === "object"
            ? { ...normalized.deliveryInfo }
            : {};
    deliveryInfo.persons = normalizePersonsCount(deliveryInfo.persons);
    deliveryInfo.comment = normalizeDeliveryComment(deliveryInfo.comment);
    normalized.deliveryInfo = deliveryInfo;

    return normalized;
}

export function buildCheckoutSessionSnapshot(store) {
    return {
        clientRequestId: store.clientRequestId,
        localCart: store.cartItems.filter((item) => !item.isSystem),
        complementSelections: store.complementSelections,
        complementEntitledSnapshot: store.complementEntitledSnapshot,
        forms: {
            deliveryInfo: store.deliveryInfo,
            paymentInfo: store.paymentInfo,
            guestContact: store.guestContact,
            promotions: store.promotions,
        },
    };
}

export function resolveClientRequestId() {
    if (typeof window === "undefined") {
        return crypto.randomUUID();
    }

    const saved = readCheckoutSessionPayload();
    if (saved?.clientRequestId) {
        return saved.clientRequestId;
    }

    const id = crypto.randomUUID();
    writeCheckoutSessionPayload({ clientRequestId: id });

    return id;
}

function ensureCheckoutSessionActive(store) {
    if (store.sessionReady) {
        return;
    }

    store.clientRequestId = resolveClientRequestId();
    store.sessionReady = true;
}

/**
 * Адрес профиля для preview/place: auth + courier + выбранный адрес.
 * Гость использует deliveryInfo.address из store.
 */
export function resolveCheckoutPreviewAddress(store) {
    if (store.deliveryInfo?.method !== "courier") {
        return null;
    }

    const isGuest = Boolean(
        store.guestContact?.name && store.guestContact?.phone,
    );
    if (isGuest) {
        return null;
    }

    const userStore = useUserStore();
    if (!userStore.token || userStore.selectedAddressId == null) {
        return null;
    }

    return userStore.selectedAddress ?? null;
}

function effectivePreviewAddress(store, selectedAddress) {
    if (selectedAddress != null) {
        return selectedAddress;
    }

    return resolveCheckoutPreviewAddress(store);
}

function resolveRegisteredClientId(store, options = {}) {
    if (options.clientId != null) {
        return Number(options.clientId);
    }

    if (store.formedClient?.client_id != null) {
        return Number(store.formedClient.client_id);
    }

    const userStore = useUserStore();
    if (userStore.token && userStore.profile?.id != null) {
        return Number(userStore.profile.id);
    }

    return null;
}

function resolveCoords(selectedAddress, store) {
    const source =
        selectedAddress ??
        (store.deliveryInfo?.address &&
        typeof store.deliveryInfo.address === "object"
            ? store.deliveryInfo.address
            : null);

    if (!source || typeof source !== "object") {
        return { latitude: null, longitude: null };
    }

    const latitudeRaw = source.latitude ?? source.lat;
    const longitudeRaw = source.longitude ?? source.lng;
    const latitude = latitudeRaw != null && latitudeRaw !== "" ? Number(latitudeRaw) : null;
    const longitude =
        longitudeRaw != null && longitudeRaw !== "" ? Number(longitudeRaw) : null;

    return {
        latitude: Number.isFinite(latitude) ? latitude : null,
        longitude: Number.isFinite(longitude) ? longitude : null,
    };
}

function buildComplementSelections(store) {
    const known = new Set(
        (store.context?.promotions?.complements ?? []).map((row) => Number(row.product_id)),
    );

    return Object.entries(store.complementSelections ?? {})
        .map(([productId, quantity]) => ({
            product_id: Number(productId),
            quantity: Math.max(0, Number(quantity) || 0),
        }))
        .filter((row) => known.has(row.product_id));
}

/**
 * Слепок намерения клиента (Memento): тот же body считает локальный движок и принимает POST /order.
 */
export function buildCheckoutIntent(
    store,
    selectedAddress = null,
    options = {},
) {
    const userLines = store.userItems.map((item) => ({
        product_id: item.productId,
        kind: item.kind === "set" ? "set" : "product",
        quantity: item.qty,
    }));

    const clientId = resolveRegisteredClientId(store, options);
    const isGuest = Boolean(
        options.isGuest ||
        (store.guestContact?.name && store.guestContact?.phone),
    );

    const userStore = useUserStore();
    const clientPayload = buildClientPayload(store, {
        clientId,
        isGuest: isGuest || clientId == null,
        profile:
            clientId != null && userStore.token ? userStore.profile : null,
    });

    const deliveryPayload = store.deliveryInfo.method
        ? buildDeliveryPayload(store, selectedAddress)
        : null;

    const paymentPayload = store.paymentInfo.method
        ? buildPaymentPayload(store)
        : null;

    const coords = resolveCoords(selectedAddress, store);
    const giftProductId = Number(store.promotions?.freeRollGiftProductId) || 0;

    return {
        branch_id: useContentStore().selectedBranchId,
        lines: userLines,
        delivery_method:
            deliveryPayload?.method ?? store.deliveryInfo.method ?? "courier",
        client: clientPayload,
        address: deliveryPayload?.address ?? null,
        delivery_comment: deliveryPayload?.comment,
        persons:
            deliveryPayload?.persons ??
            normalizePersonsCount(store.deliveryInfo?.persons),
        scheduled_at: deliveryPayload?.scheduled_at,
        payment_method: paymentPayload?.method ?? "cash",
        change_from_rubles: paymentPayload?.change_from_rubles,
        latitude: coords.latitude,
        longitude: coords.longitude,
        gift_product_id: giftProductId > 0 ? giftProductId : null,
        complement_selections: buildComplementSelections(store),
    };
}

/**
 * Контекст филиала грузится один раз на филиал; повторно — по 409 или смене филиала.
 */
export async function ensureCheckoutContext(store, { force = false } = {}) {
    const branchId = await useContentStore().ensureSelectedBranchId();
    if (branchId == null) {
        return null;
    }
    if (!force && store.context && store.context.branch_id === branchId) {
        return store.context;
    }

    store.flushing = true;
    try {
        store.setContext(await fetchCheckoutContextRequest(branchId));
        return store.context;
    } finally {
        store.flushing = false;
    }
}

/**
 * Локальный пересчёт корзины по правилам из контекста (без сети, кроме первой загрузки контекста).
 */
export async function recomputeCheckout(
    store,
    selectedAddress = null,
    options = {},
) {
    ensureCheckoutSessionActive(store);
    store.error = null;

    try {
        await ensureCheckoutContext(store);
    } catch (e) {
        store.error =
            e?.response?.data?.message || "Не удалось загрузить условия заказа.";
        return null;
    }
    if (!store.context) {
        return null;
    }

    const previewAddress = effectivePreviewAddress(store, selectedAddress);
    const intent = buildCheckoutIntent(store, previewAddress, options);
    const formation = formCheckout(store.context, intent);
    if (formation === null) {
        if (store.hasCartItems) {
            store.resetQuoteState();
            store.persistSession();
        }
        return null;
    }

    store.applyFormation(adaptFormationToCheckoutSnapshot(formation));
    return formation;
}

export async function bootstrapCheckoutStoreSession(store) {
    if (store.sessionReady) {
        return;
    }

    const saved = readCheckoutSessionPayload();
    if (saved?.localCart?.length) {
        store.restoreLocalCart(saved.localCart);
    }
    if (saved?.forms) {
        store.patchLocal(normalizeCheckoutSessionForms(saved.forms));
    }
    if (
        saved?.complementSelections
        && typeof saved.complementSelections === "object"
    ) {
        store.complementSelections = { ...saved.complementSelections };
    }
    if (saved?.complementEntitledSnapshot != null) {
        store.complementEntitledSnapshot =
            Number(saved.complementEntitledSnapshot) || 0;
    }

    store.clientRequestId = saved?.clientRequestId ?? resolveClientRequestId();
    store.sessionReady = true;

    if (store.hasCartItems) {
        await recomputeCheckout(store);
    }
}

export function buildLocalCartItem(product, qty, payload = null) {
    const productId = Number(product?.id);
    const quantity = Math.max(1, Number(qty) || 1);
    const unitRub = roundRubles2(
        Number(product?.price?.amount ?? product?.price) || 0,
    );
    const unitKopecks = Math.round(unitRub * 100);
    const lineKopecks = unitKopecks * quantity;

    return {
        lineKey: `user:${productId}`,
        origin: "user",
        isSystem: false,
        lineKind: "user",
        productId,
        kind: product?.kind === "set" ? "set" : "product",
        qty: quantity,
        productSnapshot: {
            id: productId,
            name: String(product?.name || ""),
            price: unitRub,
            kind: product?.kind === "set" ? "set" : "product",
        },
        pricing: {
            listUnitPriceKopecks: unitKopecks,
            finalUnitPriceKopecks: unitKopecks,
            lineTotalKopecks: lineKopecks,
        },
        payload,
    };
}

export function recalculateLocalCartTotals(store) {
    const userLines = store.userItems;
    const subtotalKopecks = userLines.reduce(
        (sum, item) => sum + (Number(item.pricing?.lineTotalKopecks) || 0),
        0,
    );
    store.itemsSubtotalRubles = roundRubles2(subtotalKopecks / 100);
    store.itemsTotalRubles = store.itemsSubtotalRubles;
}

export function upsertLocalCartLine(store, product, quantity, payload = null) {
    const productId = Number(product?.id);
    const nextQty = Math.max(0, Number(quantity) || 0);
    const without = store.cartItems.filter(
        (item) => !(item.productId === productId && !item.isSystem),
    );

    if (nextQty > 0) {
        without.push(buildLocalCartItem(product, nextQty, payload));
    }

    store.cartItems = without;
    recalculateLocalCartTotals(store);
    store.persistSession();
}

export async function setCheckoutPromotionGift(store, productId) {
    store.patchLocal({
        promotions: {
            freeRollGiftProductId:
                productId != null ? Number(productId) || null : null,
        },
    });
    store.persistSession();

    if (store.hasCartItems) {
        await recomputeCheckout(store);
    }
}

function checkoutError(message) {
    const error = new Error(message);
    error.response = { data: { message } };
    return error;
}

/**
 * Command: отправка слепка намерения. Сервер пересчитывает тем же OrderFormation;
 * на 409 подменяем контекст, пересчитываем корзину и показываем клиенту обновлённый заказ.
 */
export async function placeOrderOnServer(store, selectedAddress = null) {
    const previewAddress = effectivePreviewAddress(store, selectedAddress);
    const formation = await recomputeCheckout(store, previewAddress);
    if (formation === null) {
        throw checkoutError(store.error || "Корзина пуста.");
    }

    if (formation.delivery.method === "courier" && formation.delivery.in_zone !== true) {
        throw checkoutError(
            formation.delivery.in_zone === false
                ? "Доставка по этому адресу невозможна. Укажи адрес ближе или выбери самовывоз."
                : "Выбери адрес из подсказок — нужны координаты.",
        );
    }

    const body = {
        client_request_id: store.clientRequestId || resolveClientRequestId(),
        ...buildCheckoutIntent(store, previewAddress),
        rules_version: store.context.rules_version,
        expected_total_kopecks: formation.totals.grand_total,
    };

    store.flushing = true;
    store.error = null;
    try {
        const data = await placeOrderRequest(body);
        store.clearAfterCompleted();
        return { order: data };
    } catch (e) {
        const freshContext = e?.response?.data?.context;
        if (e?.response?.status === HTTP_CONFLICT && freshContext) {
            store.setContext(freshContext);
            await recomputeCheckout(store, previewAddress);
        }
        store.error =
            e?.response?.data?.message || "Не удалось оформить заказ.";
        throw e;
    } finally {
        store.flushing = false;
    }
}

export function persistCheckoutSession(payload) {
    writeCheckoutSessionPayload(payload);
}

/**
 * Жизненный цикл checkout-сессии:
 * запуск приложения → создание/восстановление draft → оформление → очистка.
 */
export async function bootstrapCheckoutSession() {
    const checkoutStore = useCheckoutStore();
    await checkoutStore.bootstrapSession();
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

/**
 * Единая read-model сессии оформления: корзина, pricing, benefits.
 */
export function useCheckoutSession() {
    const session = useCheckoutStore();

    const items = computed(() => session.cartItems);
    const userItems = computed(() => session.userItems);
    const systemItems = computed(() => session.systemItems);
    const totalAmount = computed(() =>
        session.hasDeliveryPricing
            ? session.grandTotalWithDelivery
            : session.cartTotalAmount,
    );
    const hasCartItems = computed(() => userItems.value.length > 0);
    const promoState = computed(() => session.promoState);
    const deliveryPricing = computed(() => session.deliveryPricing);
    const itemsTotalAmount = computed(() => session.itemsTotalAmount);
    const deliveryFeeAmount = computed(() => session.deliveryFeeAmount);
    const grandTotalWithDelivery = computed(
        () => session.grandTotalWithDelivery,
    );
    const isDeliveryFree = computed(() => session.isDeliveryFree);
    const hasDeliveryPricing = computed(() => session.hasDeliveryPricing);
    const benefitsProgress = computed(() => session.benefitsProgress);
    const hasBenefitsProgress = computed(() => session.hasBenefitsProgress);
    const totalItems = computed(() => session.cartTotalItems);
    const systemItemsCount = computed(() => session.cartSystemItemsCount);

    const delivery = computed(
        () => benefitsProgress.value?.delivery ?? emptyMoneyBenefit(),
    );
    const gift = computed(
        () => benefitsProgress.value?.gift ?? emptyMoneyBenefit(),
    );
    const hasActiveBenefits = computed(() =>
        Boolean(delivery.value.isActive || gift.value.isActive),
    );
    const canShowBenefitsBanner = computed(
        () =>
            totalItems.value > 0 &&
            hasBenefitsProgress.value &&
            hasActiveBenefits.value,
    );

    const deliveryProgressPercent = computed(() => {
        const threshold = Number(delivery.value.thresholdKopecks);
        const current = Number(delivery.value.currentKopecks);
        if (!Number.isFinite(threshold) || threshold <= 0) {
            return delivery.value.isReached ? 100 : 0;
        }
        return Math.min(
            100,
            Math.max(0, Math.round((current / threshold) * 100)),
        );
    });

    const giftProgressPercent = computed(() => {
        const threshold = Number(gift.value.thresholdKopecks);
        const current = Number(gift.value.currentKopecks);
        if (!Number.isFinite(threshold) || threshold <= 0) {
            return gift.value.isReached ? 100 : 0;
        }
        return Math.min(
            100,
            Math.max(0, Math.round((current / threshold) * 100)),
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
        if (gift.value.weekdayOk === false) {
            return giftCourierWeekdaysLabel(gift.value.allowedWeekdays);
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

    function quantityByProduct(productId) {
        return session.cartQuantityByProduct(productId);
    }

    return {
        session,
        items,
        userItems,
        systemItems,
        totalAmount,
        userTotalAmount: computed(() => session.cartUserTotalAmount),
        systemTotalAmount: computed(() => session.cartSystemTotalAmount),
        promoState,
        deliveryPricing,
        itemsTotalAmount,
        deliveryFeeAmount,
        grandTotalWithDelivery,
        isDeliveryFree,
        hasDeliveryPricing,
        benefitsProgress,
        hasBenefitsProgress,
        totalItems,
        systemItemsCount,
        hasCartItems,
        quantityByProduct,
        delivery,
        gift,
        deliveryBenefit: delivery,
        giftBenefit: gift,
        hasActiveBenefits,
        canShowBenefitsBanner,
        deliveryProgressPercent,
        giftProgressPercent,
        deliveryLabel,
        giftLabel,
        benefitLines,
        formatKopecksToRub,
        cartItems: items,
        userCartItems: userItems,
        systemCartItems: systemItems,
        benefits: {
            delivery,
            gift,
            hasBenefitsProgress,
            hasActiveBenefits,
            canShowBenefitsBanner,
            deliveryProgressPercent,
            giftProgressPercent,
            deliveryLabel,
            giftLabel,
            benefitLines,
            formatKopecksToRub,
        },
    };
}
