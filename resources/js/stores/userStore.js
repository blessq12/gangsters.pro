import axios from "axios";
import { defineStore, acceptHMRUpdate } from "pinia";

export const userStore = defineStore('user', {
    state: () => ({
        auth: false,
        userData: null,

    }),
    actions: {
        loadStore() {
            console.log('load user store')
        },
        auth(cred) {
            axios.post( '/api/auth/login', cred )
                .then(res => {console.log( res.data )} )
                .catch( err => {console.log( err )} )
        },
        register(cred) {
            axios.post( '/api/auth/register', cred )
                .then(res => { console.log( res.data ) })
                .catch( err => { console.log( err ) } )
        }
    },
    getters:{}
} )