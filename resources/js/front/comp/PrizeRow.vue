<script>
import gsap from "gsap";
import { mapStores } from "pinia";
import { localStore } from "../../stores/localStore";

export default {
    name: "PrizeRow",
    data() {
        return {
            isVisible: false,
        };
    },
    computed: {
        ...mapStores(localStore),
        progressPercentage() {
            const total = this.localStore.cartTotal;
            if (total <= 0) return 0;
            if (total >= 2700) return 100;
            return (total / 2700) * 100;
        },
        barColor() {
            const total = this.localStore.cartTotal;
            if (total >= 1700) return "bg-green-500";
            if (total >= 500) return "bg-yellow-500";
            return "bg-blue-500";
        },
        isPromoActive() {
            const today = new Date();
            const dayOfWeek = today.getDay(); // 0 = воскресенье, 1 = понедельник, ..., 6 = суббота
            return dayOfWeek >= 1 && dayOfWeek <= 4; // Понедельник (1) - Четверг (4)
        },
    },
    mounted() {
        // Инициализация GSAP анимации
        gsap.set(this.$refs.progressBar, {
            yPercent: -100,
            opacity: 0,
        });

        // Проверяем начальное состояние
        this.updateBarVisibility();
    },
    methods: {
        updateBarVisibility() {
            const shouldBeVisible = this.localStore.cartTotal > 0;

            if (shouldBeVisible && !this.isVisible) {
                gsap.to(this.$refs.progressBar, {
                    yPercent: 0,
                    opacity: 1,
                    duration: 0.6,
                    ease: "back.out(1.7)",
                });
                this.isVisible = true;
            } else if (!shouldBeVisible && this.isVisible) {
                // Скрываем бар наверх
                gsap.to(this.$refs.progressBar, {
                    yPercent: -100,
                    opacity: 0,
                    duration: 0.4,
                    ease: "power2.in",
                });
                this.isVisible = false;
            }
        },
    },
    watch: {
        "localStore.cartTotal": {
            handler() {
                this.updateBarVisibility();
            },
            immediate: true,
        },
    },
};
</script>

<template>
    <div
        v-if="isPromoActive"
        ref="progressBar"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-h-[20px] sticky top-24 md:top-26 z-50"
    >
        <!-- Интерактивный бар с цифрами -->
        <div
            class="relative w-full bg-gray-200 rounded-full h-8 overflow-hidden"
        >
            <!-- Заполнение бара -->
            <div
                class="h-full rounded-full transition-all duration-500 ease-out relative"
                :class="barColor"
                :style="{ width: progressPercentage + '%' }"
            ></div>

            <!-- Иконки подарков на позициях целей -->
            <div
                class="absolute top-0 left-0 w-full h-full pointer-events-none"
            >
                <!-- Подарок на 1700₽ -->
                <div
                    class="absolute -top-6 transform -translate-x-1/2 transition-all duration-300"
                    :class="{
                        'scale-125 animate-bounce':
                            localStore.cartTotal >= 1700,
                    }"
                    :style="{ left: (1700 / 2700) * 100 + '%' }"
                >
                    <div class="text-lg">
                        <span
                            v-if="localStore.cartTotal < 1700"
                            class="text-gray-400"
                            >🎁</span
                        >
                        <span v-else class="text-green-500">🎁</span>
                    </div>
                </div>

                <!-- Подарок на 2700₽ -->
                <div
                    class="absolute -top-6 transform -translate-x-1/2 transition-all duration-300"
                    :class="{
                        'scale-125 animate-bounce':
                            localStore.cartTotal >= 2700,
                    }"
                    :style="{ left: '100%' }"
                ></div>
            </div>

            <!-- Мотивационные подсказки -->
            <div
                v-if="localStore.cartTotal > 0 && localStore.cartTotal < 1700"
                class="absolute inset-0 flex items-center justify-center"
            >
                <div
                    class="animate-pulse rounded-full px-3 py-1 text-xs font-bold text-blue-700"
                >
                    🍣 +{{
                        Math.ceil((1700 - localStore.cartTotal) / 100) * 100
                    }}₽ = 1 набор роллов в подарок!
                </div>
            </div>

            <!-- Анимация при достижении 1700 -->
            <div
                v-if="
                    localStore.cartTotal >= 1700 && localStore.cartTotal < 2700
                "
                class="absolute inset-0 flex items-center justify-center"
            >
                <div
                    class="animate-pulse rounded-full px-3 py-1 text-xs font-bold text-green-700"
                >
                    🎉 1 набор роллов в подарок!
                </div>
            </div>

            <!-- Анимация при достижении 2700 -->
            <div
                v-if="localStore.cartTotal >= 2700"
                class="absolute inset-0 flex items-center justify-center"
            >
                <div
                    class="animate-bounce rounded-full px-3 py-1 text-xs font-bold text-white"
                >
                    🏆 2 набора роллов в подарок!
                </div>
            </div>
        </div>
    </div>
</template>

<style></style>
