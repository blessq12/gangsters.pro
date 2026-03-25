import { computed, onMounted, onUnmounted, ref } from "vue";
import {
    getCompanyOpenStatusHint,
    isCompanyOpenNow,
} from "../../utils/system/companyOpenStatus";

const TICK_MS = 60_000;

/**
 * Реактивный статус открытости по данным компании (обновляется раз в минуту).
 * @param {() => object|null|undefined} getCompany
 */
export function useCompanyOpenStatus(getCompany) {
    const tick = ref(0);
    let timerId = null;

    onMounted(() => {
        timerId = window.setInterval(() => {
            tick.value += 1;
        }, TICK_MS);
    });

    onUnmounted(() => {
        if (timerId != null) {
            window.clearInterval(timerId);
        }
    });

    const openNow = computed(() => {
        void tick.value;
        const c = getCompany();
        return isCompanyOpenNow(c, new Date());
    });

    const statusHint = computed(() => {
        void tick.value;
        const c = getCompany();
        return getCompanyOpenStatusHint(c, new Date());
    });

    return { openNow, statusHint, tick };
}
