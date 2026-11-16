import { createPinia } from "pinia";
import { createApp } from "vue";
import "./css/style.css";
import { registerPwa } from "./pwa/register";
import router from "./router";
import App from "./App.vue";

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.mount("#app");
registerPwa();
