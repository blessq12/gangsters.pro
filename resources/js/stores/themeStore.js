import { defineStore } from "pinia";

const THEME_KEY = "theme";
const THEME_COLOR = "#191919";

function updateSafariThemeColor() {
    if (typeof document === "undefined") return;

    let meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) {
        meta = document.createElement("meta");
        meta.setAttribute("name", "theme-color");
        document.head.appendChild(meta);
    }

    meta.setAttribute("content", THEME_COLOR);
}

export const useThemeStore = defineStore("theme", {
    state: () => ({
        theme: "dark",
    }),
    actions: {
        initTheme() {
            if (typeof window === "undefined") return;

            this.theme = "dark";
            window.localStorage.setItem(THEME_KEY, "dark");
            updateSafariThemeColor();
        },
    },
});
