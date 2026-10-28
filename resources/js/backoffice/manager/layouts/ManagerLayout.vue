<script setup>
import ManagerSideNav from "../components/ManagerSideNav.vue";
</script>

<template>
    <div class="flex min-h-0 flex-1">
        <ManagerSideNav />
        <main class="flex-1 overflow-auto p-4">
            <RouterView />
        </main>
    </div>
</template>
