<script setup>
import { computed, ref, watch } from "vue";
import { useBenefitProgress } from "../../../features/shoppingSession/useBenefitProgress";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../../shared/domainEvents";
import { useAppDesign } from "../../../design/useAppDesign";
import {
    playDockContentHide,
    playDockContentShow,
    playTopBenefitsHide,
    playTopBenefitsShow,
} from "../../../animations/animationManager";

const props = defineProps({
    showIntro: {
        type: Boolean,
        default: false,
    },
    bottomBarReady: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: "desktop",
    },
});

const benefitProgress = useBenefitProgress();
const design = useAppDesign().components.topBenefits;
const benefitRows = computed(() =>
    [
        {
            key: "delivery",
            active: Boolean(benefitProgress.delivery.value.isActive),
            label: benefitProgress.deliveryLabel.value,
            progress: benefitProgress.deliveryProgressPercent.value,
        },
        {
            key: "gift",
            active: Boolean(benefitProgress.gift.value.isActive),
            label: benefitProgress.giftLabel.value,
            progress: benefitProgress.giftProgressPercent.value,
        },
    ].filter((row) => row.active && row.label),
);

const isVisible = computed(
    () =>
        benefitProgress.canShowBenefitsBanner.value &&
        props.showIntro === false &&
        props.bottomBarReady === true,
);
const bannerMessage = computed(
    () => benefitRows.value.map((row) => row.label).join(" | "),
);
const hasShownEvent = ref(false);

const containerClass = computed(() =>
    props.variant === "mobile"
        ? design.mobileOffset
        : design.desktopOffset,
);

function progressWidth(value) {
    const safe = Number(value) || 0;
    return `${Math.min(100, Math.max(0, safe))}%`;
}

function handleContainerEnter(el, done) {
    playTopBenefitsShow(el, done, props.variant === "desktop" ? "desktop" : "mobile");
}

function handleContainerLeave(el, done) {
    playTopBenefitsHide(el, done, props.variant === "desktop" ? "desktop" : "mobile");
}

function handleRowEnter(el, done) {
    playDockContentShow(el, done, props.variant === "desktop" ? "desktop" : "mobile");
}

function handleRowLeave(el, done) {
    playDockContentHide(el, done, props.variant === "desktop" ? "desktop" : "mobile");
}

watch(
    isVisible,
    (visible) => {
        if (!visible || hasShownEvent.value) {
            return;
        }
        emitDomainEvent(DOMAIN_EVENTS.BENEFIT_BANNER_SHOWN, {
            source: "top-fixed",
            message: bannerMessage.value,
        });
        hasShownEvent.value = true;
    },
    { immediate: true },
);
</script>

<template>
    <Transition
        @enter="handleContainerEnter"
        @leave="handleContainerLeave"
    >
        <div
            v-if="isVisible"
            :class="[design.root, containerClass]"
        >
            <div :class="design.inner">
                <div :class="design.bar">
                    <TransitionGroup
                        tag="div"
                        :class="design.rows"
                        @enter="handleRowEnter"
                        @leave="handleRowLeave"
                    >
                        <div
                            v-for="row in benefitRows"
                            :key="row.key"
                            :class="design.row"
                        >
                            <p :class="design.label">{{ row.label }}</p>
                            <div :class="design.track">
                                <div
                                    :class="design.fill"
                                    :style="{ width: progressWidth(row.progress) }"
                                />
                            </div>
                        </div>
                    </TransitionGroup>
                </div>
            </div>
        </div>
    </Transition>
</template>
