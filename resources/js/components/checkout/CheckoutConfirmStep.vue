<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const c = chk.cart;
const cf = chk.confirm;

const {
    userStore,
    checkoutState,
    checkoutStepMeta,
    goToPayment,
    handleConfirmOrder,
} = useCheckoutFlowContext();

const {
    checkoutIntent,
    orderStore,
    userCartItems,
    systemCartItems,
    totalAmount,
    userTotalAmount,
    systemTotalAmount,
    formatPrice,
    formatPhone,
    isGuestCheckout,
} = checkoutState;

function lineBadge(item) {
    const key = String(item?.lineKey || "");
    if (key.startsWith("gift:")) return "Подарок";
    if (key.startsWith("complement:")) return "Комплект";
    if (item?.isSystem) return "Авто";
    return null;
}

function unitPriceRub(item) {
    const kopecks = Number(item?.pricing?.finalUnitPriceKopecks);
    if (Number.isFinite(kopecks)) return kopecks / 100;
    return Number(item?.productSnapshot?.price) || 0;
}
</script>

<template>
    <div :class="s.flowBody">
        <p :class="s.stepKicker">
            Шаг {{ checkoutStepMeta.confirm.n }} из {{ checkoutStepMeta.confirm.total }} — Подтверждение
        </p>

        <div :class="cf.summaryCard">
            <p :class="s.headingCardMuted">
                Состав заказа
            </p>
            <ul :class="cf.orderList">
                <li
                    v-if="userCartItems.length"
                    :class="s.subsectionKickerXs"
                >
                    Вы добавили
                </li>
                <li
                    v-for="item in userCartItems"
                    :key="item.lineKey"
                    :class="cf.orderLineRow"
                >
                    <span :class="cf.orderLineTruncate">
                        {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                    </span>
                    <span :class="cf.orderLineMuted">
                        {{ item.qty }} × {{ formatPrice(unitPriceRub(item)) }} ₽
                    </span>
                </li>

                <li
                    v-if="systemCartItems.length"
                    :class="s.subsectionKickerXsSpaced"
                >
                    Добавлено автоматически
                </li>
                <li
                    v-for="item in systemCartItems"
                    :key="item.lineKey"
                    :class="cf.systemLineAccent"
                >
                    <span :class="cf.orderLineTruncate">
                        {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                        <span
                            v-if="lineBadge(item)"
                            :class="cf.badgeTiny"
                        >
                            • {{ lineBadge(item) }}
                        </span>
                    </span>
                    <span :class="cf.orderLineMuted">
                        {{ item.qty }} × {{ formatPrice(unitPriceRub(item)) }} ₽
                    </span>
                </li>
            </ul>
            <div :class="cf.totalsInset">
                <div :class="cf.orderLineRow">
                    <span :class="c.totalsLabelMuted">Товары</span>
                    <span :class="c.totalsValue">{{ formatPrice(userTotalAmount) }} ₽</span>
                </div>
                <div :class="cf.orderLineRow">
                    <span :class="c.totalsLabelMuted">Автодобавления</span>
                    <span :class="c.totalsValue">{{ formatPrice(systemTotalAmount) }} ₽</span>
                </div>
                <div :class="c.totalsDivider">
                    <span :class="c.totalsLabelStrong">Итого</span>
                    <span :class="c.grandTotal">
                        {{ formatPrice(totalAmount) }} ₽
                    </span>
                </div>
            </div>
        </div>

        <div :class="cf.blockMuted">
            <p :class="s.headingCardMuted">
                Данные клиента
            </p>
            <template v-if="isGuestCheckout">
                <p :class="s.textBodyXs">
                    {{ checkoutIntent.guestContact.name || "Без имени" }},
                    {{
                        checkoutIntent.guestContact.phone
                            ? formatPhone(checkoutIntent.guestContact.phone)
                            : "без телефона"
                    }}
                </p>
            </template>
            <template v-else>
                <p :class="s.textBodyXs">
                    {{ userStore.profile.name || "Без имени" }},
                    {{
                        userStore.profile.phone
                            ? formatPhone(userStore.profile.phone)
                            : "без телефона"
                    }}
                </p>
                <p
                    v-if="userStore.profile.email"
                    :class="s.textMutedLine"
                >
                    {{ userStore.profile.email }}
                </p>
            </template>
        </div>

        <div :class="cf.blockMuted">
            <p :class="s.headingCardMuted">
                Доставка и оплата
            </p>
            <p :class="s.textBodyXs">
                Адрес:
                <template v-if="checkoutIntent.deliveryInfo.method === 'pickup'">
                    Самовывоз (адрес точки выдачи пришлём в подтверждении)
                </template>
                <template v-else>
                    <span v-if="isGuestCheckout && checkoutIntent.deliveryInfo.address">
                        {{
                            [
                                checkoutIntent.deliveryInfo.address.street,
                                checkoutIntent.deliveryInfo.address.house &&
                                    `д. ${checkoutIntent.deliveryInfo.address.house}`,
                                checkoutIntent.deliveryInfo.address.apartment &&
                                    `кв. ${checkoutIntent.deliveryInfo.address.apartment}`,
                            ]
                                .filter(Boolean)
                                .join(", ")
                        }}
                    </span>
                    <span v-else-if="userStore.selectedAddress">
                        {{
                            [
                                userStore.selectedAddress.street,
                                userStore.selectedAddress.house &&
                                    `д. ${userStore.selectedAddress.house}`,
                                userStore.selectedAddress.apartment &&
                                    `кв. ${userStore.selectedAddress.apartment}`,
                            ]
                                .filter(Boolean)
                                .join(", ")
                        }}
                    </span>
                    <span
                        v-else
                        :class="s.textMutedLine"
                    >
                        адрес не выбран
                    </span>
                </template>
            </p>
            <p :class="s.textBodyXs">
                Оплата:
                {{
                    checkoutIntent.paymentInfo.method === "cash"
                        ? "Наличными"
                        : checkoutIntent.paymentInfo.method === "card"
                          ? "Банковская карта"
                          : checkoutIntent.paymentInfo.method === "transfer"
                            ? "Перевод"
                            : "не выбрано"
                }}
                <span
                    v-if="checkoutIntent.paymentInfo.method === 'cash' && checkoutIntent.paymentInfo.changeFrom"
                    :class="cf.mutedInline"
                >
                    (сдача с {{ formatPrice(checkoutIntent.paymentInfo.changeFrom) }} ₽)
                </span>
            </p>
            <p
                v-if="checkoutIntent.customerComment"
                :class="s.textMutedLine"
            >
                Комментарий: {{ checkoutIntent.customerComment }}
            </p>
        </div>

        <div
            v-if="orderStore.error.create"
            :class="s.errorBanner"
        >
            {{ orderStore.error.create }}
        </div>

        <div :class="s.navFooterRow">
            <button
                type="button"
                :class="s.linkUnderline"
                @click="goToPayment"
            >
                Назад: оплата
            </button>
            <button
                type="button"
                :class="s.btnPrimarySmBusy"
                :disabled="orderStore.loading.create"
                @click="handleConfirmOrder"
            >
                <span v-if="orderStore.loading.create">
                    Оформляем...
                </span>
                <span v-else>
                    Подтвердить заказ
                </span>
            </button>
        </div>
    </div>
</template>
