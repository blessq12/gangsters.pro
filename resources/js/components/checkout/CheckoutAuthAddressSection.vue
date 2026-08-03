<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../modules/checkout/application/flowContext";
import { CHECKOUT_LOADING_LABELS } from "../../modules/checkout/application/session";
import { useUserStore } from "../../modules/client/store/userStore";
import FormField from "../ui/FormField.vue";
import CheckoutAddressFormFields from "./CheckoutAddressFormFields.vue";
import CheckoutSection from "./CheckoutSection.vue";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const o = chk.optionCard;

const userStore = useUserStore();
const { addresses } = storeToRefs(userStore);

const {
    checkoutState,
    selectAddress,
    handleCreateAddress,
    openProfileDock,
    toggleNewAddressOpen,
    isNewAddressOpen,
    newAddressForm,
    newAddressLoading,
} = useCheckoutFlowContext();

const { deliveryFieldErrors, newAddressFieldErrors } = checkoutState;

const hasAddresses = computed(
    () => Array.isArray(addresses.value) && addresses.value.length > 0,
);

function formatAddressLine(address) {
    return [
        address.street,
        address.house && `д. ${address.house}`,
        address.entrance && `подъезд ${address.entrance}`,
        address.apartment && `кв. ${address.apartment}`,
    ]
        .filter(Boolean)
        .join(", ");
}
</script>

<template>
    <CheckoutSection
        v-if="!hasAddresses"
        title="Куда доставить"
        variant="form"
    >
        <p :class="s.introMuted">
            Сохраним адрес в аккаунте для следующих заказов.
        </p>

        <CheckoutAddressFormFields
            show-title
            show-comment
            comment-placeholder="Заметка к адресу в профиле (необязательно)"
            show-default-checkbox
            :title="newAddressForm.title"
            :street="newAddressForm.street"
            :house="newAddressForm.house"
            :entrance="newAddressForm.entrance"
            :apartment="newAddressForm.apartment"
            :comment="newAddressForm.comment"
            :make-default="newAddressForm.make_default"
            :street-error="newAddressFieldErrors.get('street')"
            :house-error="newAddressFieldErrors.get('house')"
            @update:title="newAddressForm.title = $event"
            @update:street="newAddressForm.street = $event"
            @update:house="newAddressForm.house = $event"
            @update:entrance="newAddressForm.entrance = $event"
            @update:apartment="newAddressForm.apartment = $event"
            @update:comment="newAddressForm.comment = $event"
            @update:make-default="newAddressForm.make_default = $event"
        />

        <p
            v-if="newAddressFieldErrors.formError"
            :class="s.errorLine"
        >
            {{ newAddressFieldErrors.formError }}
        </p>
        <button
            type="button"
            :class="s.btnPrimaryNav"
            :disabled="newAddressLoading"
            @click="handleCreateAddress"
        >
            <span v-if="!newAddressLoading">Сохранить и продолжить</span>
            <span v-else>{{ CHECKOUT_LOADING_LABELS.addressSave }}</span>
        </button>
        <button
            type="button"
            :class="s.linkUnderline"
            @click="openProfileDock"
        >
            Управлять адресами в профиле
        </button>
    </CheckoutSection>

    <CheckoutSection
        v-else
        title="Куда доставить"
    >
        <FormField :error="deliveryFieldErrors.get('selectedAddress')">
            <template #default>
                <div :class="o.listStack">
                    <button
                        v-for="address in addresses"
                        :key="address.id"
                        type="button"
                        :class="[
                            o.addressCard,
                            userStore.selectedAddressId === address.id
                                ? o.cardSelected
                                : o.cardIdle,
                        ]"
                        :aria-pressed="userStore.selectedAddressId === address.id"
                        @click="selectAddress(address.id)"
                    >
                        <span
                            v-if="userStore.selectedAddressId === address.id"
                            :class="o.badge"
                        >
                            Выбрано
                        </span>
                        <span :class="o.addressInner">
                            <span :class="o.addressTitle">
                                {{
                                    address.title ||
                                        address.label ||
                                        `Адрес #${address.id}`
                                }}
                            </span>
                            <span :class="o.addressMeta">
                                {{ formatAddressLine(address) }}
                            </span>
                        </span>
                    </button>
                </div>
            </template>
        </FormField>

        <div :class="s.borderSectionTop">
            <button
                type="button"
                :class="s.expandRowBtn"
                @click="toggleNewAddressOpen"
            >
                <span>Добавить другой адрес</span>
                <span :class="s.expandRowChevronMuted">
                    {{ isNewAddressOpen ? "Скрыть" : "Развернуть" }}
                </span>
            </button>

            <Transition name="checkout-fade">
                <div
                    v-if="isNewAddressOpen"
                    :class="[s.newAddressWrap, s.sectionInset]"
                >
                    <CheckoutAddressFormFields
                        show-title
                        show-comment
                        show-default-checkbox
                        :title="newAddressForm.title"
                        :street="newAddressForm.street"
                        :house="newAddressForm.house"
                        :entrance="newAddressForm.entrance"
                        :apartment="newAddressForm.apartment"
                        :comment="newAddressForm.comment"
                        :make-default="newAddressForm.make_default"
                        :street-error="newAddressFieldErrors.get('street')"
                        :house-error="newAddressFieldErrors.get('house')"
                        @update:title="newAddressForm.title = $event"
                        @update:street="newAddressForm.street = $event"
                        @update:house="newAddressForm.house = $event"
                        @update:entrance="newAddressForm.entrance = $event"
                        @update:apartment="newAddressForm.apartment = $event"
                        @update:comment="newAddressForm.comment = $event"
                        @update:make-default="newAddressForm.make_default = $event"
                    />

                    <p
                        v-if="newAddressFieldErrors.formError"
                        :class="s.errorLine"
                    >
                        {{ newAddressFieldErrors.formError }}
                    </p>
                    <button
                        type="button"
                        :class="s.saveSecondaryBtn"
                        :disabled="newAddressLoading"
                        @click="handleCreateAddress"
                    >
                        <span v-if="!newAddressLoading">Сохранить адрес</span>
                        <span v-else>{{ CHECKOUT_LOADING_LABELS.addressSave }}</span>
                    </button>
                </div>
            </Transition>
        </div>
    </CheckoutSection>
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
