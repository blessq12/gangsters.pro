<script setup>
import { computed } from "vue";
import { siteMeta } from "../../config/siteMeta";
import { useAppDesign } from "../../design/useAppDesign";
import { usePwaInstallBanner } from "../../features/pwa/usePwaInstallBanner";

const props = defineProps({
    visible: {
        type: Boolean,
        default: true,
    },
});

const pwa = useAppDesign().components.pwaInstall;
const { installMode, shouldShowBanner, dismiss, promptInstall } =
    usePwaInstallBanner();

const showBanner = computed(() => props.visible && shouldShowBanner.value);

const bannerText = computed(() => {
    if (installMode.value === "iosHint") {
        return `Установите ${siteMeta.pwaDisplayName} на главный экран: «Поделиться» → «На экран Домой».`;
    }
    return `Установите ${siteMeta.pwaDisplayName} — быстрый доступ с главного экрана`;
});

function onPrimaryAction() {
    if (installMode.value === "chromiumInstall") {
        void promptInstall();
        return;
    }
    dismiss();
}
</script>

<template>
    <div
        v-if="showBanner"
        :class="pwa.fixedRoot"
        role="region"
        aria-label="Установка приложения"
    >
        <div :class="pwa.inner">
            <div :class="pwa.bar">
                <p :class="pwa.text">
                    {{ bannerText }}
                </p>
                <div :class="pwa.actions">
                    <button
                        v-if="installMode === 'chromiumInstall'"
                        type="button"
                        :class="pwa.installButton"
                        @click="onPrimaryAction"
                    >
                        Установить
                    </button>
                    <button
                        v-else
                        type="button"
                        :class="pwa.installButton"
                        @click="onPrimaryAction"
                    >
                        Понятно
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
