import { defineStore } from "pinia";
import { siteMeta } from "../../../config/site";

const THEME_KEY = "theme";

function resolveCanvasThemeColor() {
    if (typeof document === "undefined") {
        return siteMeta.themeColor;
    }

    const shell = document.querySelector(".app-shell");
    const source = shell ?? document.documentElement;
    const raw = getComputedStyle(source).getPropertyValue("--app-canvas").trim();
    if (raw) {
        return raw;
    }

    return siteMeta.themeColor;
}

function updateSafariThemeColor() {
    if (typeof document === "undefined") return;

    let meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) {
        meta = document.createElement("meta");
        meta.setAttribute("name", "theme-color");
        document.head.appendChild(meta);
    }

    meta.setAttribute("content", resolveCanvasThemeColor());
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

            requestAnimationFrame(() => {
                updateSafariThemeColor();
            });
        },
        syncThemeColorFromCanvas() {
            updateSafariThemeColor();
        },
    },
});
