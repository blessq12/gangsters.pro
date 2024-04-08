import axios from "axios";
import { defineStore, acceptHMRUpdate } from "pinia";
import { useToast } from 'vue-toastification'
const toast = useToast()

export const userStore = defineStore('user', {
    state: () => ({
        authStatus: false,
        userData: null,
        authErrorBag: {}
    }),
    actions: {
        loadStore() {
            let userToken = localStorage.getItem('token')
            if (userToken !== null) {
                axios.defaults.headers['Authorization'] = 'Bearer ' + userToken
                axios.defaults.headers['Accept'] = 'application/json'
                axios.post('/api/auth/user')
                    .then(res => { 
                        toast.success('Учетная запись загружена')
                        this.authStatus = true
                        this.userData = res.data.user
                    } )
                    .catch(err => {
                        toast.error(err.response.data.message + '\nНеобходима повторная авторизация')
                        localStorage.removeItem('token')
                    } )
            }
        },
        auth(cred) {
            axios.post( '/api/auth/login', cred )
                .then(res => {
                    toast.success('Успешная авторизация')
                    this.authStatus = true
                    this.userData = res.data.user
                    localStorage.setItem('token', res.data.token)
                    axios.defaults.headers['Accept'] = 'application/json'
                    axios.defaults.headers['Authorization'] = 'Bearer-' + res.data.token
                })
                .catch(err => {
                    err.response.data.errors.forEach( e => toast.error(e.message) )
                })
        },
        register(cred) {
            axios.post( '/api/auth/register', cred )
                .then(res => { 
                    toast.success('Успешная регистрация')
                    this.authStatus = true
                    this.userData = res.data.user
                    localStorage.setItem('token', res.data.token)
                    axios.defaults.headers['Accept'] = 'application/json'
                    axios.defaults.headers['Authorization'] = 'Bearer-' + res.data.token
                 })
                .catch (err => { 
                    for (const [key, value] of Object.entries(err.response.data.errors)) {
                        toast.error(`${value}`)
                    }
                 })
        }
    },
    getters:{}
} )