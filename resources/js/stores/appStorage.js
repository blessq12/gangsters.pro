import { defineStore, acceptHMRUpdate } from "pinia";

export const appStore = defineStore('app', {
    state: () => ({
        additional: false,
        modal: false,
        modalName: null,
        currentAdditional: null,
    }),
    actions: {},
    getters:{}
})

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(appStore, import.meta.hot))
}