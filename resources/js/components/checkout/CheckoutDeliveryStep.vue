<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const d = chk.delivery;

const {
    userStore,
    checkoutState,
    checkoutStepMeta,
    goToCart,
    goToGuest,
    goToPayment,
    setDeliveryMethod,
    setDeliveryComment,
    patchDeliveryAddress,
    selectAddress,
    handleCreateAddress,
} = useCheckoutFlowContext();

const {
    checkoutIntent,
    newAddressForm,
    newAddressLoading,
    newAddressError,
    isNewAddressOpen,
    deliveryStepError,
    isGuestCheckout,
} = checkoutState;
</script>

<template>
    <div :class="s.flowBody">
        <p :class="s.stepKicker">
            Шаг {{ checkoutStepMeta.delivery.n }} из {{ checkoutStepMeta.delivery.total }} — Доставка
        </p>

        <div class="space-y-2">
            <p :class="s.headingSm">
                Способ доставки
            </p>
            <div :class="d.methodRow">
                <button
                    v-for="method in ['courier', 'pickup']"
                    :key="method"
                    type="button"
                    :class="[
                        s.pillRoundText,
                        checkoutIntent.deliveryInfo.method === method ? s.pillActive : s.pillInactive,
                    ]"
                    @click="setDeliveryMethod(method)"
                >
                    {{ method === "courier" ? "Курьер" : "Самовывоз" }}
                </button>
            </div>
        </div>

        <div
            v-if="checkoutIntent.deliveryInfo.method !== 'pickup' && isGuestCheckout"
            class="space-y-2"
        >
            <p :class="s.headingSm">
                Адрес курьера
            </p>
            <div :class="s.grid2">
                <input
                    :value="checkoutIntent.deliveryInfo.address?.street ?? ''"
                    type="text"
                    placeholder="Улица"
                    :class="s.inputFieldCol2"
                    @input="
                        patchDeliveryAddress({
                            street: $event.target.value,
                        })
                    "
                />
                <input
                    :value="checkoutIntent.deliveryInfo.address?.house ?? ''"
                    type="text"
                    placeholder="Дом"
                    :class="s.inputFieldGridCell"
                    @input="
                        patchDeliveryAddress({
                            house: $event.target.value,
                        })
                    "
                />
                <input
                    :value="checkoutIntent.deliveryInfo.address?.entrance ?? ''"
                    type="text"
                    placeholder="Подъезд"
                    :class="s.inputFieldGridCell"
                    @input="
                        patchDeliveryAddress({
                            entrance: $event.target.value,
                        })
                    "
                />
                <input
                    :value="checkoutIntent.deliveryInfo.address?.apartment ?? ''"
                    type="text"
                    placeholder="Квартира"
                    :class="s.inputFieldCol2"
                    @input="
                        patchDeliveryAddress({
                            apartment: $event.target.value,
                        })
                    "
                />
            </div>
        </div>

        <div
            v-if="checkoutIntent.deliveryInfo.method !== 'pickup' && !isGuestCheckout"
            class="space-y-2"
        >
            <p :class="s.headingSm">
                Выбери адрес доставки
            </p>
            <template v-if="checkoutIntent.deliveryInfo.method !== 'pickup'">
                <div
                    v-if="!userStore.addresses.length"
                    :class="s.addressEmptyHint"
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
                        :class="s.addressLi"
                    >
                        <input
                            :id="`addr-${address.id}`"
                            type="radio"
                            :class="s.radioField"
                            :checked="userStore.selectedAddressId === address.id"
                            @change="selectAddress(address.id)"
                        />
                        <label
                            :for="`addr-${address.id}`"
                            :class="s.labelAddress"
                        >
                            <span :class="s.addressTitle">
                                {{
                                    address.title ||
                                        address.label ||
                                        `Адрес #${address.id}`
                                }}
                            </span>
                            <span :class="s.addressMeta">
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
            :class="s.errorLine"
        >
            {{ deliveryStepError }}
        </p>

        <div
            v-if="checkoutIntent.deliveryInfo.method !== 'pickup' && !isGuestCheckout"
            :class="s.borderSectionTop"
        >
            <button
                type="button"
                :class="s.expandRowBtn"
                @click="isNewAddressOpen = !isNewAddressOpen"
            >
                <span>Добавить новый адрес</span>
                <span :class="s.expandRowChevronMuted">
                    {{ isNewAddressOpen ? "Скрыть" : "Развернуть" }}
                </span>
            </button>

            <Transition name="checkout-fade">
                <div
                    v-if="isNewAddressOpen"
                    :class="s.newAddressWrap"
                >
                    <div :class="s.grid2">
                        <input
                            v-model="newAddressForm.title"
                            type="text"
                            placeholder="Название (дом, работа)"
                            :class="s.inputFieldCol2"
                        />
                        <input
                            v-model="newAddressForm.street"
                            type="text"
                            placeholder="Улица"
                            :class="s.inputFieldCol2"
                        />
                        <input
                            v-model="newAddressForm.house"
                            type="text"
                            placeholder="Дом"
                            :class="s.inputFieldGridCell"
                        />
                        <input
                            v-model="newAddressForm.entrance"
                            type="text"
                            placeholder="Подъезд"
                            :class="s.inputFieldGridCell"
                        />
                        <input
                            v-model="newAddressForm.apartment"
                            type="text"
                            placeholder="Квартира"
                            :class="s.inputFieldCol2"
                        />
                    </div>
                    <textarea
                        v-model="newAddressForm.comment"
                        rows="2"
                        placeholder="Комментарий для курьера (подъезд, код, ориентир)"
                        :class="s.textareaAddress"
                    />
                    <label :class="s.checkboxLabelRow">
                        <input
                            v-model="newAddressForm.make_default"
                            type="checkbox"
                            :class="s.checkboxSm"
                        />
                        <span>Сделать основным адресом</span>
                    </label>
                    <p
                        v-if="newAddressError"
                        :class="s.errorLine"
                    >
                        {{ newAddressError }}
                    </p>
                    <button
                        type="button"
                        :class="s.saveSecondaryBtn"
                        :disabled="newAddressLoading"
                        @click="handleCreateAddress"
                    >
                        <span v-if="!newAddressLoading">Сохранить адрес</span>
                        <span v-else>Сохраняем…</span>
                    </button>
                </div>
            </Transition>
        </div>

        <div :class="s.spacerAfterComment">
            <p :class="s.headingSm">
                Комментарий к доставке
            </p>
            <textarea
                rows="2"
                :class="s.textareaFlow"
                placeholder="Подъезд, этаж, код домофона и другие нюансы"
                :value="checkoutIntent.deliveryInfo.comment"
                @input="
                    setDeliveryComment($event.target.value)
                "
            />
        </div>

        <div :class="s.navFooterRow">
            <button
                type="button"
                :class="s.linkUnderline"
                @click="isGuestCheckout ? goToGuest() : goToCart()"
            >
                {{ isGuestCheckout ? "Назад: контакт" : "Назад к корзине" }}
            </button>
            <button
                type="button"
                :class="s.btnPrimarySm"
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
