<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { listEmployees } from "../../../api/staff.js";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import ShellIcon from "../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const router = useRouter();
const toast = useToast();
const listApi = useApi();
const { branches, ensureLoaded, branchLabel } = useOwnerBranchContext();
const { page, perPage, total, items: rows, offset, resetPage, applyPayload } = usePagedList();

const filterBranchId = ref("");

const columns = [
    { key: "name", label: "Имя" },
    { key: "email", label: "Email" },
    { key: "role", label: "Роль", width: "130px" },
    { key: "branch", label: "Филиал", width: "220px" },
    { key: "is_active", label: "Активен", width: "100px" },
    { key: "actions", label: "", width: "72px" },
];

const branchById = computed(() => {
    /** @type {Map<number, any>} */
    const map = new Map();
    branches.value.forEach((branch) => {
        map.set(Number(branch.id), branch);
    });
    return map;
});

const tableRows = computed(() =>
    rows.value.map((row) => ({
        ...row,
        branch: formatBranch(row.branch_id),
    })),
);

function formatBranch(branchId) {
    if (branchId == null || branchId === "") {
        return "Вся компания";
    }
    const branch = branchById.value.get(Number(branchId));
    return branch ? branchLabel(branch) : `#${branchId}`;
}

function roleVariant(role) {
    if (role === "owner") {
        return "danger";
    }
    if (role === "administrator") {
        return "warning";
    }
    if (role === "kitchen") {
        return "success";
    }
    return "neutral";
}

async function loadEmployees() {
    try {
        const payload = await listApi.run(() =>
            listEmployees({
                offset: offset.value,
                limit: perPage,
                branch_id: filterBranchId.value || undefined,
            }),
        );
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить сотрудников");
    }
}

function openCreate() {
    router.push({ name: "owner-staff-employee", params: { id: "new" } });
}

function openEmployee(row) {
    router.push({ name: "owner-staff-employee", params: { id: row.id } });
}

watch(page, loadEmployees);
watch(filterBranchId, () => {
    resetPage();
    loadEmployees();
});

onMounted(async () => {
    await ensureLoaded();
    await loadEmployees();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Сотрудники">
            <template #actions>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="min-w-[14rem]">
                        <select
                            v-model="filterBranchId"
                            class="h-10 w-full border border-bo-border bg-bo-field px-3 text-sm focus:border-bo-accent focus:outline-none"
                        >
                            <option value="">Все филиалы</option>
                            <option
                                v-for="branch in branches"
                                :key="branch.id"
                                :value="String(branch.id)"
                            >
                                {{ branchLabel(branch) }}
                            </option>
                        </select>
                    </div>
                    <UiButton @click="openCreate">+ Сотрудник</UiButton>
                </div>
            </template>

            <UiTable
                :columns="columns"
                :rows="tableRows"
                :loading="listApi.loading"
                empty-text="Нет сотрудников"
            >
                <template #cell-role="{ value }">
                    <UiBadge :variant="roleVariant(value)" :text="String(value)" />
                </template>
                <template #cell-is_active="{ value }">
                    <UiBadge
                        :variant="value ? 'success' : 'neutral'"
                        :text="value ? 'да' : 'нет'"
                    />
                </template>
                <template #cell-actions="{ row }">
                    <UiButton
                        class="!h-9 !px-2.5"
                        title="Редактировать"
                        aria-label="Редактировать"
                        @click.stop="openEmployee(row)"
                    >
                        <ShellIcon name="edit" class="h-4 w-4" />
                    </UiButton>
                </template>
            </UiTable>
            <div class="mt-6">
                <UiPagination
                    :page="page"
                    :per-page="perPage"
                    :total="total"
                    @update:page="page = $event"
                />
            </div>
        </UiCard>
    </div>
</template>
