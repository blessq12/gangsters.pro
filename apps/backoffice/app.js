import { createPinia } from "pinia";
import { createApp } from "vue";
import "./css/style.css";
import { registerPwa } from "./pwa/register";
import { setUnauthorizedHandler } from "./http/transport";
import router from "./router";
import App from "./App.vue";
import { useAuthStore } from "./shell/store/authStore";
import { useSessionStore } from "./shell/store/sessionStore";

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

setUnauthorizedHandler(() => {
    const auth = useAuthStore(pinia);
    const session = useSessionStore(pinia);
    auth.setToken(null);
    auth.employee = null;
    session.clear();
    if (router.currentRoute.value.name !== "backoffice-login") {
        router.push({ name: "backoffice-login" });
    }
});

app.mount("#app");
registerPwa();
