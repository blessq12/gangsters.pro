<script setup>
import { useUserStore } from "../../stores/userStore";
import {
    PROFILE_TAB_ADDRESSES,
    PROFILE_TAB_EDIT,
    PROFILE_TAB_LOGIN,
    PROFILE_TAB_ORDERS,
    PROFILE_TAB_OVERVIEW,
    PROFILE_TAB_REGISTER,
    useProfileDockTabs,
} from "../../composables/dock/useProfileDockTabs";

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

const tabClass = (name) => [
    "rounded-full px-3 py-1 transition",
    activeTab.value === name
        ? "bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]"
        : "bg-white/5 text-slate-200 hover:bg-white/10",
];
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
    >
        <div class="flex items-center gap-2 mb-3">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-full border border-amber-400/40 bg-black/70 text-sm font-semibold text-amber-200 shadow-[0_0_16px_rgba(251,191,36,0.6)]"
            >
                {{ userStore.profile.name?.[0] || "G" }}
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-50">
                    {{ userStore.profile.name || "Гость Gangsters" }}
                </p>
                <p class="text-[11px] text-slate-400">
                    {{ isAuthenticated ? "Личный кабинет" : "Войдите или зарегистрируйтесь" }}
                </p>
            </div>
        </div>

        <!-- Гость: один ряд -->
        <div
            v-if="!isAuthenticated"
            class="mb-3 flex flex-wrap gap-2 text-[11px] font-medium"
        >
            <button
                type="button"
                :class="tabClass(PROFILE_TAB_LOGIN)"
                @click="activeTab = PROFILE_TAB_LOGIN"
            >
                Вход
            </button>
            <button
                type="button"
                :class="tabClass(PROFILE_TAB_REGISTER)"
                @click="activeTab = PROFILE_TAB_REGISTER"
            >
                Регистрация
            </button>
        </div>

        <!-- Авторизован: один ряд, без вложенных табов внутри контента -->
        <div
            v-else
            class="mb-3 flex flex-wrap gap-2 text-[11px] font-medium"
        >
            <button
                type="button"
                :class="tabClass(PROFILE_TAB_OVERVIEW)"
                @click="switchToOverview"
            >
                Обзор
            </button>
            <button
                type="button"
                :class="tabClass(PROFILE_TAB_ADDRESSES)"
                @click="activeTab = PROFILE_TAB_ADDRESSES"
            >
                Адреса
            </button>
            <button
                type="button"
                :class="tabClass(PROFILE_TAB_ORDERS)"
                @click="activeTab = PROFILE_TAB_ORDERS"
            >
                Заказы
            </button>
            <button
                type="button"
                :class="tabClass(PROFILE_TAB_EDIT)"
                @click="switchToEdit"
            >
                Данные
            </button>
        </div>

        <div class="space-y-3">
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
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-400">
                    Адреса доставки
                </p>
                <ClientAddressesManager />
            </div>

            <div
                v-else-if="activeTab === PROFILE_TAB_ORDERS"
                class="min-h-[12rem]"
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

