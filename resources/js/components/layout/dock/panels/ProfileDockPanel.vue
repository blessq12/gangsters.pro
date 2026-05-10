<script setup>
import { useAppDesign } from "../../../../design/useAppDesign";
import { useUserStore } from "../../../../stores/userStore";
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

function profileTabClasses(name) {
    const tabs = p.tabs;
    return [
        tabs.base,
        activeTab.value === name ? tabs.active : tabs.inactive,
    ];
}
</script>

<template>
    <div :class="s.shell">
        <div :class="p.headerRow">
            <div :class="p.avatar">
                {{ userStore.profile.name?.[0] || "G" }}
            </div>
            <div>
                <p :class="p.nameLine">
                    {{ userStore.profile.name || "Гость Gangsters" }}
                </p>
                <p :class="s.typography.metaLine">
                    {{ isAuthenticated ? "Личный кабинет" : "Войдите или зарегистрируйтесь" }}
                </p>
            </div>
        </div>

        <!-- Гость: один ряд -->
        <div
            v-if="!isAuthenticated"
            :class="p.tabRow"
        >
            <button
                type="button"
                :class="profileTabClasses(PROFILE_TAB_LOGIN)"
                @click="activeTab = PROFILE_TAB_LOGIN"
            >
                Вход
            </button>
            <button
                type="button"
                :class="profileTabClasses(PROFILE_TAB_REGISTER)"
                @click="activeTab = PROFILE_TAB_REGISTER"
            >
                Регистрация
            </button>
        </div>

        <!-- Авторизован: один ряд, без вложенных табов внутри контента -->
        <div
            v-else
            :class="p.tabRow"
        >
            <button
                type="button"
                :class="profileTabClasses(PROFILE_TAB_OVERVIEW)"
                @click="switchToOverview"
            >
                Обзор
            </button>
            <button
                type="button"
                :class="profileTabClasses(PROFILE_TAB_ADDRESSES)"
                @click="activeTab = PROFILE_TAB_ADDRESSES"
            >
                Адреса
            </button>
            <button
                type="button"
                :class="profileTabClasses(PROFILE_TAB_ORDERS)"
                @click="activeTab = PROFILE_TAB_ORDERS"
            >
                Заказы
            </button>
            <button
                type="button"
                :class="profileTabClasses(PROFILE_TAB_EDIT)"
                @click="switchToEdit"
            >
                Данные
            </button>
        </div>

        <div :class="p.contentStack">
            <ClientLoginForm
                v-if="activeTab === PROFILE_TAB_LOGIN"
                @logged-in="handleLoggedIn"
            />

            <ClientRegisterForm
                v-else-if="activeTab === PROFILE_TAB_REGISTER"
                @registered="handleRegistered"
            />

            <ClientProfileView
                v-else-if="activeTab === PROFILE_TAB_OVERVIEW"
                @logout="handleLoggedOut"
            />

            <div v-else-if="activeTab === PROFILE_TAB_ADDRESSES">
                <p :class="s.typography.sectionLabelUppercase">
                    Адреса доставки
                </p>
                <ClientAddressesManager />
            </div>

            <div
                v-else-if="activeTab === PROFILE_TAB_ORDERS"
                :class="p.ordersMinHeight"
            >
                <ClientOrderHistory />
            </div>

            <ClientProfileEditForm
                v-else-if="activeTab === PROFILE_TAB_EDIT"
                @updated="handleUpdated"
            />
        </div>
    </div>
</template>

<style scoped></style>
