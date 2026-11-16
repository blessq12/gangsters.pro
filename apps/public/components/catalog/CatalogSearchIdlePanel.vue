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
    history: {
        type: Array,
        default: () => [],
    },
    examplesLabel: {
        type: String,
        default: "Попробуй, например",
    },
    historyLabel: {
        type: String,
        default: "Недавние запросы",
    },
});

const emit = defineEmits(["applyQuery"]);
</script>

<template>
    <div :class="cs.idleRoot">
        <div :class="cs.idleIntro">
            <p :class="cs.idleTitle">
                {{ title }}
            </p>
            <p :class="cs.idleLead">
                {{ lead }}
            </p>
        </div>

        <template v-if="history.length">
            <p :class="cs.idleSectionLabel">
                {{ historyLabel }}
            </p>
            <div :class="cs.idleChips">
                <button
                    v-for="entry in history"
                    :key="entry"
                    type="button"
                    :class="cs.chipHistory"
                    @click="emit('applyQuery', entry)"
                >
                    <i
                        :class="cs.chipHistoryIcon"
                        aria-hidden="true"
                    />
                    <span :class="cs.chipHistoryText">{{ entry }}</span>
                </button>
            </div>
        </template>

        <p :class="cs.idleSectionLabel">
            {{ examplesLabel }}
        </p>
        <div :class="cs.idleChips">
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
</template>
