<script setup>
import { useAppDesign } from "../../design/useAppDesign";

defineProps({
    selected: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    hint: {
        type: String,
        default: "",
    },
    icon: {
        type: String,
        default: "",
    },
    badge: {
        type: String,
        default: "Выбрано",
    },
});

defineEmits(["select"]);

const o = useAppDesign().components.checkout.optionCard;
</script>

<template>
    <button
        type="button"
        :class="[o.card, selected ? o.cardSelected : o.cardIdle]"
        :aria-pressed="selected"
        @click="$emit('select')"
    >
        <span
            v-if="selected"
            :class="o.badge"
        >
            {{ badge }}
        </span>

        <span :class="o.inner">
            <span
                v-if="icon"
                :class="[o.iconWrap, selected ? o.iconWrapSelected : o.iconWrapIdle]"
                aria-hidden="true"
            >
                <i :class="icon" />
            </span>

            <span :class="o.body">
                <span :class="o.title">
                    {{ title }}
                </span>
                <span
                    v-if="hint"
                    :class="o.hint"
                >
                    {{ hint }}
                </span>
            </span>
        </span>
    </button>
</template>
