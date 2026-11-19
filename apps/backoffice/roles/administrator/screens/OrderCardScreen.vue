<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    acceptOrder,
    cancelOrder,
    completePickup,
    getOrder,
    reassignKitchen,
} from "../../../api/orders.js";
import OrderFacts from "../../../shell/components/order/OrderFacts.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useKitchenChoice } from "../../../shell/composables/useKitchenChoice.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { administratorCommands } from "../../../shell/domain/orderStage.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const detailApi = useApi();
const actionApi = useApi();
const {
    kitchens,
    error: kitchensError,
    load: loadKitchens,
    labelOf,
    kitchenLabel,
} = useKitchenChoice();

const order = ref(null);
const confirmOpen = ref(false);
const reassignOpen = ref(false);
const cancelOpen = ref(false);
const pickupOpen = ref(false);
const selectedKitchenId = ref("");

const orderId = computed(() => route.params.id);
const commands = computed(() => administratorCommands(order.value));
const kitchenTitle = computed(() => labelOf(order.value?.kitchen_id));
const otherKitchens = computed(() =>
    kitchens.value.filter((kitchen) => String(kitchen.id) !== String(order.value?.kitchen_id ?? "")),
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
        selectedKitchenId.value = kitchens.value.length === 1 ? String(kitchens.value[0].id) : "";
        confirmOpen.value = true;
        return;
    }
    if (id === "reassign") {
        selectedKitchenId.value = "";
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
    const kitchenId = Number(selectedKitchenId.value);
    if (!kitchenId) {
        toast.error("Выбери кухню");
        return;
    }
    confirmOpen.value = false;
    try {
        order.value = await actionApi.run(() => acceptOrder(order.value.id, { kitchen_id: kitchenId }));
        toast.success("Заказ подтверждён. Тикет и платёж созданы.");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось подтвердить");
    }
}

async function submitReassign() {
    const kitchenId = Number(selectedKitchenId.value);
    if (!kitchenId) {
        toast.error("Выбери другую кухню");
        return;
    }
    reassignOpen.value = false;
    try {
        order.value = await actionApi.run(() => reassignKitchen(order.value.id, { kitchen_id: kitchenId }));
        toast.success("Кухня переназначена. Старый тикет сменится новым.");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось переназначить кухню");
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
    await loadKitchens();
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-3xl space-y-6 px-4 py-6 sm:px-6 lg:py-10">
        <UiButton class="!bg-white/10 !text-bo-fg" @click="goList">
            К списку заказов
        </UiButton>

        <UiCard title="Карточка заказа">
            <p v-if="detailApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </p>
            <OrderFacts
                v-else-if="order"
                :order="order"
                :kitchen-title="kitchenTitle"
            />
            <p v-else class="text-sm text-bo-muted">
                Заказ не найден.
            </p>

            <div v-if="order && commands.length" class="mt-6 flex flex-wrap gap-3">
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

        <UiModal :open="confirmOpen" title="Подтвердить заказ" @close="confirmOpen = false">
            <p class="mb-4 text-sm text-bo-muted">
                Назначим кухню. Появятся тикет в очереди и платёж в ожидании.
            </p>
            <UiFormField label="Кухня" required>
                <select
                    v-model="selectedKitchenId"
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                >
                    <option value="">Выбери кухню</option>
                    <option
                        v-for="kitchen in kitchens"
                        :key="kitchen.id"
                        :value="String(kitchen.id)"
                    >
                        {{ kitchenLabel(kitchen) }}
                    </option>
                </select>
            </UiFormField>
            <p v-if="kitchensError" class="mt-2 text-sm text-red-300">
                {{ kitchensError }}
            </p>
            <template #footer>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="confirmOpen = false">
                    Закрыть
                </UiButton>
                <UiButton :disabled="actionApi.loading" @click="submitConfirm">
                    Подтвердить
                </UiButton>
            </template>
        </UiModal>

        <UiModal :open="reassignOpen" title="Переназначить кухню" @close="reassignOpen = false">
            <p class="mb-4 text-sm text-bo-muted">
                Пока тикет не взят в работу, старый закроется и встанет новый на другую кухню.
            </p>
            <UiFormField label="Новая кухня" required>
                <select
                    v-model="selectedKitchenId"
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                >
                    <option value="">Выбери кухню</option>
                    <option
                        v-for="kitchen in otherKitchens"
                        :key="kitchen.id"
                        :value="String(kitchen.id)"
                    >
                        {{ kitchenLabel(kitchen) }}
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
