<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useUserStore } from "../../stores/userStore";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const d = chk.delivery;

const userStore = useUserStore();
const { addresses } = storeToRefs(userStore);

const {
    checkoutState,
    selectAddress,
    handleCreateAddress,
    openProfileDock,
    toggleNewAddressOpen,
} = useCheckoutFlowContext();

const {
    newAddressForm,
    newAddressLoading,
    newAddressError,
    isNewAddressOpen,
} = checkoutState;

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
    <div v-if="!hasAddresses" :class="d.emptyHero">
        <div class="space-y-1">
            <p :class="d.emptyTitle">
                Адрес доставки
            </p>
            <p :class="d.emptyLead">
                Укажи адрес — сохраним в аккаунте для следующих заказов.
            </p>
        </div>

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
            :class="d.savePrimaryBtn"
            :disabled="newAddressLoading"
            @click="handleCreateAddress"
        >
            <span v-if="!newAddressLoading">Сохранить и продолжить</span>
            <span v-else>Сохраняем…</span>
        </button>
        <button
            type="button"
            :class="d.profileLink"
            @click="openProfileDock"
        >
            Управлять адресами в профиле
        </button>
    </div>

    <div v-else :class="d.listSection">
        <p :class="s.headingSm">
            Выбери адрес доставки
        </p>
        <ul class="space-y-2">
            <li
                v-for="address in addresses"
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
                        {{ formatAddressLine(address) }}
                    </span>
                </label>
            </li>
        </ul>

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
