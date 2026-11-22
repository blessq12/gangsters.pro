<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { getOrder } from "../../../api/orders.js";
import OrderFacts from "../../../shell/components/order/OrderFacts.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useBranchChoice } from "../../../shell/composables/useBranchChoice.js";
import { useToast } from "../../../shell/composables/useToast.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const detailApi = useApi();
const { load: loadBranches, labelOf } = useBranchChoice();

const order = ref(null);
const orderId = computed(() => route.params.id);
const branchTitle = computed(() => labelOf(order.value?.branch_id));

async function load() {
    if (!orderId.value) {
        return;
    }
    try {
        order.value = await detailApi.run(() => getOrder(orderId.value));
    } catch (err) {
        order.value = null;
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказ");
    }
}

watch(orderId, load);
onMounted(async () => {
    await loadBranches();
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:py-10">
        <UiButton class="!bg-white/10 !text-bo-fg" @click="router.push({ name: 'owner-orders' })">
            К списку заказов
        </UiButton>
        <UiCard title="Карточка заказа">
            <p v-if="detailApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </p>
            <OrderFacts
                v-else-if="order"
                :order="order"
                :branch-title="branchTitle"
            />
            <p v-else class="text-sm text-bo-muted">
                Заказ не найден.
            </p>
        </UiCard>
    </div>
</template>
