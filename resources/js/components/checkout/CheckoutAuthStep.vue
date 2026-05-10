<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const chk = useAppDesign().components.checkout;
const s = chk.shared;

const { checkoutState, handleAuthCompleted, handleContinueAsGuest, goToCart } =
    useCheckoutFlowContext();
const { authTab } = checkoutState;
</script>

<template>
    <div class="space-y-3">
        <p :class="s.introMuted">
            Войди или зарегистрируйся — так удобнее отслеживать заказы и адреса. Либо
            оформи заказ без аккаунта.
        </p>

        <div :class="s.authTabRow">
            <button
                type="button"
                :class="[
                    s.pillRoundText,
                    authTab === 'login' ? s.pillActive : s.pillInactive,
                ]"
                @click="authTab = 'login'"
            >
                Вход
            </button>
            <button
                type="button"
                :class="[
                    s.pillRoundText,
                    authTab === 'register' ? s.pillActive : s.pillInactive,
                ]"
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

        <div :class="chk.auth.footerCol">
            <button
                type="button"
                :class="s.btnSecondaryOutlineCompact"
                @click="handleContinueAsGuest"
            >
                Продолжить без регистрации
            </button>
            <button
                type="button"
                :class="s.linkUnderline"
                @click="goToCart"
            >
                Вернуться к корзине
            </button>
        </div>
    </div>
</template>
