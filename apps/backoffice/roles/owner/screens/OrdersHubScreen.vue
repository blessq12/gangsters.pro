<script setup>
import { computed, ref, unref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import OrderFormationRulesScreen from "./OrderFormationRulesScreen.vue";
import OrdersScreen from "./OrdersScreen.vue";

const route = useRoute();
const router = useRouter();

/** @type {import('vue').Ref<{ savePolicy: () => Promise<void>, canSave: import('vue').ComputedRef<boolean>|boolean }|null>} */
const rulesRef = ref(null);

const sections = [
    { id: "list", label: "Список" },
    { id: "rules", label: "Правила оформления" },
];

const sectionIds = sections.map((item) => item.id);

const section = computed(() =>
    sectionIds.includes(String(route.query.section))
        ? String(route.query.section)
        : "list",
);

const showRulesSave = computed(() => section.value === "rules");

const rulesCanSave = computed(() => Boolean(unref(rulesRef.value?.canSave)));

function setSection(id) {
    if (section.value === id) {
        return;
    }
    if (id === "list") {
        router.replace({ name: "owner-orders", query: {} });
        return;
    }
    router.replace({ name: "owner-orders", query: { section: id } });
}

function saveRules() {
    rulesRef.value?.savePolicy();
}

watch(
    () => route.query.section,
    (value) => {
        if (value && !sectionIds.includes(String(value))) {
            router.replace({ name: "owner-orders", query: {} });
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <div class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">
                    Заказы
                </h1>
                <UiButton
                    v-if="showRulesSave"
                    :disabled="!rulesCanSave"
                    @click="saveRules"
                >
                    Сохранить
                </UiButton>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="item in sections"
                    :key="item.id"
                    type="button"
                    class="h-11 px-4 text-sm font-medium transition-colors duration-150"
                    :class="section === item.id
                        ? 'bg-bo-accent text-white'
                        : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                    @click="setSection(item.id)"
                >
                    {{ item.label }}
                </button>
            </div>
        </div>

        <OrdersScreen v-if="section === 'list'" embedded />
        <OrderFormationRulesScreen
            v-else
            ref="rulesRef"
            embedded
        />
    </div>
</template>
