<script setup>
import { useAppDesign } from "../../design/useAppDesign";

const cb = useAppDesign().components.home.secondary.contentBlock;

defineProps({
    title: {
        type: String,
        default: "",
    },
    subtitle: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <section :class="cb.section">
        <div :class="cb.glowLayer">
            <div :class="cb.glowAmberTR"></div>
            <div :class="cb.glowRoseBL"></div>
        </div>

        <div :class="cb.inner">
            <div
                v-if="title || subtitle"
                :class="cb.headerRow"
            >
                <div>
                    <p :class="cb.accentBar"></p>
                    <h2
                        v-if="title"
                        :class="cb.title"
                    >
                        {{ title }}
                    </h2>
                </div>
                <p
                    v-if="subtitle"
                    :class="cb.subtitle"
                >
                    {{ subtitle }}
                </p>
            </div>
            <div :class="cb.body">
                <slot />
            </div>
        </div>
    </section>
</template>
