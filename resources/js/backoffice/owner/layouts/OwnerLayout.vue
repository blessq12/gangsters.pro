<script setup>
import OwnerSideNav from "../components/OwnerSideNav.vue";
</script>

<template>
    <div class="flex min-h-0 flex-1">
        <OwnerSideNav />
        <main class="flex-1 overflow-auto p-4">
            <RouterView />
        </main>
    </div>
</template>
