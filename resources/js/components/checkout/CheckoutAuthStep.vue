<script setup>
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const { authTab, handleAuthCompleted, goToCart } = useCheckoutFlowContext();
</script>

<template>
    <div class="space-y-3">
        <p class="text-xs text-slate-300">
            Для оформления заказа нужен личный кабинет. Войди или зарегистрируйся —
            это займёт минуту.
        </p>

        <div class="flex gap-2 text-[11px] font-medium">
            <button
                type="button"
                class="rounded-full px-3 py-1 transition"
                :class="
                    authTab === 'login'
                        ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                        : 'bg-white/5 text-slate-200 hover:bg-white/10'
                "
                @click="authTab = 'login'"
            >
                Вход
            </button>
            <button
                type="button"
                class="rounded-full px-3 py-1 transition"
                :class="
                    authTab === 'register'
                        ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                        : 'bg-white/5 text-slate-200 hover:bg-white/10'
                "
                @click="authTab = 'register'"
            >
                Регистрация
            </button>
        </div>

        <div class="space-y-3">
            <ClientLoginForm
                v-if="authTab === 'login'"
                @logged-in="handleAuthCompleted"
            />

            <ClientRegisterForm
                v-else
                @registered="handleAuthCompleted"
            />
        </div>

        <div class="mt-2 flex justify-between text-[11px] text-slate-400">
            <button
                type="button"
                class="underline-offset-2 hover:underline"
                @click="goToCart"
            >
                Вернуться к корзине
            </button>
        </div>
    </div>
</template>
