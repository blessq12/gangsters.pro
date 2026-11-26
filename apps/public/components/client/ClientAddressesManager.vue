<script setup>
import { computed, ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useUserStore } from "../../modules/client/store/userStore";
import { useContentStore } from "../../modules/content/store";
import { applyApiFieldErrors } from "../../platform/extractApiFieldErrors";
import { mapApiError } from "../../platform/mapApiError";
import { useFormFieldErrors } from "../../platform/useFormFieldErrors";
import CheckoutAddressFormFields from "../checkout/CheckoutAddressFormFields.vue";

const userStore = useUserStore();
const contentStore = useContentStore();
const fieldErrors = useFormFieldErrors();
const branchCity = computed(() => String(contentStore.branch?.city ?? "").trim());

const cli = useAppDesign().components.client;
const s = cli.shared;
const ad = cli.addresses;

const form = ref({
    street: "",
    house: "",
    entrance: "",
    floor: "",
    apartment: "",
    make_default: false,
    latitude: null,
    longitude: null,
});

const loading = ref(false);

const hasAddresses = computed(
    () => Array.isArray(userStore.addresses) && userStore.addresses.length > 0,
);

/** Аккордеон только когда адреса уже есть; без адресов форма всегда открыта. */
const isAddOpen = ref(!hasAddresses.value);

watch(hasAddresses, (has) => {
    if (!has) {
        isAddOpen.value = true;
    }
});

const showAddForm = computed(() => !hasAddresses.value || isAddOpen.value);

watch(
    () => form.value.street,
    () => fieldErrors.clearField("street"),
);
watch(
    () => form.value.house,
    () => fieldErrors.clearField("house"),
);

function resetForm() {
    form.value = {
        street: "",
        house: "",
        entrance: "",
        floor: "",
        apartment: "",
        make_default: false,
        latitude: null,
        longitude: null,
    };
}

function readCoord(value) {
    if (value === null || value === undefined || value === "") {
        return null;
    }
    const number = Number(value);
    return Number.isFinite(number) ? number : null;
}

function applySuggestion(hit) {
    form.value = {
        ...form.value,
        street: String(hit.street ?? ""),
        house: String(hit.house ?? ""),
        entrance: String(hit.entrance ?? form.value.entrance ?? ""),
        floor: String(hit.floor ?? form.value.floor ?? ""),
        apartment: String(hit.apartment ?? form.value.apartment ?? ""),
        latitude: readCoord(hit.lat),
        longitude: readCoord(hit.lng),
    };
}

function clearCoords() {
    form.value = { ...form.value, latitude: null, longitude: null };
}

async function addAddress() {
    fieldErrors.clearAll();

    if (!String(form.value.street || "").trim()) {
        fieldErrors.setFieldError("street", "Укажи улицу.");
    }
    if (!String(form.value.house || "").trim()) {
        fieldErrors.setFieldError("house", "Укажи дом.");
    }
    if (
        !fieldErrors.hasAny.value &&
        (form.value.latitude === null || form.value.longitude === null)
    ) {
        fieldErrors.setFieldError("street", "Выбери адрес из подсказок — нужны координаты.");
    }
    if (fieldErrors.hasAny.value) {
        return;
    }

    loading.value = true;

    try {
        await userStore.addClientAddress({
            street: form.value.street,
            house: form.value.house,
            entrance: form.value.entrance || null,
            floor: form.value.floor || null,
            apartment: form.value.apartment || null,
            make_default: form.value.make_default,
            lat: form.value.latitude,
            lng: form.value.longitude,
        });

        resetForm();
        isAddOpen.value = false;
    } catch (e) {
        console.error(e);
        if (
            !applyApiFieldErrors(fieldErrors, e, {
                street: "street",
                house: "house",
            })
        ) {
            fieldErrors.setFormError(
                mapApiError(e, "Не удалось сохранить адрес. Попробуй ещё раз."),
            );
        }
    } finally {
        loading.value = false;
    }
}

async function removeAddress(id) {
    try {
        await userStore.deleteClientAddress(id);
    } catch (e) {
        console.error(e);
        fieldErrors.setFormError(mapApiError(e, "Не удалось удалить адрес. Попробуй ещё раз."));
    }
}

function useAddress(id) {
    userStore.selectAddress(id);
}
</script>

<template>
    <div :class="ad.root">
        <div v-if="hasAddresses" :class="ad.listStack">
            <div v-for="address in userStore.addresses" :key="address.id" :class="ad.card">
                <div :class="ad.cardRow">
                    <div>
                        <p :class="ad.titleStrong">{{ address.street }}, д. {{ address.house }}</p>
                        <p
                            v-if="address.entrance || address.floor || address.apartment"
                            :class="ad.metaLine"
                        >
                            <span v-if="address.entrance">подъезд {{ address.entrance }}</span>
                            <span v-if="address.entrance && (address.floor || address.apartment)">
                                ,
                            </span>
                            <span v-if="address.floor">эт. {{ address.floor }}</span>
                            <span v-if="address.floor && address.apartment">,</span>
                            <span v-if="address.apartment">кв. {{ address.apartment }}</span>
                        </p>
                    </div>
                    <div :class="ad.actionsCol">
                        <button type="button" :class="ad.btnSelect" @click="useAddress(address.id)">
                            {{ userStore.selectedAddressId === address.id ? "Выбран" : "Выбрать" }}
                        </button>
                        <button
                            type="button"
                            :class="ad.linkRemove"
                            @click="removeAddress(address.id)"
                        >
                            удалить
                        </button>
                    </div>
                </div>
                <p v-if="address.comment" :class="ad.commentLine">
                    {{ address.comment }}
                </p>
            </div>
        </div>

        <p v-else :class="ad.emptyHint">
            Добавь адрес один раз — при следующих заказах не придётся вводить его снова.
        </p>

        <p v-if="fieldErrors.formError && hasAddresses && !showAddForm" :class="s.error11">
            {{ fieldErrors.formError }}
        </p>

        <div :class="ad.addSection">
            <button
                v-if="hasAddresses"
                type="button"
                :class="ad.expandBtn"
                @click="isAddOpen = !isAddOpen"
            >
                <span>Добавить новый адрес</span>
                <span :class="ad.expandChevron">
                    {{ isAddOpen ? "Скрыть" : "Развернуть" }}
                </span>
            </button>

            <Transition name="fade">
                <form v-if="showAddForm" :class="ad.addForm" @submit.prevent="addAddress">
                    <CheckoutAddressFormFields
                        show-default-checkbox
                        :street="form.street"
                        :house="form.house"
                        :entrance="form.entrance"
                        :floor="form.floor"
                        :apartment="form.apartment"
                        :make-default="form.make_default"
                        :latitude="form.latitude"
                        :longitude="form.longitude"
                        :branch-city="branchCity"
                        :street-error="fieldErrors.get('street')"
                        :house-error="fieldErrors.get('house')"
                        @update:street="form.street = $event"
                        @update:house="form.house = $event"
                        @update:entrance="form.entrance = $event"
                        @update:floor="form.floor = $event"
                        @update:apartment="form.apartment = $event"
                        @update:make-default="form.make_default = $event"
                        @apply-suggestion="applySuggestion"
                        @clear-coords="clearCoords"
                    />

                    <p v-if="fieldErrors.formError" :class="s.error11">
                        {{ fieldErrors.formError }}
                    </p>

                    <button type="submit" :disabled="loading" :class="s.btnPrimaryCompact">
                        <span v-if="!loading">Сохранить адрес</span>
                        <span v-else>Сохраняем…</span>
                    </button>
                </form>
            </Transition>
        </div>
    </div>
</template>
