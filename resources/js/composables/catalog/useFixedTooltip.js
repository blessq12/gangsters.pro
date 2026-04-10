import { nextTick, onMounted, onUnmounted, reactive, ref } from "vue";

const VIEWPORT_PADDING = 8;
const TOOLTIP_OFFSET = 8;

export function useFixedTooltip() {
    const tooltipRef = ref(null);
    const anchorRef = ref(null);
    const tooltipStyle = reactive({
        top: "0px",
        left: "0px",
        visibility: "hidden",
    });

    const updateTooltipPosition = () => {
        if (!anchorRef.value || !tooltipRef.value || typeof window === "undefined") return;

        const anchorRect = anchorRef.value.getBoundingClientRect();
        const tooltipRect = tooltipRef.value.getBoundingClientRect();

        let left = anchorRect.left;
        let top = anchorRect.top - tooltipRect.height - TOOLTIP_OFFSET;

        if (left + tooltipRect.width > window.innerWidth - VIEWPORT_PADDING) {
            left = window.innerWidth - tooltipRect.width - VIEWPORT_PADDING;
        }
        if (left < VIEWPORT_PADDING) {
            left = VIEWPORT_PADDING;
        }
        if (top < VIEWPORT_PADDING) {
            top = anchorRect.bottom + TOOLTIP_OFFSET;
        }

        tooltipStyle.left = `${Math.round(left)}px`;
        tooltipStyle.top = `${Math.round(top)}px`;
        tooltipStyle.visibility = "visible";
    };

    const openAt = async (anchorEl) => {
        anchorRef.value = anchorEl ?? null;
        tooltipStyle.visibility = "hidden";
        await nextTick();
        updateTooltipPosition();
    };

    const close = () => {
        anchorRef.value = null;
        tooltipStyle.visibility = "hidden";
    };

    const onViewportChange = () => {
        if (!anchorRef.value) return;
        updateTooltipPosition();
    };

    onMounted(() => {
        window.addEventListener("resize", onViewportChange, { passive: true });
        window.addEventListener("scroll", onViewportChange, true);
    });

    onUnmounted(() => {
        window.removeEventListener("resize", onViewportChange);
        window.removeEventListener("scroll", onViewportChange, true);
    });

    return {
        tooltipRef,
        tooltipStyle,
        openAt,
        close,
    };
}
