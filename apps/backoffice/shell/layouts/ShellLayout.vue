<script setup>
import ShellTopbar from "../components/ShellTopbar.vue";
import ShellFooter from "../components/ShellFooter.vue";
import ToastHost from "../components/ToastHost.vue";
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-100 text-slate-900">
        <ShellTopbar />
        <div class="flex min-h-0 flex-1 flex-col">
            <RouterView />
        </div>
        <ShellFooter />
        <ToastHost />
    </div>
</template>
