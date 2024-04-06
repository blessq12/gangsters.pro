import { defineStore, acceptHMRUpdate } from "pinia";

export const userStore = defineStore('user', {
    state: () => ({
        auth: false,
        userData: null,

    }),
    actions: {
        loadStore() {
            console.log('load user store')
        }
    },
    getters:{}
} )