import { defineStore } from "pinia";

const THEME_KEY = "theme";

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
        },
        setTheme(theme) {
            if (theme !== "light" && theme !== "dark") return;

            this.theme = theme;

            if (typeof window !== "undefined") {
                window.localStorage.setItem(THEME_KEY, theme);
            }
        },
        toggleTheme() {
            this.setTheme(this.theme === "dark" ? "light" : "dark");
        },
    },
});

