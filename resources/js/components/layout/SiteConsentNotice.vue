<script setup>
import { ref } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import {
    acceptSiteConsentNotice,
    hasAcceptedSiteConsentNotice,
} from "../../modules/shell/application/siteConsentNotice";

const notice = useAppDesign().components.siteConsentNotice;

const isVisible = ref(!hasAcceptedSiteConsentNotice());

function accept() {
    acceptSiteConsentNotice();
    isVisible.value = false;
}
</script>

<template>
    <div
        v-if="isVisible"
        :class="notice.root"
        role="dialog"
        aria-live="polite"
        aria-label="Уведомление об использовании файлов cookie"
    >
        <div :class="notice.panel">
            <p :class="notice.text">
                Мы используем необходимые cookie для работы сайта.
                Аналитические cookie (Яндекс.Метрика и др.)<span
                    :class="notice.textConsentSuffix"
                >
                    включаем только с вашего согласия</span
                >.
            </p>
            <button
                type="button"
                :class="notice.action"
                @click="accept"
            >
                Принять
            </button>
        </div>
    </div>
</template>
