<script setup>
import { useUserStore } from "../../stores/userStore";
import { useProfileDockTabs } from "../../composables/dock/useProfileDockTabs";

const userStore = useUserStore();

const {
    activeTab,
    isAuthenticated,
    handleLoggedIn,
    handleRegistered,
    handleUpdated,
    handleLoggedOut,
    switchToEdit,
    switchToView,
} = useProfileDockTabs(userStore);
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 sm:px-6 lg:px-8 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
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
                    {{ isAuthenticated ? "Вы авторизованы" : "Войдите или зарегистрируйтесь" }}
                </p>
            </div>
        </div>

        <div class="mb-3 flex gap-2 text-[11px] font-medium">
            <button
                v-if="!isAuthenticated"
                type="button"
                class="rounded-full px-3 py-1 transition"
                :class="
                    activeTab === 'login'
                        ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                        : 'bg-white/5 text-slate-200 hover:bg-white/10'
                "
                @click="activeTab = 'login'"
            >
                Вход
            </button>
            <button
                v-if="!isAuthenticated"
                type="button"
                class="rounded-full px-3 py-1 transition"
                :class="
                    activeTab === 'register'
                        ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                        : 'bg-white/5 text-slate-200 hover:bg-white/10'
                "
                @click="activeTab = 'register'"
            >
                Регистрация
            </button>
            <button
                v-if="isAuthenticated"
                type="button"
                class="rounded-full px-3 py-1 transition"
                :class="
                    activeTab === 'view'
                        ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                        : 'bg-white/5 text-slate-200 hover:bg-white/10'
                "
                @click="switchToView"
            >
                Профиль
            </button>
            <button
                v-if="isAuthenticated"
                type="button"
                class="rounded-full px-3 py-1 transition"
                :class="
                    activeTab === 'edit'
                        ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                        : 'bg-white/5 text-slate-200 hover:bg-white/10'
                "
                @click="switchToEdit"
            >
                Редактировать
            </button>
        </div>

        <div class="space-y-3">
            <ClientLoginForm
                v-if="activeTab === 'login'"
                @logged-in="handleLoggedIn"
            />

            <ClientRegisterForm
                v-else-if="activeTab === 'register'"
                @registered="handleRegistered"
            />

            <ClientProfileView
                v-else-if="activeTab === 'view'"
                @logout="handleLoggedOut"
            />

            <ClientProfileEditForm
                v-else-if="activeTab === 'edit'"
                @updated="handleUpdated"
            />
        </div>
    </div>
</template>

<style scoped></style>
