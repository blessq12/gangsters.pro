<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import FormField from "../ui/FormField.vue";

defineProps({
    street: {
        type: String,
        default: "",
    },
    house: {
        type: String,
        default: "",
    },
    entrance: {
        type: String,
        default: "",
    },
    apartment: {
        type: String,
        default: "",
    },
    streetError: {
        type: String,
        default: null,
    },
    houseError: {
        type: String,
        default: null,
    },
    showTitle: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: "",
    },
    showComment: {
        type: Boolean,
        default: false,
    },
    comment: {
        type: String,
        default: "",
    },
    commentPlaceholder: {
        type: String,
        default: "Комментарий для курьера (подъезд, код, ориентир)",
    },
    showDefaultCheckbox: {
        type: Boolean,
        default: false,
    },
    makeDefault: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "update:street",
    "update:house",
    "update:entrance",
    "update:apartment",
    "update:title",
    "update:comment",
    "update:makeDefault",
]);

const s = useAppDesign().components.checkout.shared;
</script>

<template>
    <FormField
        v-if="showTitle"
        label="Название"
    >
        <template #default="{ id, invalidClass }">
            <input
                :id="id"
                :value="title"
                type="text"
                placeholder="Дом, работа"
                :class="[s.inputFieldFull, invalidClass]"
                @input="emit('update:title', $event.target.value)"
            />
        </template>
    </FormField>

    <FormField
        label="Улица"
        :error="streetError"
    >
        <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
            <input
                :id="id"
                :value="street"
                type="text"
                placeholder="Например, Ленина"
                :class="[s.inputFieldFull, invalid && invalidClass]"
                :aria-invalid="ariaInvalid"
                :aria-describedby="describedBy"
                @input="emit('update:street', $event.target.value)"
            />
        </template>
    </FormField>

    <div :class="s.grid3">
        <FormField
            label="Дом"
            :error="houseError"
        >
            <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
                <input
                    :id="id"
                    :value="house"
                    type="text"
                    placeholder="12"
                    :class="[s.inputFieldGridCell, invalid && invalidClass]"
                    :aria-invalid="ariaInvalid"
                    :aria-describedby="describedBy"
                    @input="emit('update:house', $event.target.value)"
                />
            </template>
        </FormField>

        <FormField label="Подъезд">
            <template #default="{ id }">
                <input
                    :id="id"
                    :value="entrance"
                    type="text"
                    placeholder="1"
                    :class="s.inputFieldGridCell"
                    @input="emit('update:entrance', $event.target.value)"
                />
            </template>
        </FormField>

        <FormField label="Квартира">
            <template #default="{ id }">
                <input
                    :id="id"
                    :value="apartment"
                    type="text"
                    placeholder="45"
                    :class="s.inputFieldGridCell"
                    @input="emit('update:apartment', $event.target.value)"
                />
            </template>
        </FormField>
    </div>

    <FormField
        v-if="showComment"
        label="Комментарий к адресу"
    >
        <template #default="{ id }">
            <textarea
                :id="id"
                :value="comment"
                rows="2"
                :placeholder="commentPlaceholder"
                :class="s.textareaAddress"
                @input="emit('update:comment', $event.target.value)"
            />
        </template>
    </FormField>

    <label
        v-if="showDefaultCheckbox"
        :class="s.checkboxLabelRow"
    >
        <AppCheckbox
            :model-value="makeDefault"
            size="sm"
            @update:model-value="emit('update:makeDefault', $event)"
        />
        <span>Сделать основным адресом</span>
    </label>
</template>
