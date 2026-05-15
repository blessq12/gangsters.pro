<script setup>
import { siteMeta } from "../../config/siteMeta";
import { useAppDesign } from "../../design/useAppDesign";
import { usePwaInstallPrompt } from "../../features/pwa/usePwaInstallPrompt";

defineProps({
    visible: {
        type: Boolean,
        default: true,
    },
});

const pwa = useAppDesign().components.pwaInstall;
const { canInstall, dismiss, promptInstall } = usePwaInstallPrompt();
</script>

<template>
    <div
        v-if="visible && canInstall"
        :class="pwa.fixedRoot"
        role="region"
        aria-label="Установка приложения"
    >
        <div :class="pwa.inner">
            <div :class="pwa.bar">
                <p :class="pwa.text">
                    Установи {{ siteMeta.name }} — быстрый доступ с главного экрана
                </p>
                <div :class="pwa.actions">
                    <button
                        type="button"
                        :class="pwa.installButton"
                        @click="promptInstall"
                    >
                        Установить
                    </button>
                    <button
                        type="button"
                        :class="pwa.dismissButton"
                        @click="dismiss"
                    >
                        Позже
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
