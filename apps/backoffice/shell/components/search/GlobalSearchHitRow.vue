<script setup>
import { computed } from "vue";
import ShellIcon from "../ShellIcon.vue";

const props = defineProps({
    /** @type {import('vue').PropType<import('../../search/types.js').GlobalSearchHit>} */
    hit: { type: Object, required: true },
});

const emit = defineEmits(["select"]);

const iconName = computed(() => {
    const map = {
        order: "orders",
        client: "clients",
        delivery: "logistics",
        warehouse: "warehouse",
        catalog: "catalog",
    };
    return map[props.hit?.type] || "search";
});

function onSelect() {
    emit("select", props.hit);
}
</script>

<template>
    <button
        type="button"
        class="flex w-full items-start gap-3 px-3 py-2.5 text-left transition-colors hover:bg-white/[0.06]"
        @click="onSelect"
    >
        <ShellIcon :name="iconName" class="mt-0.5 h-4 w-4 shrink-0 text-bo-muted" />
        <span class="min-w-0 flex-1">
            <span class="block truncate text-sm text-bo-fg">
                {{ hit.title }}
            </span>
            <span v-if="hit.subtitle" class="mt-0.5 block truncate text-xs text-bo-muted">
                {{ hit.subtitle }}
            </span>
        </span>
    </button>
</template>
