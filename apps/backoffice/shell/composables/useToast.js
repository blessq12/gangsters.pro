import { reactive } from "vue";

const state = reactive({
    /** @type {{id: number, variant: 'success'|'error', message: string}[]} */
    items: [],
});

let seq = 0;

/**
 * @param {'success'|'error'} variant
 * @param {string} message
 */
function push(variant, message) {
    const id = ++seq;
    state.items.push({ id, variant, message: String(message) });
    window.setTimeout(() => {
        const idx = state.items.findIndex((item) => item.id === id);
        if (idx >= 0) {
            state.items.splice(idx, 1);
        }
    }, 4000);
}

export function useToast() {
    return {
        items: state.items,
        success(message) {
            push("success", message);
        },
        error(message) {
            push("error", message);
        },
        dismiss(id) {
            const idx = state.items.findIndex((item) => item.id === id);
            if (idx >= 0) {
                state.items.splice(idx, 1);
            }
        },
    };
}
