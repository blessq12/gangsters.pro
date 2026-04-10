<script setup>
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const {
    userStore,
    orderStore,
    cartItems,
    complimentaryPreviewItems,
    totalAmount,
    formatPrice,
    formatPhone,
    isGuestCheckout,
    goToPayment,
    handleConfirmOrder,
} = useCheckoutFlowContext();
</script>

<template>
    <div class="space-y-3 text-xs sm:text-sm text-slate-200">
        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400">
            Шаг 3 из 3 — Подтверждение
        </p>

        <div class="space-y-2 rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-3">
            <p class="text-[11px] font-semibold text-slate-300">
                Состав заказа
            </p>
            <ul class="space-y-1 text-xs">
                <li
                    v-for="item in cartItems"
                    :key="item.productId"
                    class="flex items-center justify-between gap-2"
                >
                    <span class="truncate text-slate-100">
                        {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                    </span>
                    <span class="shrink-0 text-slate-300">
                        {{ item.qty }} ×
                        {{ formatPrice(item.productSnapshot?.price) }} ₽
                    </span>
                </li>
            </ul>
            <div
                v-if="complimentaryPreviewItems.length"
                class="mt-2 rounded-xl border border-emerald-400/30 bg-emerald-950/20 px-2 py-2"
            >
                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-emerald-300">
                    Бесплатно добавим
                </p>
                <ul class="mt-1 space-y-1 text-[11px] text-emerald-100">
                    <li
                        v-for="item in complimentaryPreviewItems"
                        :key="`confirm-complimentary-${item.product_id}-${item.rule_id}`"
                        class="flex items-center justify-between gap-2"
                    >
                        <span class="truncate">{{ item.name }}</span>
                    <span class="shrink-0">{{ item.quantity }} шт · 0 ₽</span>
                    </li>
                </ul>
            </div>
            <div class="mt-2 flex items-center justify-between border-t border-white/5 pt-2 text-xs">
                <span class="text-slate-300/85">Итого</span>
                <span class="font-semibold text-amber-300">
                    {{ formatPrice(totalAmount) }} ₽
                </span>
            </div>
        </div>

        <div class="space-y-1 rounded-2xl bg-[rgba(255,255,255,0.02)] px-3 py-3">
            <p class="text-[11px] font-semibold text-slate-300">
                Данные клиента
            </p>
            <template v-if="isGuestCheckout">
                <p class="text-xs text-slate-200">
                    {{ orderStore.guestContact.name || "Без имени" }},
                    {{
                        orderStore.guestContact.phone
                            ? formatPhone(orderStore.guestContact.phone)
                            : "без телефона"
                    }}
                </p>
                <p
                    v-if="orderStore.guestContact.email"
                    class="text-[11px] text-slate-400"
                >
                    {{ orderStore.guestContact.email }}
                </p>
            </template>
            <template v-else>
                <p class="text-xs text-slate-200">
                    {{ userStore.profile.name || "Без имени" }},
                    {{
                        userStore.profile.phone
                            ? formatPhone(userStore.profile.phone)
                            : "без телефона"
                    }}
                </p>
                <p
                    v-if="userStore.profile.email"
                    class="text-[11px] text-slate-400"
                >
                    {{ userStore.profile.email }}
                </p>
            </template>
        </div>

        <div class="space-y-1 rounded-2xl bg-[rgba(255,255,255,0.02)] px-3 py-3">
            <p class="text-[11px] font-semibold text-slate-300">
                Доставка и оплата
            </p>
            <p class="text-xs text-slate-200">
                Адрес:
                <template v-if="orderStore.deliveryInfo.method === 'pickup'">
                    Самовывоз (адрес точки выдачи пришлём в подтверждении)
                </template>
                <template v-else>
                    <span v-if="isGuestCheckout && orderStore.deliveryInfo.address">
                        {{
                            [
                                orderStore.deliveryInfo.address.street,
                                orderStore.deliveryInfo.address.house &&
                                    `д. ${orderStore.deliveryInfo.address.house}`,
                                orderStore.deliveryInfo.address.apartment &&
                                    `кв. ${orderStore.deliveryInfo.address.apartment}`,
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
                        class="text-slate-400"
                    >
                        адрес не выбран
                    </span>
                </template>
            </p>
            <p class="text-xs text-slate-200">
                Оплата:
                {{
                    orderStore.paymentInfo.method === "cash"
                        ? "Наличными"
                        : orderStore.paymentInfo.method === "card"
                          ? "Банковская карта"
                          : orderStore.paymentInfo.method === "transfer"
                            ? "Перевод"
                            : "не выбрано"
                }}
                <span
                    v-if="orderStore.paymentInfo.method === 'cash' && orderStore.paymentInfo.changeFrom"
                    class="ml-1 text-slate-400"
                >
                    (сдача с {{ formatPrice(orderStore.paymentInfo.changeFrom) }} ₽)
                </span>
            </p>
            <p
                v-if="orderStore.customerComment"
                class="text-[11px] text-slate-400"
            >
                Комментарий: {{ orderStore.customerComment }}
            </p>
        </div>

        <div
            v-if="orderStore.error.create"
            class="rounded-2xl border border-red-500/40 bg-red-950/40 px-3 py-2 text-[11px] text-red-200"
        >
            {{ orderStore.error.create }}
        </div>

        <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
            <button
                type="button"
                class="underline-offset-2 hover:underline"
                @click="goToPayment"
            >
                Назад: оплата
            </button>
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-full bg-amber-400 px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(251,191,36,0.7)] transition hover:bg-amber-300 disabled:opacity-60"
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
