<script setup>
import { computed } from "vue";
import BaseModal from "../ui/BaseModal.vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useContentStore } from "../../stores/contentStore";
import { useUiStore } from "../../stores/uiStore";
import {
    buildClosedOrdersNotice,
    markClosedNoticeDismissedThisSession,
} from "../../utils/system/closedOrdersNotice";

const cn = useAppDesign().components.closedNotice;
const contentStore = useContentStore();
const uiStore = useUiStore();

const isOpen = computed({
    get() {
        return uiStore.showClosedForOrdersModal;
    },
    set(value) {
        if (value) {
            uiStore.openClosedForOrdersModal();
            return;
        }
        dismiss();
    },
});

const notice = computed(() =>
    buildClosedOrdersNotice(contentStore.profile, new Date()),
);

function dismiss() {
    markClosedNoticeDismissedThisSession();
    uiStore.closeClosedForOrdersModal();
}
</script>

<template>
    <BaseModal v-model="isOpen">
        <div :class="cn.contentWrap">
            <p :class="cn.kicker">
                Закрыто
            </p>
            <h1 :class="cn.title">
                {{ notice.title }}
            </h1>
            <p :class="cn.lead">
                {{ notice.lead }}
            </p>

            <div
                v-if="notice.todayLine"
                :class="cn.todayBlock"
            >
                <p :class="cn.todayLabel">
                    Сегодня
                </p>
                <p :class="cn.todayLine">
                    {{ notice.todayLine }}
                </p>
            </div>

            <div :class="cn.actions">
                <button
                    type="button"
                    :class="cn.confirmBtn"
                    @click="dismiss"
                >
                    Понятно
                </button>
            </div>
        </div>
    </BaseModal>
</template>
