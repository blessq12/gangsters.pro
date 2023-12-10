import { defineStore, acceptHMRUpdate } from "pinia";

export const orderStore = defineStore('order', {
    state:()=>({
        loading: false,
        orderData:{
            name: '',
            tel: '',
            street: '',
            house: '',
            staircase: '',
            floor: '',
            appartment: '',
            payType: 'cash',
            comment: ''
        }
    }),
    getters:{
        
    },
    actions:{
        createOrder(cartData, state){
            state.loading = true
            let data = {
                cart: cartData,
                orderData: state.orderData
            }
            axios.post('/api/order/create', data).then( res => {
                if (res){

                }
            } ).catch( err => {
                console.log( err )
            } )
        }
    }
})