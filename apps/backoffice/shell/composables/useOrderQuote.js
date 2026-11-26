import { computed, onBeforeUnmount, ref } from "vue";
import { quoteOrder } from "../../api/orders.js";
import { useApi } from "./useApi.js";

/**
 * @param {Record<string, unknown>|null|undefined} line
 */
function quoteLineKind(line) {
    return String(line?.payload?.kind ?? line?.line_type ?? "regular");
}

/**
 * Debounce quote + производные (gift/complements/totals).
 * @param {{
 *   buildBody: () => Record<string, unknown>|null,
 *   canQuote?: () => boolean,
 *   onError?: (message: string) => void,
 * }} options
 */
export function useOrderQuote(options) {
    const quoteApi = useApi();
    const quote = ref(/** @type {Record<string, unknown>|null} */ (null));
    let quoteTimer = /** @type {ReturnType<typeof setTimeout>|null} */ (null);

    const quoteLines = computed(() => {
        const lines = quote.value?.cart?.lines;
        return Array.isArray(lines) ? lines : [];
    });

    const freeComplementLines = computed(() =>
        quoteLines.value.filter((line) => quoteLineKind(line) === "complement"),
    );

    const giftLines = computed(() =>
        quoteLines.value.filter((line) => quoteLineKind(line) === "gift"),
    );

    const mainQuoteLines = computed(() =>
        quoteLines.value.filter((line) => {
            const kind = quoteLineKind(line);
            return kind === "regular" || kind === "set";
        }),
    );

    const giftCandidates = computed(() => {
        const benefits = quote.value?.benefits;
        if (!benefits || typeof benefits !== "object") {
            return [];
        }
        return Array.isArray(benefits.gift_candidates) ? benefits.gift_candidates : [];
    });

    const giftEligible = computed(() => Boolean(quote.value?.benefits?.gift_eligible));

    const totals = computed(() => {
        const source = quote.value?.totals || quote.value?.cart?.totals || {};
        return {
            items: Number(source.items_total_rubles ?? source.items_rubles ?? 0),
            delivery: Number(source.delivery_fee_rubles ?? 0),
            grand: Number(source.grand_total_rubles ?? 0),
            items_kopecks: Number(source.items_total ?? 0),
            delivery_kopecks: Number(source.delivery_fee ?? 0),
            grand_kopecks: Number(source.grand_total ?? 0),
        };
    });

    const quoteInZone = computed(() => {
        const raw = quote.value?.delivery?.in_zone;
        if (raw === true) {
            return true;
        }
        if (raw === false) {
            return false;
        }
        return null;
    });

    async function refreshQuote() {
        if (typeof options.canQuote === "function" && !options.canQuote()) {
            quote.value = null;
            return null;
        }
        const body = options.buildBody();
        if (!body) {
            quote.value = null;
            return null;
        }
        try {
            quote.value = await quoteApi.run(() => quoteOrder(body));
            return quote.value;
        } catch (err) {
            quote.value = null;
            const message = err instanceof Error ? err.message : "Не удалось рассчитать заказ";
            options.onError?.(message);
            throw err;
        }
    }

    function scheduleQuote(delayMs = 320) {
        if (quoteTimer) {
            clearTimeout(quoteTimer);
        }
        quoteTimer = setTimeout(() => {
            refreshQuote().catch(() => {
                /* toast уже в onError */
            });
        }, delayMs);
    }

    function clearQuote() {
        quote.value = null;
        if (quoteTimer) {
            clearTimeout(quoteTimer);
            quoteTimer = null;
        }
    }

    onBeforeUnmount(() => {
        if (quoteTimer) {
            clearTimeout(quoteTimer);
            quoteTimer = null;
        }
    });

    return {
        quoteApi,
        quote,
        quoteLines,
        freeComplementLines,
        giftLines,
        mainQuoteLines,
        giftCandidates,
        giftEligible,
        totals,
        quoteInZone,
        refreshQuote,
        scheduleQuote,
        clearQuote,
    };
}

/**
 * @param {Record<string, unknown>[]} lines
 */
export function mapQuoteLinesToUpdate(lines) {
    return lines
        .map((line) => {
            const kind = quoteLineKind(line);
            return {
                product_id: Number(line.product_id),
                name: String(line.product_name ?? line.name ?? ""),
                unit_price_kopecks: Number(line.unit_price_kopecks ?? 0),
                quantity: Math.max(1, Number(line.quantity) || 1),
                line_type: kind,
            };
        })
        .filter((line) => Number.isFinite(line.product_id) && line.product_id > 0);
}
