import { defineStore,acceptHMRUpdate } from "pinia";

export const userStore = defineStore('user',{
    state:()=>({
        auth: false
    }),
    getters:{},
    actions:{}
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(userStore, import.meta.hot))
  }