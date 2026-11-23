<script setup>
defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    emptyText: { type: String, default: "Нет данных" },
    rowKey: { type: String, default: "id" },
});

defineEmits(["row-click"]);
</script>

<template>
    <div class="w-full max-w-full overflow-x-auto border border-bo-border">
        <div v-if="loading" class="px-6 py-12 text-center text-sm text-bo-muted">
            Загрузка…
        </div>
        <table v-else class="min-w-full table-fixed text-sm">
            <thead class="border-b border-bo-border bg-white/[0.02]">
                <tr>
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        class="px-3 py-3 text-[11px] font-medium uppercase tracking-[0.12em] text-bo-muted sm:px-4"
                        :class="[
                            col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                            col.truncate ? 'truncate' : 'whitespace-nowrap',
                        ]"
                        :style="col.width ? { width: col.width } : undefined"
                    >
                        {{ col.label }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bo-border">
                <tr v-if="rows.length === 0">
                    <td
                        :colspan="columns.length"
                        class="px-4 py-12 text-center text-bo-muted"
                    >
                        {{ emptyText }}
                    </td>
                </tr>
                <tr
                    v-for="(row, index) in rows"
                    :key="row[rowKey] ?? index"
                    class="cursor-pointer transition-colors duration-150 hover:bg-white/[0.03]"
                    @click="$emit('row-click', row)"
                >
                    <td
                        v-for="col in columns"
                        :key="col.key"
                        class="px-3 py-3.5 align-middle text-bo-fg sm:px-4"
                        :class="[
                            col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                            col.truncate ? 'max-w-0 truncate' : 'whitespace-nowrap',
                        ]"
                        :title="col.truncate ? String(row[col.key] ?? '') : undefined"
                    >
                        <slot
                            :name="`cell-${col.key}`"
                            :row="row"
                            :value="row[col.key]"
                        >
                            {{ row[col.key] }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
