<script setup>
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../store/authStore";
import { useSessionStore } from "../store/sessionStore";
import UiLoader from "../components/ui/UiLoader.vue";

const auth = useAuthStore();
const session = useSessionStore();
const router = useRouter();

onMounted(async () => {
    await auth.bootstrap();
    if (!auth.authRequired) {
        return;
    }
    if (!auth.isAuthenticated || !auth.role) {
        await router.replace({ name: "backoffice-login" });
        return;
    }
    const module = await session.activateRole(auth.role, router);
    await router.replace({ name: module.home });
});
</script>

<template>
    <UiLoader />
</template>
