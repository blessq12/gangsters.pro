<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { listProducts, listSets } from "../../../api/catalog.js";
import {
    cloneBranchSettings,
    deleteBranchMenuItem,
    getBranchMenu,
    saveBranchMenuItem,
} from "../../../api/company.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import UiToggle from "../../../shell/components/ui/UiToggle.vue";
import OwnerBranchSelector from "../components/branch/OwnerBranchSelector.vue";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";

defineProps({
    /** Без внешней рамки страницы — для хаба «Меню». */
    embedded: { type: Boolean, default: false },
});

const PAGE_LIMIT = 100;

const toast = useToast();
const loadApi = useApi();
const cloneApi = useApi();
const {
    branches,
    selectedBranchId,
    ensureLoaded,
    branchLabel,
} = useOwnerBranchContext();

/**
 * @type {import('vue').Ref<Array<{
 *   id: number,
 *   kind: string,
 *   name: string,
 *   catalogRubles: number,
 *   inMenu: boolean,
 *   isAvailable: boolean,
 *   priceRubles: string,
 *   saved: { isAvailable: boolean, priceRubles: string },
 *   saving: boolean,
 *   error: string,
 * }>>}
 */
const rows = ref([]);
const removeTarget = ref(null);
const cloneOpen = ref(false);
const cloneForm = ref({
    sourceBranchId: "",
    copyMenu: true,
    copyPromotions: true,
    copyDelivery: false,
    mode: "overwrite",
});

const columns = [
    { key: "name", label: "Позиция" },
    { key: "kind", label: "Тип", width: "110px" },
    { key: "catalogRubles", label: "Цена в каталоге", width: "140px" },
    { key: "isAvailable", label: "В продаже", width: "170px" },
    { key: "priceRubles", label: "Цена в филиале (₽)", width: "190px" },
    { key: "actions", label: "", width: "260px" },
];

const kindLabels = {
    product: "Товар",
    set: "Набор",
};

const confirmMessage = computed(() =>
    removeTarget.value
        ? `Убрать «${removeTarget.value.name}» из меню филиала?`
        : "",
);

const sourceBranches = computed(() =>
    branches.value.filter((branch) => String(branch.id) !== String(selectedBranchId.value)),
);

/** Копейки из ответа API → строка в рублях для поля ввода. */
function kopecksToRubles(kopecks) {
    return String(Number(kopecks ?? 0) / 100);
}

/** Рубли из поля ввода → копейки; null, если значение некорректно. */
function rublesToKopecks(value) {
    const text = String(value ?? "").trim().replace(",", ".");
    if (text === "" || Number.isNaN(Number(text)) || Number(text) < 0) {
        return null;
    }
    return Math.round(Number(text) * 100);
}

async function fetchAllCatalogItems(fetchPage) {
    const result = [];
    let total = 0;
    do {
        const payload = await fetchPage({ offset: result.length, limit: PAGE_LIMIT });
        const items = Array.isArray(payload?.items) ? payload.items : [];
        total = Number(payload?.total ?? 0);
        result.push(...items);
        if (items.length === 0) {
            break;
        }
    } while (result.length < total);
    return result;
}

function buildRow(item, kind, menuByItemId) {
    const entry = menuByItemId.get(Number(item.id));
    const catalogRubles = Number(item.price?.amount ?? 0);
    const priceRubles = entry
        ? kopecksToRubles(entry.price_kopecks)
        : String(catalogRubles);
    const isAvailable = entry ? Boolean(entry.is_available) : false;

    return {
        id: Number(item.id),
        kind,
        name: item.name ?? `#${item.id}`,
        catalogRubles,
        inMenu: Boolean(entry),
        isAvailable,
        priceRubles,
        saved: { isAvailable, priceRubles },
        saving: false,
        error: "",
    };
}

async function loadMenu() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        rows.value = [];
        return;
    }
    try {
        const [products, sets, menu] = await loadApi.run(() => Promise.all([
            fetchAllCatalogItems(listProducts),
            fetchAllCatalogItems(listSets),
            getBranchMenu(branchId),
        ]));
        if (branchId !== selectedBranchId.value) {
            return;
        }
        const menuByItemId = new Map(
            (Array.isArray(menu?.items) ? menu.items : [])
                .map((entry) => [Number(entry.catalog_item_id), entry]),
        );
        rows.value = [
            ...products.map((item) => buildRow(item, "product", menuByItemId)),
            ...sets.map((item) => buildRow(item, "set", menuByItemId)),
        ];
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить меню");
    }
}

function isDirty(row) {
    return row.isAvailable !== row.saved.isAvailable
        || row.priceRubles !== row.saved.priceRubles;
}

async function saveRow(row) {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        toast.error("Выбери филиал");
        return;
    }
    const priceKopecks = rublesToKopecks(row.priceRubles);
    if (priceKopecks === null) {
        row.error = "Укажите цену ≥ 0";
        return;
    }
    row.error = "";
    row.saving = true;
    try {
        const saved = await saveBranchMenuItem(branchId, row.id, {
            is_available: row.isAvailable,
            price_kopecks: priceKopecks,
        });
        row.inMenu = true;
        row.isAvailable = Boolean(saved?.is_available ?? row.isAvailable);
        row.priceRubles = kopecksToRubles(saved?.price_kopecks ?? priceKopecks);
        row.saved = { isAvailable: row.isAvailable, priceRubles: row.priceRubles };
        toast.success("Позиция сохранена");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    } finally {
        row.saving = false;
    }
}

async function confirmRemove() {
    const row = removeTarget.value;
    const branchId = selectedBranchId.value;
    removeTarget.value = null;
    if (!row || !branchId) {
        return;
    }
    row.saving = true;
    try {
        await deleteBranchMenuItem(branchId, row.id);
        row.inMenu = false;
        row.isAvailable = false;
        row.priceRubles = String(row.catalogRubles);
        row.saved = { isAvailable: row.isAvailable, priceRubles: row.priceRubles };
        row.error = "";
        toast.success("Позиция убрана из меню");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка удаления");
    } finally {
        row.saving = false;
    }
}

function openCloneDialog() {
    if (!selectedBranchId.value) {
        toast.error("Сначала выбери целевой филиал");
        return;
    }
    const firstSource = sourceBranches.value[0];
    cloneForm.value = {
        sourceBranchId: firstSource ? String(firstSource.id) : "",
        copyMenu: true,
        copyPromotions: true,
        copyDelivery: false,
        mode: "overwrite",
    };
    cloneOpen.value = true;
}

async function submitClone() {
    const targetId = Number(selectedBranchId.value);
    const sourceId = Number(cloneForm.value.sourceBranchId);
    if (!targetId) {
        toast.error("Выбери целевой филиал");
        return;
    }
    if (!sourceId) {
        toast.error("Выбери филиал-источник");
        return;
    }
    if (
        !cloneForm.value.copyMenu
        && !cloneForm.value.copyPromotions
        && !cloneForm.value.copyDelivery
    ) {
        toast.error("Выбери хотя бы один блок для копирования");
        return;
    }

    try {
        const result = await cloneApi.run(() => cloneBranchSettings({
            source_branch_id: sourceId,
            target_branch_ids: [targetId],
            copy_menu: cloneForm.value.copyMenu,
            copy_promotions: cloneForm.value.copyPromotions,
            copy_delivery: cloneForm.value.copyDelivery,
            mode: cloneForm.value.mode,
        }));
        cloneOpen.value = false;
        const target = Array.isArray(result?.targets) ? result.targets[0] : null;
        const menuCopied = Number(target?.menu?.copied ?? 0);
        toast.success(
            menuCopied > 0
                ? `Склонировано. Меню: ${menuCopied} поз.`
                : "Клонирование выполнено",
        );
        await loadMenu();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось клонировать");
    }
}

watch(selectedBranchId, loadMenu);
onMounted(async () => {
    await ensureLoaded();
    await loadMenu();
});
</script>

<template>
    <div
        :class="embedded
            ? 'space-y-8'
            : 'mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10'"
    >
        <UiCard title="Меню филиала">
            <template #actions>
                <div class="flex flex-wrap items-center gap-2">
                    <OwnerBranchSelector />
                    <UiButton
                        class="!h-9 !px-3"
                        :disabled="!selectedBranchId || sourceBranches.length === 0"
                        @click="openCloneDialog"
                    >
                        Клонировать из другого филиала
                    </UiButton>
                </div>
            </template>

            <p class="mb-5 text-sm text-bo-muted">
                Название, фото и состав позиций правятся в каталоге.
                Здесь настраивается, что продаётся в выбранном филиале и по какой цене.
            </p>

            <p v-if="!selectedBranchId" class="text-sm text-bo-muted">
                Выбери филиал, чтобы настроить меню.
            </p>

            <UiTable
                v-else
                :columns="columns"
                :rows="rows"
                :loading="loadApi.loading"
                empty-text="В каталоге нет позиций"
            >
                <template #cell-name="{ row }">
                    <div class="flex flex-wrap items-center gap-2">
                        <span>{{ row.name }}</span>
                        <UiBadge
                            :variant="row.inMenu ? 'success' : 'neutral'"
                            :text="row.inMenu ? 'в меню' : 'не в меню'"
                        />
                    </div>
                </template>
                <template #cell-kind="{ value }">
                    {{ kindLabels[value] ?? value }}
                </template>
                <template #cell-catalogRubles="{ value }">
                    {{ value }} ₽
                </template>
                <template #cell-isAvailable="{ row }">
                    <UiToggle
                        v-model="row.isAvailable"
                        label-on="В продаже"
                        label-off="Скрыто"
                        :disabled="row.saving"
                    />
                </template>
                <template #cell-priceRubles="{ row }">
                    <UiInput
                        v-model="row.priceRubles"
                        type="number"
                        :disabled="row.saving"
                    />
                    <p v-if="row.error" class="mt-1 text-xs text-red-300">
                        {{ row.error }}
                    </p>
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <UiButton
                            class="!h-9 !px-2.5"
                            :disabled="row.saving || (row.inMenu && !isDirty(row))"
                            @click.stop="saveRow(row)"
                        >
                            Сохранить
                        </UiButton>
                        <UiButton
                            v-if="row.inMenu"
                            class="!h-9 !px-2.5"
                            :disabled="row.saving"
                            @click.stop="removeTarget = row"
                        >
                            Убрать из меню
                        </UiButton>
                    </div>
                </template>
            </UiTable>
        </UiCard>

        <UiConfirmDialog
            :open="removeTarget !== null"
            title="Убрать из меню"
            :message="confirmMessage"
            @confirm="confirmRemove"
            @cancel="removeTarget = null"
        />

        <UiModal
            :open="cloneOpen"
            title="Клонировать настройки филиала"
            @close="cloneOpen = false"
        >
            <p class="mb-4 text-sm text-bo-muted">
                Целевой филиал: текущий выбранный.
                Catalog не копируется — только меню и связанные настройки.
            </p>

            <label class="mb-4 block text-sm">
                <span class="mb-1 block text-bo-muted">Филиал-источник</span>
                <select
                    v-model="cloneForm.sourceBranchId"
                    class="w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                >
                    <option value="">Выбери источник</option>
                    <option
                        v-for="branch in sourceBranches"
                        :key="branch.id"
                        :value="String(branch.id)"
                    >
                        {{ branchLabel(branch) }}
                    </option>
                </select>
            </label>

            <fieldset class="mb-4 space-y-2">
                <legend class="mb-1 text-sm text-bo-muted">Что копировать</legend>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="cloneForm.copyMenu" type="checkbox" class="accent-bo-accent">
                    Меню (доступность и цена)
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="cloneForm.copyPromotions" type="checkbox" class="accent-bo-accent">
                    Политики и роли акций
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="cloneForm.copyDelivery" type="checkbox" class="accent-bo-accent">
                    Доставка и зоны
                </label>
            </fieldset>

            <fieldset class="mb-2 space-y-2">
                <legend class="mb-1 text-sm text-bo-muted">Режим</legend>
                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="cloneForm.mode"
                        type="radio"
                        value="overwrite"
                        class="accent-bo-accent"
                    >
                    Перезаписать существующие
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="cloneForm.mode"
                        type="radio"
                        value="skip_existing"
                        class="accent-bo-accent"
                    >
                    Пропустить существующие
                </label>
            </fieldset>

            <template #footer>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="cloneOpen = false">
                    Закрыть
                </UiButton>
                <UiButton :disabled="cloneApi.loading" @click="submitClone">
                    Клонировать
                </UiButton>
            </template>
        </UiModal>
    </div>
</template>
