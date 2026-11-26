<script setup>
import { onMounted } from "vue";
import { useOwnerBranchContext } from "../../composables/useOwnerBranchContext.js";

const { branches, selectedBranchId, loading, error, ensureLoaded, select, branchLabel } =
    useOwnerBranchContext();

onMounted(ensureLoaded);
</script>

<template>
    <div class="min-w-[16rem]">
        <label class="block text-sm text-bo-muted" for="owner-branch-context">
            Филиал
            <select
                id="owner-branch-context"
                :value="selectedBranchId"
                :disabled="loading"
                class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm text-bo-fg transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none disabled:cursor-not-allowed disabled:opacity-40"
                @change="select($event.target.value)"
            >
                <option value="">Выбери филиал</option>
                <option v-for="branch in branches" :key="branch.id" :value="String(branch.id)">
                    {{ branchLabel(branch) }}
                </option>
            </select>
        </label>
        <p v-if="error" class="mt-1 text-xs text-red-300">
            {{ error }}
        </p>
    </div>
</template>
