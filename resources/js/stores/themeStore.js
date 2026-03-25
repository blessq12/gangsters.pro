import { defineStore } from "pinia";

const THEME_KEY = "theme";
const THEME_COLORS = {
    dark: "#1f1f23",
    light: "#f9fafb",
};

function updateSafariThemeColor(theme) {
    if (typeof document === "undefined") return;

    const safeTheme = THEME_COLORS[theme] ? theme : "dark";
    const color = THEME_COLORS[safeTheme];

    let meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) {
        meta = document.createElement("meta");
        meta.setAttribute("name", "theme-color");
        document.head.appendChild(meta);
    }

    meta.setAttribute("content", color);
}

export const useThemeStore = defineStore("theme", {
    state: () => ({
        theme: "dark",
    }),
    actions: {
        initTheme() {
            if (typeof window === "undefined") return;

            const saved = window.localStorage.getItem(THEME_KEY);

            if (saved === "light" || saved === "dark") {
                this.theme = saved;
            } else {
                this.theme = "dark";
            }

            updateSafariThemeColor(this.theme);
        },
        setTheme(theme) {
            if (theme !== "light" && theme !== "dark") return;

            this.theme = theme;

            if (typeof window !== "undefined") {
                window.localStorage.setItem(THEME_KEY, theme);
            }

            updateSafariThemeColor(theme);
        },
        toggleTheme() {
            this.setTheme(this.theme === "dark" ? "light" : "dark");
        },
    },
});

