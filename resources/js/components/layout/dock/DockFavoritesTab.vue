<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useAppDesign } from "../../../design/useAppDesign";
import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../../platform/domainEvents";
import { useUiStore } from "../../../modules/shell/store/uiStore";

const FLASH_MS = 1000;

const props = defineProps({
    iconClass: {
        type: String,
        required: true,
    },
});

const emit = defineEmits({
    toggle: () => true,
});

const dock = useAppDesign().components.dock;
const ft = dock.favoritesTab;
const uiStore = useUiStore();

const justAdded = ref(false);
let flashTimer = null;
let unsubscribeAdd = null;

const isActive = computed(() => uiStore.dockActiveId === "favorites");

const wrapClass = computed(() => {
    if (justAdded.value) {
        return [ft.wrap, ft.wrapFlash];
    }
    return [
        ft.wrap,
        isActive.value
            ? dock.shared.tabIconActive
            : dock.shared.tabIconInactive,
    ];
});

function clearFlashTimer() {
    if (flashTimer != null) {
        clearTimeout(flashTimer);
        flashTimer = null;
    }
}

function showAddedFlash() {
    justAdded.value = true;
    clearFlashTimer();
    flashTimer = setTimeout(() => {
        justAdded.value = false;
        flashTimer = null;
    }, FLASH_MS);
}

function onClick() {
    emit("toggle");
}

onMounted(() => {
    unsubscribeAdd = subscribeDomainEvent(
        DOMAIN_EVENTS.FAVORITE_ADD_REQUESTED,
        () => {
            showAddedFlash();
        },
    );
});

onUnmounted(() => {
    clearFlashTimer();
    if (unsubscribeAdd) {
        unsubscribeAdd();
        unsubscribeAdd = null;
    }
});
</script>

<template>
    <button
        type="button"
        :class="dock.chrome.tabButton"
        title="Избранное"
        :aria-label="justAdded ? 'Добавлено в избранное' : 'Избранное'"
        :aria-pressed="isActive"
        data-dock-target="favorites"
        @click="onClick"
    >
        <span
            :class="wrapClass"
            data-dock-bump-root="favorites"
        >
            <span
                :class="[
                    ft.idleLayer,
                    justAdded ? ft.idleLayerHidden : '',
                ]"
            >
                <i
                    :class="['mdi', props.iconClass, ft.iconMdiSize]"
                    aria-hidden="true"
                />
            </span>

            <Transition name="dock-fav-added">
                <span
                    v-if="justAdded"
                    :class="ft.flashLayer"
                >
                    <i
                        :class="ft.flashIcon"
                        aria-hidden="true"
                    />
                </span>
            </Transition>
        </span>
    </button>
</template>

<style scoped>
.dock-fav-added-enter-active,
.dock-fav-added-leave-active {
    transition: transform 0.22s ease;
}

.dock-fav-added-enter-from {
    transform: translateY(-100%);
}

.dock-fav-added-leave-to {
    transform: translateY(100%);
}

.dock-fav-added-enter-to,
.dock-fav-added-leave-from {
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    .dock-fav-added-enter-active,
    .dock-fav-added-leave-active {
        transition: none;
    }
}
</style>
