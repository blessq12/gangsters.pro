<script setup>
import CourierSideNav from "../components/CourierSideNav.vue";
</script>

<template>
    <div class="flex min-h-0 flex-1">
        <CourierSideNav />
        <main class="flex-1 overflow-auto p-4">
            <RouterView />
        </main>
    </div>
</template>
