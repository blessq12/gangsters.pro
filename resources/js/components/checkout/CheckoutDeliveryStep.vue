<script setup>
import { ref, watch } from "vue";
import { useRuPhoneModel } from "../../composables/client/useRuPhoneModel";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import {
    normalizeRuPhoneDigits,
    RU_PHONE_MASKA_PATTERN,
    RU_PHONE_MASKA_TOKENS_ATTR,
} from "../../validation/ruPhone";

const {
    userStore,
    checkoutState,
    goToCart,
    goToPayment,
    setDeliveryMethod,
    setDeliveryComment,
    setGuestContact,
    patchDeliveryAddress,
    selectAddress,
    handleCreateAddress,
} = useCheckoutFlowContext();

const {
    orderStore,
    newAddressForm,
    newAddressLoading,
    newAddressError,
    isNewAddressOpen,
    deliveryStepError,
    isGuestCheckout,
} = checkoutState;

const guestPhoneForm = ref({
    phone: normalizeRuPhoneDigits(orderStore.guestContact.phone),
});

const { phoneMask } = useRuPhoneModel(guestPhoneForm, "phone");

watch(
    () => guestPhoneForm.value.phone,
    (digits) => {
        const n = normalizeRuPhoneDigits(digits);
        const cur = normalizeRuPhoneDigits(orderStore.guestContact.phone);
        if (n !== cur) {
            setGuestContact({ phone: n });
        }
    },
);

watch(
    () => orderStore.guestContact.phone,
    (p) => {
        const n = normalizeRuPhoneDigits(p);
        if (n !== guestPhoneForm.value.phone) {
            guestPhoneForm.value.phone = n;
        }
    },
);
</script>

<template>
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
                    @click="setDeliveryMethod(method)"
                >
                    {{ method === "courier" ? "Курьер" : "Самовывоз" }}
                </button>
            </div>
        </div>

        <div
            v-if="isGuestCheckout"
            class="space-y-2 rounded-2xl border border-white/10 bg-black/30 px-3 py-3"
        >
            <p class="text-xs font-semibold text-slate-100">
                Контакт
            </p>
            <input
                :value="orderStore.guestContact.name"
                type="text"
                placeholder="Имя"
                class="w-full rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                @input="
                    setGuestContact({ name: $event.target.value })
                "
            />
            <input
                v-model="phoneMask.masked"
                v-maska="phoneMask"
                :data-maska="RU_PHONE_MASKA_PATTERN"
                :data-maska-tokens="RU_PHONE_MASKA_TOKENS_ATTR"
                type="tel"
                placeholder="+7 (___) ___-__-__"
                class="w-full rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
            />
            <input
                :value="orderStore.guestContact.email"
                type="email"
                placeholder="Email (необязательно)"
                class="w-full rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                @input="
                    setGuestContact({ email: $event.target.value })
                "
            />
        </div>

        <div
            v-if="orderStore.deliveryInfo.method !== 'pickup' && isGuestCheckout"
            class="space-y-2"
        >
            <p class="text-xs font-semibold text-slate-100">
                Адрес курьера
            </p>
            <div class="grid grid-cols-2 gap-2">
                <input
                    :value="orderStore.deliveryInfo.address?.street ?? ''"
                    type="text"
                    placeholder="Улица"
                    class="col-span-2 rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                    @input="
                        patchDeliveryAddress({
                            street: $event.target.value,
                        })
                    "
                />
                <input
                    :value="orderStore.deliveryInfo.address?.house ?? ''"
                    type="text"
                    placeholder="Дом"
                    class="rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                    @input="
                        patchDeliveryAddress({
                            house: $event.target.value,
                        })
                    "
                />
                <input
                    :value="orderStore.deliveryInfo.address?.entrance ?? ''"
                    type="text"
                    placeholder="Подъезд"
                    class="rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                    @input="
                        patchDeliveryAddress({
                            entrance: $event.target.value,
                        })
                    "
                />
                <input
                    :value="orderStore.deliveryInfo.address?.apartment ?? ''"
                    type="text"
                    placeholder="Квартира"
                    class="col-span-2 rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-[11px] text-slate-50 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/60"
                    @input="
                        patchDeliveryAddress({
                            apartment: $event.target.value,
                        })
                    "
                />
            </div>
        </div>

        <div
            v-if="orderStore.deliveryInfo.method !== 'pickup' && !isGuestCheckout"
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
                            @change="selectAddress(address.id)"
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
            v-if="orderStore.deliveryInfo.method !== 'pickup' && !isGuestCheckout"
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

            <Transition name="checkout-fade">
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
                    setDeliveryComment($event.target.value)
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

<style scoped>
.checkout-fade-enter-active,
.checkout-fade-leave-active {
    transition: opacity 0.2s ease;
}
.checkout-fade-enter-from,
.checkout-fade-leave-to {
    opacity: 0;
}
</style>
