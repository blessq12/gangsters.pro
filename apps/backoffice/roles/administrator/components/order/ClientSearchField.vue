<script setup>
import { computed, ref, watch } from "vue";
import { listClients } from "../../../../api/clients.js";
import { useApi } from "../../../../shell/composables/useApi.js";
import {
    formatRuPhoneCanonical,
    isRuPhoneComplete,
    normalizeRuPhoneDigits,
} from "../../../../shell/phone/ruPhone.js";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";

const props = defineProps({
    name: { type: String, default: "" },
    phone: { type: String, default: "" },
    clientId: { type: [Number, String, null], default: null },
    nameError: { type: String, default: "" },
    phoneError: { type: String, default: "" },
});

const emit = defineEmits(["update:name", "update:phone", "select-client", "clear-client"]);

const searchApi = useApi();
const hits = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const open = ref(false);
const searching = ref(false);
/** Цифры последнего завершённого поиска (полный номер). */
const lastSearchedDigits = ref("");
let timer = /** @type {ReturnType<typeof setTimeout>|null} */ (null);

const nameModel = computed({
    get: () => props.name,
    set: (value) => emit("update:name", value),
});

const phoneModel = computed({
    get: () => props.phone,
    set: (value) => emit("update:phone", value),
});

const selected = computed(() => props.clientId != null && props.clientId !== "");

const phoneDigits = computed(() => normalizeRuPhoneDigits(props.phone));

const phoneComplete = computed(() => isRuPhoneComplete(props.phone));

/** Полный номер искали, хитов нет — режим нового клиента. */
const showNewClient = computed(
    () =>
        !selected.value
        && phoneComplete.value
        && lastSearchedDigits.value === phoneDigits.value
        && !searching.value
        && hits.value.length === 0,
);

watch(
    () => props.phone,
    (value) => {
        if (selected.value) {
            return;
        }
        if (timer) {
            clearTimeout(timer);
        }
        timer = setTimeout(() => runSearch(value), 280);
    },
);

watch(
    () => props.clientId,
    (id) => {
        if (!id) {
            return;
        }
        open.value = false;
        hits.value = [];
        lastSearchedDigits.value = "";
    },
);

watch(
    [phoneComplete, hits, selected],
    () => {
        if (selected.value) {
            return;
        }
        if (!phoneComplete.value || hits.value.length > 0) {
            if (props.name) {
                emit("update:name", "");
            }
        }
    },
);

/**
 * @param {string|null|undefined} value
 */
async function runSearch(value) {
    const digits = normalizeRuPhoneDigits(value);
    if (!isRuPhoneComplete(digits)) {
        hits.value = [];
        open.value = false;
        searching.value = false;
        lastSearchedDigits.value = "";
        return;
    }

    searching.value = true;
    try {
        const payload = await searchApi.run(() => listClients({ q: digits, limit: 8, offset: 0 }));
        const items = Array.isArray(payload?.items) ? payload.items : [];
        hits.value = items;
        open.value = items.length > 0;
        lastSearchedDigits.value = digits;
    } catch {
        hits.value = [];
        open.value = false;
        lastSearchedDigits.value = digits;
    } finally {
        searching.value = false;
    }
}

/**
 * @param {Record<string, unknown>} client
 */
function pick(client) {
    const rawPhone = String(client.phone ?? "");
    const formatted = formatRuPhoneCanonical(rawPhone) || rawPhone;
    emit("select-client", {
        id: Number(client.id),
        name: String(client.name ?? ""),
        phone: formatted,
    });
    hits.value = [];
    open.value = false;
    lastSearchedDigits.value = "";
}

function clearSelected() {
    emit("clear-client");
    hits.value = [];
    open.value = false;
    lastSearchedDigits.value = "";
}
</script>

<template>
    <div class="space-y-3">
        <div
            v-if="selected"
            class="flex flex-wrap items-center justify-between gap-2 border border-bo-border bg-bo-field px-3 py-2 text-sm"
        >
            <div>
                <div class="text-bo-fg">
                    {{ name || "Клиент" }}
                </div>
                <div class="text-xs text-bo-muted">
                    {{ phone || "—" }} · выбранный клиент
                </div>
            </div>
            <button
                type="button"
                class="text-sm text-bo-muted underline-offset-2 hover:text-bo-fg hover:underline"
                @click="clearSelected"
            >
                Сбросить
            </button>
        </div>

        <template v-else>
            <UiFormField
                label="Телефон"
                required
                :error="phoneError"
            >
                <div class="relative">
                    <UiInput
                        v-model="phoneModel"
                        type="tel"
                        autocomplete="off"
                    />
                    <div
                        v-if="open"
                        class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-bo-border bg-bo-elevated shadow-bo"
                    >
                        <button
                            v-for="client in hits"
                            :key="String(client.id)"
                            type="button"
                            class="flex w-full flex-col gap-0.5 px-3 py-2.5 text-left text-sm transition-colors hover:bg-white/[0.06]"
                            @click="pick(client)"
                        >
                            <span class="text-bo-fg">{{ client.name || "Без имени" }}</span>
                            <span class="text-xs text-bo-muted">{{ client.phone || "—" }}</span>
                        </button>
                    </div>
                </div>
            </UiFormField>

            <p
                v-if="searching && phoneComplete"
                class="text-xs text-bo-muted"
            >
                Ищем клиента…
            </p>

            <template v-if="showNewClient">
                <p class="text-xs text-bo-muted">
                    Клиент не найден — оформим заказ на нового.
                </p>
                <UiFormField
                    label="Имя"
                    required
                    :error="nameError"
                >
                    <UiInput v-model="nameModel" />
                </UiFormField>
            </template>
        </template>
    </div>
</template>
