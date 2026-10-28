<script setup>
import ProductionSideNav from "../components/ProductionSideNav.vue";
</script>

<template>
    <div class="flex min-h-0 flex-1">
        <ProductionSideNav />
        <main class="flex-1 overflow-auto p-4">
            <RouterView />
        </main>
    </div>
</template>
