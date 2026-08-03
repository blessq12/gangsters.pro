<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../../design/useAppDesign";
import { useUiStore } from "../../../modules/shell/store/uiStore";
import { useUserStore } from "../../../modules/client/store/userStore";

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
const pt = dock.profileTab;
const uiStore = useUiStore();
const userStore = useUserStore();
const { profile, token } = storeToRefs(userStore);

const isAuthenticated = computed(
    () => Boolean(token.value) && Boolean(profile.value?.id),
);

const isActive = computed(() => uiStore.dockActiveId === "profile");

const monogram = computed(() => {
    const name = String(profile.value?.name || "").trim();
    if (!name) {
        return "G";
    }
    return name.charAt(0).toLocaleUpperCase("ru-RU");
});

const wrapClass = computed(() => {
    if (isAuthenticated.value) {
        return [pt.wrap, pt.wrapAuthed];
    }
    return [
        pt.wrap,
        isActive.value
            ? dock.shared.tabIconActive
            : dock.shared.tabIconInactive,
    ];
});

function onClick() {
    emit("toggle");
}
</script>

<template>
    <button
        type="button"
        :class="dock.chrome.tabButton"
        title="Профиль"
        :aria-label="
            isAuthenticated
                ? `Профиль: ${profile.name || monogram}`
                : 'Профиль'
        "
        :aria-pressed="isActive"
        data-dock-target="profile"
        @click="onClick"
    >
        <span
            :class="wrapClass"
            data-dock-bump-root="profile"
        >
            <span
                v-if="isAuthenticated"
                :class="pt.monogram"
                aria-hidden="true"
            >
                {{ monogram }}
            </span>
            <i
                v-else
                :class="['mdi', props.iconClass, pt.iconMdiSize]"
                aria-hidden="true"
            />
        </span>
    </button>
</template>
