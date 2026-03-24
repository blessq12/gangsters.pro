<script setup>
import { useUiStore } from "../../stores/uiStore";

const uiStore = useUiStore();
</script>

<template>
    <Transition name="mobile-menu-fade">
        <div
            v-if="uiStore.isMobileMenuOpen"
            class="pointer-events-none fixed inset-x-0 top-20 z-30 md:hidden"
        >
            <div class="pointer-events-auto mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <nav
                    class="space-y-1 rounded-2xl border border-amber-400/30 bg-[rgba(0,0,0,0.9)] px-4 py-3 text-sm font-medium text-slate-50 shadow-[0_20px_50px_rgba(0,0,0,0.9)] backdrop-blur"
                >
                    <RouterLink
                        :to="{ name: 'home' }"
                        class="block rounded-xl px-3 py-2 hover:bg-white/5"
                        @click="uiStore.setMobileMenuOpen(false)"
                    >
                        Главная
                    </RouterLink>
                    <RouterLink
                        :to="{ name: 'about' }"
                        class="block rounded-xl px-3 py-2 hover:bg-white/5"
                        @click="uiStore.setMobileMenuOpen(false)"
                    >
                        О компании
                    </RouterLink>
                    <RouterLink
                        :to="{ name: 'delivery' }"
                        class="block rounded-xl px-3 py-2 hover:bg-white/5"
                        @click="uiStore.setMobileMenuOpen(false)"
                    >
                        Оплата и доставка
                    </RouterLink>
                    <RouterLink
                        :to="{ name: 'contacts' }"
                        class="block rounded-xl px-3 py-2 hover:bg-white/5"
                        @click="uiStore.setMobileMenuOpen(false)"
                    >
                        Контакты
                    </RouterLink>
                </nav>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.mobile-menu-fade-enter-active,
.mobile-menu-fade-leave-active {
    transition: opacity 0.2s ease;
}
.mobile-menu-fade-enter-from,
.mobile-menu-fade-leave-to {
    opacity: 0;
}
</style>
