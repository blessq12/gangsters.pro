import axios from "axios";
import { defineStore, acceptHMRUpdate } from "pinia";
import { useToast } from 'vue-toastification'
const toast = useToast()

export const userStore = defineStore('user', {
    state: () => ({
        authStatus: false,
        userData: null,
        authErrorBag: {},
        orders:[]
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
        auth(action, cred) {    
            let url = action == 'login' ? '/api/auth/login' : '/api/auth/register'
            axios.post( url, cred )
                .then(res => { 
                    this.authStatus = true
                    this.userData = res.data.user
                    localStorage.setItem('token', res.data.token)
                    axios.defaults.headers['Accept'] = 'application/json'
                    axios.defaults.headers['Authorization'] = 'Bearer-' + res.data.token
                 })
                .catch (err => { 
                    console.log(err.response.data)
                 })
        },
        logout(){
            this.authStatus = false
            this.userData = null
            localStorage.removeItem('token')
            axios.defaults.headers['Authorization'] = ''
            toast.success('Вы вышли из аккаунта')
        },
        updateUser(data){
            axios.patch( '/api/auth/update-user', data )
                .then(res => {
                    this.userData = res.data.user
                    toast.success('Изменения сохранены')
                 })
                .catch (err => { 
                    for (const [key, value] of Object.entries(err.response.data.errors)) {
                        toast.error(`${value}`)
                    }
                 })
        },
        resetPassword(email){
            axios.post('/api/auth/reset-password', {email: email})
            .then( res => { 
                toast.success(res.data.message)
             })
            .catch( err => { 
                toast.error(err.response.data.message)
             })
        },
        getLastOrders(){
            return axios.get('/api/orders/' + this.userData.id + '/get')
        }
    },
    getters:{}
} )