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
    <div class="overflow-x-auto rounded border border-slate-200 bg-white">
        <div v-if="loading" class="p-6 text-center text-sm text-slate-500">
            Загрузка…
        </div>
        <table v-else class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        class="px-3 py-2 font-medium"
                        :style="col.width ? { width: col.width } : undefined"
                    >
                        {{ col.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="rows.length === 0">
                    <td
                        :colspan="columns.length"
                        class="px-3 py-8 text-center text-slate-500"
                    >
                        {{ emptyText }}
                    </td>
                </tr>
                <tr
                    v-for="(row, index) in rows"
                    :key="row[rowKey] ?? index"
                    class="cursor-pointer border-b border-slate-100 hover:bg-slate-50"
                    @click="$emit('row-click', row)"
                >
                    <td
                        v-for="col in columns"
                        :key="col.key"
                        class="px-3 py-2 align-middle text-slate-900"
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
