<script setup>
import { onMounted } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import FormField from "../ui/FormField.vue";
import CheckoutAuthAddressSection from "./CheckoutAuthAddressSection.vue";
import CheckoutOrderPreview from "./CheckoutOrderPreview.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";

const s = useAppDesign().components.checkout.shared;
const d = useAppDesign().components.checkout.delivery;

const {
    checkoutState,
    goToCart,
    goToGuest,
    goToPayment,
    setDeliveryMethod,
    setDeliveryComment,
    guestAddressDraft,
    patchGuestAddressDraft,
    handleGuestAddressHouseBlur,
    scheduleDeliveryPreview,
} = useCheckoutFlowContext();

onMounted(() => {
    scheduleDeliveryPreview();
});

const { checkoutIntent, deliveryFieldErrors, isGuestCheckout } = checkoutState;
</script>

<template>
    <CheckoutStepFrame group="delivery">
        <FormField :error="deliveryFieldErrors.get('method')">
            <template #default>
                <CheckoutSection title="Способ">
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
                </CheckoutSection>
            </template>
        </FormField>

        <CheckoutSection
            v-if="checkoutIntent.deliveryInfo.method !== 'pickup' && isGuestCheckout"
            title="Адрес"
            variant="form"
        >
            <FormField :error="deliveryFieldErrors.get('street')">
                <template #default="{ id, invalid, invalidClass, describedBy, 'aria-invalid': ariaInvalid }">
                    <input
                        :id="id"
                        :value="guestAddressDraft.street"
                        type="text"
                        placeholder="Улица"
                        :class="[s.inputFieldFull, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                        @input="
                            patchGuestAddressDraft({
                                street: $event.target.value,
                            })
                        "
                    />
                </template>
            </FormField>

            <div :class="s.grid2">
                <FormField :error="deliveryFieldErrors.get('house')">
                    <template #default="{ id, invalid, invalidClass, describedBy, 'aria-invalid': ariaInvalid }">
                        <input
                            :id="id"
                            :value="guestAddressDraft.house"
                            type="text"
                            placeholder="Дом"
                            :class="[s.inputFieldGridCell, invalid && invalidClass]"
                            :aria-invalid="ariaInvalid"
                            :aria-describedby="describedBy"
                            @input="
                                patchGuestAddressDraft({
                                    house: $event.target.value,
                                })
                            "
                            @blur="handleGuestAddressHouseBlur"
                        />
                    </template>
                </FormField>

                <input
                    :value="guestAddressDraft.entrance"
                    type="text"
                    placeholder="Подъезд"
                    :class="s.inputFieldGridCell"
                    @input="
                        patchGuestAddressDraft({
                            entrance: $event.target.value,
                        })
                    "
                />
                <input
                    :value="guestAddressDraft.apartment"
                    type="text"
                    placeholder="Квартира"
                    :class="s.inputFieldCol2"
                    @input="
                        patchGuestAddressDraft({
                            apartment: $event.target.value,
                        })
                    "
                />
            </div>
        </CheckoutSection>

        <CheckoutAuthAddressSection
            v-if="checkoutIntent.deliveryInfo.method !== 'pickup' && !isGuestCheckout"
        />

        <CheckoutSection
            title="Комментарий"
            variant="form"
        >
            <textarea
                rows="2"
                :class="s.textareaFlow"
                placeholder="Подъезд, код, этаж"
                :value="checkoutIntent.deliveryInfo.comment"
                @input="setDeliveryComment($event.target.value)"
            />
        </CheckoutSection>

        <p
            v-if="deliveryFieldErrors.formError"
            :class="s.errorLine"
        >
            {{ deliveryFieldErrors.formError }}
        </p>

        <CheckoutOrderPreview variant="delivery" />

        <template #nav>
            <button
                type="button"
                :class="s.linkUnderline"
                @click="isGuestCheckout ? goToGuest() : goToCart()"
            >
                Назад
            </button>
            <button
                type="button"
                :class="s.btnPrimarySm"
                @click="goToPayment"
            >
                Далее
            </button>
        </template>
    </CheckoutStepFrame>
</template>
