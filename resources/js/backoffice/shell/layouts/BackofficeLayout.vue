<script setup>
import BackofficeTopNav from "../components/BackofficeTopNav.vue";
import BackofficeFooter from "../components/BackofficeFooter.vue";
</script>

<template>
    <div class="flex min-h-screen flex-col bg-neutral-100 text-neutral-900">
        <BackofficeTopNav />
        <div class="flex min-h-0 flex-1">
            <RouterView />
        </div>
        <BackofficeFooter />
    </div>
</template>
