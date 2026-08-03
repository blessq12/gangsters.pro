<script setup>
import { computed, ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useUserStore } from "../../modules/client/store/userStore";
import { applyApiFieldErrors } from "../../platform/extractApiFieldErrors";
import { mapApiError } from "../../platform/mapApiError";
import { useFormFieldErrors } from "../../platform/useFormFieldErrors";
import FormField from "../ui/FormField.vue";

const userStore = useUserStore();
const fieldErrors = useFormFieldErrors();

const cli = useAppDesign().components.client;
const s = cli.shared;
const ad = cli.addresses;

const form = ref({
    street: "",
    house: "",
    entrance: "",
    apartment: "",
    comment: "",
    make_default: false,
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
        apartment: "",
        comment: "",
        make_default: false,
    };
}

async function addAddress() {
    fieldErrors.clearAll();

    if (!String(form.value.street || "").trim()) {
        fieldErrors.setFieldError("street", "Укажи улицу.");
    }
    if (!String(form.value.house || "").trim()) {
        fieldErrors.setFieldError("house", "Укажи дом.");
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
            apartment: form.value.apartment || null,
            comment: form.value.comment || null,
            make_default: form.value.make_default,
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
        fieldErrors.setFormError(
            mapApiError(e, "Не удалось удалить адрес. Попробуй ещё раз."),
        );
    }
}

function useAddress(id) {
    userStore.selectAddress(id);
}
</script>

<template>
    <div :class="ad.root">
        <div v-if="hasAddresses" :class="ad.listStack">
            <div
                v-for="address in userStore.addresses"
                :key="address.id"
                :class="ad.card"
            >
                <div :class="ad.cardRow">
                    <div>
                        <p :class="ad.titleStrong">
                            {{ address.street }}, д. {{ address.house }}
                        </p>
                        <p
                            v-if="address.entrance || address.apartment"
                            :class="ad.metaLine"
                        >
                            <span v-if="address.entrance">
                                подъезд {{ address.entrance }}
                            </span>
                            <span v-if="address.entrance && address.apartment">
                                ,
                            </span>
                            <span v-if="address.apartment">
                                кв. {{ address.apartment }}
                            </span>
                        </p>
                    </div>
                    <div :class="ad.actionsCol">
                        <button
                            type="button"
                            :class="ad.btnSelect"
                            @click="useAddress(address.id)"
                        >
                            {{
                                userStore.selectedAddressId === address.id
                                    ? "Выбран"
                                    : "Выбрать"
                            }}
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
            Добавь адрес один раз — при следующих заказах не придётся вводить
            его снова.
        </p>

        <p
            v-if="fieldErrors.formError && hasAddresses && !showAddForm"
            :class="s.error11"
        >
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
                <form
                    v-if="showAddForm"
                    :class="ad.addForm"
                    @submit.prevent="addAddress"
                >
                    <FormField :error="fieldErrors.get('street')">
                        <template
                            #default="{
                                id,
                                invalid,
                                invalidClass,
                                describedBy,
                                ariaInvalid,
                            }"
                        >
                            <input
                                :id="id"
                                v-model="form.street"
                                type="text"
                                placeholder="Улица"
                                :class="[s.input, invalid && invalidClass]"
                                :aria-invalid="ariaInvalid"
                                :aria-describedby="describedBy"
                            />
                        </template>
                    </FormField>

                    <div :class="s.addressDetailsGrid">
                        <FormField :error="fieldErrors.get('house')">
                            <template
                                #default="{
                                    id,
                                    invalid,
                                    invalidClass,
                                    describedBy,
                                    ariaInvalid,
                                }"
                            >
                                <input
                                    :id="id"
                                    v-model="form.house"
                                    type="text"
                                    placeholder="Дом"
                                    :class="[
                                        s.inputGrid11,
                                        'w-full',
                                        invalid && invalidClass,
                                    ]"
                                    :aria-invalid="ariaInvalid"
                                    :aria-describedby="describedBy"
                                />
                            </template>
                        </FormField>

                        <input
                            v-model="form.entrance"
                            type="text"
                            placeholder="Подъезд"
                            :class="[s.inputGrid11, 'w-full']"
                        />
                        <input
                            v-model="form.apartment"
                            type="text"
                            placeholder="Квартира"
                            :class="[s.inputGrid11, 'w-full']"
                        />
                    </div>

                    <textarea
                        v-model="form.comment"
                        rows="2"
                        placeholder="Комментарий для курьера (подъезд, код, ориентир)"
                        :class="s.textarea"
                    />

                    <label :class="s.checkboxRow11">
                        <AppCheckbox v-model="form.make_default" size="sm" />
                        <span>Сделать основным адресом</span>
                    </label>

                    <p v-if="fieldErrors.formError" :class="s.error11">
                        {{ fieldErrors.formError }}
                    </p>

                    <button
                        type="submit"
                        :disabled="loading"
                        :class="s.btnPrimaryCompact"
                    >
                        <span v-if="!loading">Сохранить адрес</span>
                        <span v-else>Сохраняем…</span>
                    </button>
                </form>
            </Transition>
        </div>
    </div>
</template>
