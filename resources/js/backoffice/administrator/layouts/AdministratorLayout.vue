<script setup>
import AdministratorSideNav from "../components/AdministratorSideNav.vue";
</script>

<template>
    <div class="flex min-h-0 flex-1">
        <AdministratorSideNav />
        <main class="flex-1 overflow-auto p-4">
            <RouterView />
        </main>
    </div>
</template>
