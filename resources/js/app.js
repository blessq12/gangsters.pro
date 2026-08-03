import "@mdi/font/css/materialdesignicons.min.css";
import { vMaska } from "maska";
import { createPinia } from "pinia";
import { createApp, defineAsyncComponent } from "vue";
import VueLazyload from "vue-lazyload";
import "../css/vue-toastification.css";
import "./bootstrap";
import { applyPageHead } from "./modules/shell/application/usePageHead";
import router from "./router";
import App from "./App.vue";

const app = createApp(App);

app.use(VueLazyload, {
    lazyComponent: true,
    preLoad: 1.5,
    attempt: 1,
    error: "http://via.placeholder.com/300x200?text=error",
    loading: "/images/placeholder/loading.gif",
    observer: true,
    observerOptions: {
        rootMargin: "0px",
        threshold: 0.1,
    },
});

app.use(createPinia());
app.use(router);
app.directive("maska", vMaska);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 */

const vueModules = {
    ...import.meta.glob("./components/**/*.vue"),
    ...import.meta.glob("./layouts/SecondaryPageLayout.vue"),
};

Object.entries(vueModules).forEach(([path, loader]) => {
    const name = path
        .split("/")
        .pop()
        .replace(/\.\w+$/, "");
    app.component(name, defineAsyncComponent(loader));
});

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

router.isReady().then(() => {
    applyPageHead(router.currentRoute.value);
});

app.mount("#app");

document.querySelectorAll("input, textarea").forEach(function (input) {
    input.addEventListener("focus", function () {
        document.body.style.zoom = "1"; // Prevent zoom
    });
    input.addEventListener("blur", function () {
        document.body.style.zoom = ""; // Reset zoom
    });
});
