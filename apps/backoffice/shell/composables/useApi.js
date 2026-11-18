import { reactive } from "vue";

/**
 * Единый паттерн loading/error для async-вызовов.
 */
export function useApi() {
    const api = reactive({
        data: null,
        loading: false,
        error: null,
        /**
         * @template T
         * @param {() => Promise<T>} fn
         * @returns {Promise<T>}
         */
        async run(fn) {
            api.loading = true;
            api.error = null;
            try {
                const result = await fn();
                api.data = result;
                return result;
            } catch (err) {
                api.error = err instanceof Error ? err.message : String(err);
                throw err;
            } finally {
                api.loading = false;
            }
        },
    });

    return api;
}
