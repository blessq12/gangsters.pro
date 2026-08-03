<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../../../design/useAppDesign";
import { useUserStore } from "../../../../modules/client/store/userStore";
import {
    PROFILE_TAB_ADDRESSES,
    PROFILE_TAB_EDIT,
    PROFILE_TAB_LOGIN,
    PROFILE_TAB_ORDERS,
    PROFILE_TAB_OVERVIEW,
    PROFILE_TAB_REGISTER,
    useProfileDockTabs,
} from "../composables/useProfileDockTabs";

const panels = useAppDesign().components.dockPanels;

const userStore = useUserStore();

const {
    activeTab,
    isAuthenticated,
    handleLoggedIn,
    handleRegistered,
    handleUpdated,
    handleLoggedOut,
    switchToOverview,
    switchToEdit,
} = useProfileDockTabs(userStore);

const s = panels.shared;
const p = panels.profile;

const panelTitle = computed(() =>
    isAuthenticated.value
        ? userStore.profile.name || "Гость"
        : "Стань частью семьи",
);

const panelSubtitle = computed(() => {
    if (!isAuthenticated.value) {
        return "";
    }
    return userStore.profile.phone || "";
});

function segmentCellClasses(name) {
    const a = p.segmentSelector;
    return [
        a.cell,
        a.cellDivider,
        activeTab.value === name ? a.cellSelected : a.cellIdle,
    ];
}

function logout() {
    userStore.clearAuth();
    handleLoggedOut();
}
</script>

<template>
    <DockPanelLayout
        :title="panelTitle"
        :description="panelSubtitle"
    >
        <template
            v-if="isAuthenticated"
            #headerActions
        >
            <button
                type="button"
                :class="p.headerLogout"
                @click="logout"
            >
                Выйти
            </button>
        </template>
        <div
            v-if="!isAuthenticated"
            :class="[p.segmentSelector.shell, p.segmentSelector.shellGuest]"
            role="tablist"
            aria-label="Вход или регистрация"
        >
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === PROFILE_TAB_LOGIN"
                :class="segmentCellClasses(PROFILE_TAB_LOGIN)"
                @click="activeTab = PROFILE_TAB_LOGIN"
            >
                Вход
            </button>
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === PROFILE_TAB_REGISTER"
                :class="segmentCellClasses(PROFILE_TAB_REGISTER)"
                @click="activeTab = PROFILE_TAB_REGISTER"
            >
                Регистрация
            </button>
        </div>

        <div
            v-else
            :class="p.segmentSelector.shell"
            role="tablist"
            aria-label="Личный кабинет"
        >
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === PROFILE_TAB_OVERVIEW"
                :class="segmentCellClasses(PROFILE_TAB_OVERVIEW)"
                @click="switchToOverview"
            >
                Обзор
            </button>
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === PROFILE_TAB_ADDRESSES"
                :class="segmentCellClasses(PROFILE_TAB_ADDRESSES)"
                @click="activeTab = PROFILE_TAB_ADDRESSES"
            >
                Адреса
            </button>
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === PROFILE_TAB_ORDERS"
                :class="segmentCellClasses(PROFILE_TAB_ORDERS)"
                @click="activeTab = PROFILE_TAB_ORDERS"
            >
                Заказы
            </button>
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === PROFILE_TAB_EDIT"
                :class="segmentCellClasses(PROFILE_TAB_EDIT)"
                @click="switchToEdit"
            >
                Данные
            </button>
        </div>

        <div :class="s.contentStack">
            <ClientLoginForm
                v-if="activeTab === PROFILE_TAB_LOGIN"
                @logged-in="handleLoggedIn"
                @go-register="activeTab = PROFILE_TAB_REGISTER"
            />

            <ClientRegisterForm
                v-else-if="activeTab === PROFILE_TAB_REGISTER"
                @registered="handleRegistered"
            />

            <ClientProfileView
                v-else-if="activeTab === PROFILE_TAB_OVERVIEW"
            />

            <ClientAddressesManager
                v-else-if="activeTab === PROFILE_TAB_ADDRESSES"
            />

            <div
                v-else-if="activeTab === PROFILE_TAB_ORDERS"
                :class="p.ordersMinHeight"
            >
                <ClientOrderHistory />
            </div>

            <ClientProfileEditForm
                v-else-if="activeTab === PROFILE_TAB_EDIT"
                @updated="handleUpdated"
                @logout="handleLoggedOut"
            />
        </div>
    </DockPanelLayout>
</template>

<style scoped></style>
