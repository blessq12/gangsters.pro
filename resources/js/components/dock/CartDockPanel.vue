<script setup>
import { computed, ref } from "vue";
import { useUserStore } from "../../stores/userStore";
import { useOrderStore } from "../../stores/orderStore";
import ClientLoginForm from "../client/ClientLoginForm.vue";
import ClientRegisterForm from "../client/ClientRegisterForm.vue";

const userStore = useUserStore();
const orderStore = useOrderStore();

orderStore.initFromStorage();

const cartItems = computed(() => userStore.cartItems);
const totalAmount = computed(() => userStore.cartTotalAmount);
const isAuthenticated = computed(
    () => !!userStore.token && !!userStore.profile.id,
);

const activeStep = ref("cart"); // cart | auth | delivery | payment | confirm | success
const authTab = ref("login"); // login | register

const newAddressForm = ref({
    title: "",
    street: "",
    house: "",
    entrance: "",
    apartment: "",
    comment: "",
    make_default: true,
});
const newAddressLoading = ref(false);
const newAddressError = ref("");
const isNewAddressOpen = ref(false);
const deliveryStepError = ref("");
const paymentStepError = ref("");

const hasCartItems = computed(() => cartItems.value.length > 0);

const formatPrice = (value) =>
    new Intl.NumberFormat("ru-RU").format(Number(value) || 0);

function formatPhone(raw) {
    if (!raw) return "";
    const digits = String(raw).replace(/\D/g, "");
    // берём последние 10 цифр как федеральный номер
    const tail = digits.slice(-10);
    if (tail.length !== 10) {
        return raw;
    }
    const part1 = tail.slice(0, 3);
    const part2 = tail.slice(3, 6);
    const part3 = tail.slice(6, 8);
    const part4 = tail.slice(8, 10);
    return `+7 (${part1}) ${part2}-${part3}-${part4}`;
}

function syncCartToOrderStore() {
    orderStore.setCartItems(cartItems.value);
}

function handleStartCheckout() {
    if (!hasCartItems.value) return;
    syncCartToOrderStore();
    // дефолты выбора доставки и оплаты на шаге оформления
    if (!orderStore.deliveryInfo.method) {
        orderStore.setDeliveryInfo({ method: "courier" });
    }
    if (!orderStore.paymentInfo.method) {
        orderStore.setPaymentInfo({ method: "card" });
    }
    if (!isAuthenticated.value) {
        activeStep.value = "auth";
    } else {
        activeStep.value = "delivery";
    }
}

function handleAuthCompleted() {
    if (!hasCartItems.value) {
        activeStep.value = "cart";
        return;
    }
    syncCartToOrderStore();
    activeStep.value = "delivery";
}

function goToCart() {
    activeStep.value = "cart";
}

function goToDelivery() {
    if (!hasCartItems.value) {
        activeStep.value = "cart";
        return;
    }
    activeStep.value = "delivery";
}

function goToPayment() {
    deliveryStepError.value = "";

    if (!orderStore.deliveryInfo.method) {
        deliveryStepError.value = "Выбери способ доставки.";
        return;
    }

    if (
        orderStore.deliveryInfo.method === "courier" &&
        !userStore.selectedAddress
    ) {
        deliveryStepError.value = "Выбери адрес доставки или добавь новый.";
        return;
    }

    activeStep.value = "payment";
}

function goToConfirm() {
    paymentStepError.value = "";

    if (!orderStore.paymentInfo.method) {
        paymentStepError.value = "Выбери способ оплаты.";
        return;
    }

    activeStep.value = "confirm";
}

function goToSuccess() {
    activeStep.value = "success";
}

function handlePlaceOrderSuccess() {
    // очищаем корзину пользователя, чтобы не висели старые позиции
    const ids = cartItems.value.map((item) => item.productId);
    ids.forEach((id) => userStore.removeFromCart(id));
    goToSuccess();
}

async function handleConfirmOrder() {
    if (!hasCartItems.value) return;

    // финальная защита на случай обхода шагов
    if (
        !orderStore.deliveryInfo.method ||
        (orderStore.deliveryInfo.method === "courier" &&
            !userStore.selectedAddress) ||
        !orderStore.paymentInfo.method
    ) {
        return;
    }
    try {
        const client = userStore.profile;
        await orderStore.createOrder(client, userStore.selectedAddress);
        handlePlaceOrderSuccess();
    } catch (e) {
        // ошибка уже записана в orderStore.error.create
    }
}

async function handleCreateAddress() {
    newAddressError.value = "";

    if (!newAddressForm.value.street || !newAddressForm.value.house) {
        newAddressError.value = "Укажи улицу и дом";
        return;
    }

    newAddressLoading.value = true;

    try {
        await userStore.addClientAddress({
            title: newAddressForm.value.title || null,
            street: newAddressForm.value.street,
            house: newAddressForm.value.house,
            entrance: newAddressForm.value.entrance || null,
            apartment: newAddressForm.value.apartment || null,
            comment: newAddressForm.value.comment || null,
            make_default: newAddressForm.value.make_default,
        });

        newAddressForm.value = {
            title: "",
            street: "",
            house: "",
            entrance: "",
            apartment: "",
            comment: "",
            make_default: true,
        };
    } catch (e) {
        console.error(e);
        newAddressError.value =
            e?.response?.data?.message ||
            "Не удалось сохранить адрес. Попробуй ещё раз.";
    } finally {
        newAddressLoading.value = false;
    }
}
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 sm:px-6 lg:px-8 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
    >
        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm sm:text-base font-semibold text-slate-50">
                    Корзина
                </p>
                <div
                    class="flex h-8 items-center rounded-full bg-black/70 px-3 text-xs text-slate-200"
                >
                    {{ userStore.cartTotalItems }} шт
                </div>
            </div>

            <!-- Шаг: содержимое корзины -->
            <template v-if="activeStep === 'cart'">
                <div
                    v-if="!cartItems.length"
                    class="rounded-2xl bg-[rgba(255,255,255,0.03)] px-4 py-5 text-sm text-slate-300"
                >
                    Корзина пока пустая. Добавь пару вкусных позиций, и тут станет веселее.
                </div>

                <ul
                    v-else
                    class="space-y-2 text-xs sm:text-sm text-slate-200"
                >
                    <li
                        v-for="item in cartItems"
                        :key="item.productId"
                        class="flex items-center justify-between gap-3 rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-2"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-100">
                                {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                            </p>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                {{ formatPrice(item.productSnapshot?.price) }} ₽ за шт
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div
                                class="inline-flex items-center justify-between rounded-full border border-amber-400/60 bg-black/70 px-2 py-1 text-xs text-slate-50"
                            >
                                <button
                                    type="button"
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-black/70 text-[14px]"
                                    @click="userStore.decrementCart(item.productId)"
                                >
                                    –
                                </button>
                                <span class="px-2 font-semibold">
                                    {{ item.qty }}
                                </span>
                                <button
                                    type="button"
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-black/70 text-[14px]"
                                    @click="userStore.incrementCart(item.productId)"
                                >
                                    +
                                </button>
                            </div>

                            <button
                                type="button"
                                class="shrink-0 text-[11px] text-slate-400 transition-colors hover:text-red-400"
                                @click="userStore.removeFromCart(item.productId)"
                            >
                                Убрать
                            </button>
                        </div>
                    </li>
                </ul>

                <div
                    v-if="cartItems.length"
                    class="mt-3 flex items-center justify-between text-xs sm:text-sm"
                >
                    <span class="text-slate-300/85">Итого</span>
                    <span class="font-semibold text-amber-300">
                        {{ formatPrice(totalAmount) }} ₽
                    </span>
                </div>

                <div
                    v-if="cartItems.length"
                    class="mt-3"
                >
                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center rounded-full bg-amber-400 px-4 py-2 text-xs font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.7)] transition hover:bg-amber-300"
                        @click="handleStartCheckout"
                    >
                        Перейти к оформлению
                    </button>
                </div>
            </template>

            <!-- Шаг: авторизация -->
            <template v-else-if="activeStep === 'auth'">
                <div class="space-y-3">
                    <p class="text-xs text-slate-300">
                        Для оформления заказа нужен личный кабинет. Войди или зарегистрируйся —
                        это займёт минуту.
                    </p>

                    <div class="flex gap-2 text-[11px] font-medium">
                        <button
                            type="button"
                            class="rounded-full px-3 py-1 transition"
                            :class="
                                authTab === 'login'
                                    ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                                    : 'bg-white/5 text-slate-200 hover:bg-white/10'
                            "
                            @click="authTab = 'login'"
                        >
                            Вход
                        </button>
                        <button
                            type="button"
                            class="rounded-full px-3 py-1 transition"
                            :class="
                                authTab === 'register'
                                    ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                                    : 'bg-white/5 text-slate-200 hover:bg-white/10'
                            "
                            @click="authTab = 'register'"
                        >
                            Регистрация
                        </button>
                    </div>

                    <div class="space-y-3">
                        <ClientLoginForm
                            v-if="authTab === 'login'"
                            @logged-in="handleAuthCompleted"
                        />

                        <ClientRegisterForm
                            v-else
                            @registered="handleAuthCompleted"
                        />
                    </div>

                    <div class="mt-2 flex justify-between text-[11px] text-slate-400">
                        <button
                            type="button"
                            class="underline-offset-2 hover:underline"
                            @click="goToCart"
                        >
                            Вернуться к корзине
                        </button>
                    </div>
                </div>
            </template>

            <!-- Шаг: доставка -->
            <template v-else-if="activeStep === 'delivery'">
                <div class="space-y-3 text-xs sm:text-sm text-slate-200">
                    <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400">
                        Шаг 1 из 3 — Доставка
                    </p>

                    <div class="space-y-2">
                        <p class="text-xs font-semibold text-slate-100">
                            Способ доставки
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="method in ['courier', 'pickup']"
                                :key="method"
                                type="button"
                                class="rounded-full px-3 py-1 text-[11px] transition"
                                :class="
                                    orderStore.deliveryInfo.method === method
                                        ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                                        : 'bg-white/5 text-slate-200 hover:bg-white/10'
                                "
                                @click="orderStore.setDeliveryInfo({ method })"
                            >
                                {{ method === "courier" ? "Курьер" : "Самовывоз" }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="orderStore.deliveryInfo.method !== 'pickup'"
                        class="space-y-2"
                    >
                        <p class="text-xs font-semibold text-slate-100">
                            Выбери адрес доставки
                        </p>
                        <template v-if="orderStore.deliveryInfo.method !== 'pickup'">
                            <div
                                v-if="!userStore.addresses.length"
                                class="rounded-2xl border border-dashed border-slate-600/60 bg-black/40 px-4 py-3 text-[11px] text-slate-300"
                            >
                                Адресов пока нет. Добавь/отредактируй адреса в профиле — мы подтянем их
                                сюда автоматически.
                            </div>
                            <ul
                                v-else
                                class="space-y-2"
                            >
                                <li
                                    v-for="address in userStore.addresses"
                                    :key="address.id"
                                    class="flex items-center gap-2 rounded-2xl border border-white/10 bg-black/40 px-3 py-2"
                                >
                                    <input
                                        :id="`addr-${address.id}`"
                                        type="radio"
                                        class="h-4 w-4 rounded-full border-slate-400 text-amber-400 focus:ring-amber-400"
                                        :checked="userStore.selectedAddressId === address.id"
                                        @change="userStore.selectAddress(address.id)"
                                    />
                                    <label
                                        :for="`addr-${address.id}`"
                                        class="flex-1 cursor-pointer text-xs text-slate-200"
                                    >
                                        <span class="block font-medium text-slate-100">
                                            {{
                                                address.title ||
                                                address.label ||
                                                `Адрес #${address.id}`
                                            }}
                                        </span>
                                        <span class="block text-[11px] text-slate-400">
                                            {{
                                                [
                                                    address.street,
                                                    address.house && `д. ${address.house}`,
                                                    address.entrance &&
                                                        `подъезд ${address.entrance}`,
                                                    address.apartment && `кв. ${address.apartment}`,
                                                ]
                                                    .filter(Boolean)
                                                    .join(", ")
                                            }}
                                        </span>
                                    </label>
                                </li>
                            </ul>
                        </template>
                    </div>
                    <p
                        v-if="deliveryStepError"
                        class="text-[11px] text-red-400"
                    >
                        {{ deliveryStepError }}
                    </p>

                    <div
                        v-if="orderStore.deliveryInfo.method !== 'pickup'"
                        class="space-y-2 border-t border-white/5 pt-3"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-2xl bg-white/5 px-3 py-2 text-[11px] font-medium text-slate-100 hover:bg-white/10"
                            @click="isNewAddressOpen = !isNewAddressOpen"
                        >
                            <span>Добавить новый адрес</span>
                            <span class="text-[11px] text-slate-400">
                                {{ isNewAddressOpen ? "Скрыть" : "Развернуть" }}
                            </span>
                        </button>

                        <Transition name="fade">
                            <div
                                v-if="isNewAddressOpen"
                                class="space-y-2 pt-1"
                            >
                                <div class="grid grid-cols-2 gap-2">
                                    <input
                                        v-model="newAddressForm.title"
                                        type="text"
                                        placeholder="Название (дом, работа)"
                                        class="col-span-2 rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                                    />
                                    <input
                                        v-model="newAddressForm.street"
                                        type="text"
                                        placeholder="Улица"
                                        class="col-span-2 rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                                    />
                                    <input
                                        v-model="newAddressForm.house"
                                        type="text"
                                        placeholder="Дом"
                                        class="rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                                    />
                                    <input
                                        v-model="newAddressForm.entrance"
                                        type="text"
                                        placeholder="Подъезд"
                                        class="rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                                    />
                                    <input
                                        v-model="newAddressForm.apartment"
                                        type="text"
                                        placeholder="Квартира"
                                        class="rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                                    />
                                </div>
                                <textarea
                                    v-model="newAddressForm.comment"
                                    rows="2"
                                    placeholder="Комментарий для курьера (подъезд, код, ориентир)"
                                    class="w-full rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                                />
                                <label class="flex items-center gap-2 text-[11px] text-slate-300">
                                    <input
                                        v-model="newAddressForm.make_default"
                                        type="checkbox"
                                        class="h-3.5 w-3.5 rounded border-white/20 bg-black/60 text-amber-400 focus:ring-amber-400/60"
                                    />
                                    <span>Сделать основным адресом</span>
                                </label>
                                <p
                                    v-if="newAddressError"
                                    class="text-[11px] text-red-400"
                                >
                                    {{ newAddressError }}
                                </p>
                                <button
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-full bg-white/5 px-3 py-1.5 text-[11px] font-medium text-slate-100 transition hover:bg-white/10 disabled:opacity-50"
                                    :disabled="newAddressLoading"
                                    @click="handleCreateAddress"
                                >
                                    <span v-if="!newAddressLoading">Сохранить адрес</span>
                                    <span v-else>Сохраняем…</span>
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-100">
                            Комментарий к доставке
                        </p>
                        <textarea
                            rows="2"
                            class="w-full rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-xs text-slate-100 placeholder-slate-500 outline-none focus:border-amber-400"
                            placeholder="Подъезд, этаж, код домофона и другие нюансы"
                            :value="orderStore.deliveryInfo.comment"
                            @input="
                                orderStore.setDeliveryInfo({
                                    comment: $event.target.value,
                                })
                            "
                        />
                    </div>

                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                        <button
                            type="button"
                            class="underline-offset-2 hover:underline"
                            @click="goToCart"
                        >
                            Назад к корзине
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-full bg-amber-400 px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(251,191,36,0.7)] transition hover:bg-amber-300"
                            @click="goToPayment"
                        >
                            Далее: оплата
                        </button>
                    </div>
                </div>
            </template>

            <!-- Шаг: оплата -->
            <template v-else-if="activeStep === 'payment'">
                <div class="space-y-3 text-xs sm:text-sm text-slate-200">
                    <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400">
                        Шаг 2 из 3 — Оплата
                    </p>

                    <div class="space-y-2">
                        <p class="text-xs font-semibold text-slate-100">
                            Способ оплаты
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="method in ['cash', 'card', 'transfer']"
                                :key="method"
                                type="button"
                                class="rounded-full px-3 py-1 text-[11px] transition"
                                :class="
                                    orderStore.paymentInfo.method === method
                                        ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                                        : 'bg-white/5 text-slate-200 hover:bg-white/10'
                                "
                                @click="orderStore.setPaymentInfo({ method })"
                            >
                                {{
                                    method === "cash"
                                        ? "Наличными"
                                        : method === "card"
                                          ? "Банковская карта"
                                          : "Перевод"
                                }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="orderStore.paymentInfo.method === 'cash'"
                        class="space-y-1"
                    >
                        <p class="text-xs font-semibold text-slate-100">
                            Сдача с
                        </p>
                        <input
                            type="number"
                            min="0"
                            class="w-full rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-xs text-slate-100 placeholder-slate-500 outline-none focus:border-amber-400"
                            placeholder="Например, 2000"
                            :value="orderStore.paymentInfo.changeFrom ?? ''"
                            @input="
                                orderStore.setPaymentInfo({
                                    changeFrom: $event.target.value,
                                })
                            "
                        />
                    </div>

                    <p
                        v-if="paymentStepError"
                        class="text-[11px] text-red-400"
                    >
                        {{ paymentStepError }}
                    </p>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-100">
                            Комментарий к заказу
                        </p>
                        <textarea
                            rows="2"
                            class="w-full rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-xs text-slate-100 placeholder-slate-500 outline-none focus:border-amber-400"
                            placeholder="Например: без лука, позвонить за 10 минут до доставки"
                            :value="orderStore.customerComment"
                            @input="orderStore.setCustomerComment($event.target.value)"
                        />
                    </div>

                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                        <button
                            type="button"
                            class="underline-offset-2 hover:underline"
                            @click="goToDelivery"
                        >
                            Назад: доставка
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-full bg-amber-400 px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(251,191,36,0.7)] transition hover:bg-amber-300"
                            @click="goToConfirm"
                        >
                            Далее: подтвердить
                        </button>
                    </div>
                </div>
            </template>

            <!-- Шаг: подтверждение -->
            <template v-else-if="activeStep === 'confirm'">
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
                                v-for="item in orderStore.cartItems"
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
                                <span v-if="userStore.selectedAddress">
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

            <!-- Шаг: успех -->
            <template v-else-if="activeStep === 'success'">
                <div class="space-y-3 text-xs sm:text-sm text-slate-200">
                    <p class="text-[11px] uppercase tracking-[0.18em] text-amber-300">
                        Заказ оформлен
                    </p>
                    <p class="text-sm font-semibold text-slate-50">
                        Спасибо, бро. Мы приняли заказ и скоро свяжемся для подтверждения.
                    </p>
                    <p class="text-xs text-slate-300">
                        Номер заказа:
                        <span class="font-mono text-amber-300">
                            {{ orderStore.lastCreatedOrder?.id ?? "—" }}
                        </span>
                    </p>
                    <div class="mt-2 flex justify-end">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-full bg-amber-400 px-4 py-2 text-xs font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.7)] transition hover:bg-amber-300"
                            @click="goToCart"
                        >
                            Вернуться к меню
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<style scoped></style>

