import { acceptHMRUpdate, defineStore } from "pinia";

export const appStore = defineStore("app", {
    state: () => ({
        additional: false,
        modal: false,
        modalName: null,
        currentAdditional: null,
        device: null,
        links: null,
        company: null,
    }),
    actions: {
        defineDevice() {
            const userAgent = navigator.userAgent.toLowerCase();
            if (userAgent.includes("mobi")) {
                this.device = "phone";
            } else if (userAgent.includes("tablet")) {
                this.device = "tablet";
            } else {
                this.device = "desktop";
            }
            return this.device;
        },
    },
    getters: {},
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(appStore, import.meta.hot));
}
