<script setup>
import { useAppDesign } from "../../design/useAppDesign";

const cs = useAppDesign().components.catalog.search;

defineProps({
    title: {
        type: String,
        required: true,
    },
    lead: {
        type: String,
        required: true,
    },
    examples: {
        type: Array,
        default: () => [],
    },
    query: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["applyQuery", "clearQuery"]);
</script>

<template>
    <div :class="cs.panelHero">
        <div
            :class="cs.panelGlow"
            aria-hidden="true"
        />
        <div :class="cs.panelContent">
            <p :class="cs.kicker">
                {{ title }}
            </p>
            <p :class="cs.title">
                «{{ query }}»
            </p>
            <p :class="cs.lead">
                {{ lead }}
            </p>

            <div :class="cs.chips">
                <button
                    type="button"
                    :class="cs.chip"
                    @click="emit('clearQuery')"
                >
                    Сбросить запрос
                </button>
                <button
                    v-for="example in examples"
                    :key="example"
                    type="button"
                    :class="cs.chip"
                    @click="emit('applyQuery', example)"
                >
                    {{ example }}
                </button>
            </div>
        </div>
    </div>
</template>
