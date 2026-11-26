<script setup>
import { watch } from "vue";
import UiModal from "../ui/UiModal.vue";
import { useBranchMenu } from "../../composables/useBranchMenu.js";
import BranchMenuBrowser from "./BranchMenuBrowser.vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    branchId: { type: Number, default: null },
});

const emit = defineEmits(["update:open", "select"]);

const { menuApi, categories, loadMenu, clearMenu } = useBranchMenu();

watch(
    () => [props.open, props.branchId],
    async ([isOpen, branchId]) => {
        if (!isOpen) {
            return;
        }
        const id = Number(branchId);
        if (!Number.isFinite(id) || id < 1) {
            clearMenu();
            return;
        }
        await loadMenu(id);
    },
);

function close() {
    emit("update:open", false);
}

/**
 * @param {Record<string, unknown>} item
 */
function onSelect(item) {
    emit("select", item);
}
</script>

<template>
    <UiModal :open="open" title="Меню филиала" size="xl" fixed @close="close">
        <BranchMenuBrowser
            class="h-full min-h-0"
            :categories="categories"
            :loading="menuApi.loading"
            layout="sidebar"
            @select="onSelect"
        />
    </UiModal>
</template>
