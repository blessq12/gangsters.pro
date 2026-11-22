<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    acceptOrder,
    cancelOrder,
    completePickup,
    getOrder,
    reassignBranch,
} from "../../../api/orders.js";
import OrderFacts from "../../../shell/components/order/OrderFacts.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useBranchChoice } from "../../../shell/composables/useBranchChoice.js";
import { useToast } from "../../../shell/composables/useToast.js";
import {
    administratorCommands,
    orderNumberOf,
    orderStatusLabel,
    orderStatusVariant,
} from "../../../shell/domain/orderStage.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const detailApi = useApi();
const actionApi = useApi();
const {
    branches,
    load: loadBranches,
    labelOf,
    branchLabel,
} = useBranchChoice();

const order = ref(null);
const confirmOpen = ref(false);
const reassignOpen = ref(false);
const cancelOpen = ref(false);
const pickupOpen = ref(false);
const selectedBranchId = ref("");

const orderId = computed(() => route.params.id);
const commands = computed(() => administratorCommands(order.value));
const branchTitle = computed(() => labelOf(order.value?.branch_id));
const branchCity = computed(() => {
    const branch = branches.value.find((item) => String(item.id) === String(order.value?.branch_id ?? ""));
    return String(branch?.city ?? "").trim();
});
const otherBranches = computed(() =>
    branches.value.filter((branch) => String(branch.id) !== String(order.value?.branch_id ?? "")),
);

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

function goList() {
    router.push({ name: "administrator-live-orders" });
}

function goRoutes() {
    router.push({ name: "administrator-logistics" });
}

function onCommand(id) {
    if (id === "confirm") {
        confirmOpen.value = true;
        return;
    }
    if (id === "reassign") {
        selectedBranchId.value = "";
        reassignOpen.value = true;
        return;
    }
    if (id === "pickup") {
        pickupOpen.value = true;
        return;
    }
    if (id === "toRoute") {
        goRoutes();
        return;
    }
    if (id === "cancel") {
        cancelOpen.value = true;
    }
}

async function submitConfirm() {
    confirmOpen.value = false;
    try {
        order.value = await actionApi.run(() => acceptOrder(order.value.id));
        toast.success("Заказ подтверждён. Тикет и платёж созданы.");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось подтвердить");
    }
}

async function submitReassign() {
    const branchId = Number(selectedBranchId.value);
    if (!branchId) {
        toast.error("Выбери другой филиал");
        return;
    }
    reassignOpen.value = false;
    try {
        order.value = await actionApi.run(() => reassignBranch(order.value.id, { branch_id: branchId }));
        toast.success("Филиал переназначен. Старый тикет сменится новым.");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось переназначить филиал");
    }
}

async function submitCancel() {
    cancelOpen.value = false;
    try {
        order.value = await actionApi.run(() => cancelOrder(order.value.id));
        toast.success("Заказ отменён. Платёж закрыт.");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось отменить");
    }
}

async function submitPickup() {
    pickupOpen.value = false;
    try {
        order.value = await actionApi.run(() => completePickup(order.value.id));
        toast.success("Заказ выдан. Платёж оплачен.");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось выдать");
    }
}

watch(orderId, load);
onMounted(async () => {
    await loadBranches();
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton class="!bg-white/10 !text-bo-fg" @click="goList">
                    К списку заказов
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ order ? orderNumberOf(order) : `Заказ #${orderId}` }}
                </h1>
                <UiBadge
                    v-if="order"
                    :variant="orderStatusVariant(order.status)"
                    :text="orderStatusLabel(order.status)"
                />
            </div>
        </div>

        <p v-if="detailApi.loading" class="text-sm text-bo-muted">
            Загрузка…
        </p>
        <p v-else-if="!order" class="text-sm text-bo-muted">
            Заказ не найден.
        </p>
        <template v-else>
            <UiCard v-if="commands.length" title="Действия">
                <div class="flex flex-wrap gap-3">
                    <UiButton
                        v-for="command in commands"
                        :key="command.id"
                        :disabled="actionApi.loading"
                        @click="onCommand(command.id)"
                    >
                        {{ command.label }}
                    </UiButton>
                </div>
            </UiCard>

            <OrderFacts
                :order="order"
                :branch-title="branchTitle"
                :branch-city="branchCity"
            />
        </template>

        <UiConfirmDialog
            :open="confirmOpen"
            title="Подтвердить заказ?"
            message="Филиал уже на заказе. Появятся тикет в очереди и платёж в ожидании."
            @confirm="submitConfirm"
            @cancel="confirmOpen = false"
        />

        <UiModal :open="reassignOpen" title="Переназначить филиал" @close="reassignOpen = false">
            <p class="mb-4 text-sm text-bo-muted">
                Пока тикет не взят в работу, старый закроется и встанет новый на другой филиал.
            </p>
            <UiFormField label="Новый филиал" required>
                <select
                    v-model="selectedBranchId"
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                >
                    <option value="">Выбери филиал</option>
                    <option
                        v-for="branch in otherBranches"
                        :key="branch.id"
                        :value="String(branch.id)"
                    >
                        {{ branchLabel(branch) }}
                    </option>
                </select>
            </UiFormField>
            <template #footer>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="reassignOpen = false">
                    Закрыть
                </UiButton>
                <UiButton :disabled="actionApi.loading" @click="submitReassign">
                    Переназначить
                </UiButton>
            </template>
        </UiModal>

        <UiConfirmDialog
            :open="pickupOpen"
            title="Выдать самовывоз?"
            message="Заказ станет выданным, платёж — оплаченным. В маршрутный лист он не попадёт."
            @confirm="submitPickup"
            @cancel="pickupOpen = false"
        />

        <UiConfirmDialog
            :open="cancelOpen"
            title="Отменить заказ?"
            message="Отмена закроет тикет, остановку маршрутного листа и платёж. Статус в обход этого действия не переключается."
            @confirm="submitCancel"
            @cancel="cancelOpen = false"
        />
    </div>
</template>
