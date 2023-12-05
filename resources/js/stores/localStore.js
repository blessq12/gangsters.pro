import { defineStore,acceptHMRUpdate } from "pinia";

export const localStore = defineStore( 'local', {
    state:()=>({
        cart: [],
        fav: []
    }),
    getters:{
        totalCart(){
            return this.cart.reduce( (acc, item) => { acc += parseInt(item.price) } )
        }
    },
    actions:{
        loadStores(){
            let cart = localStorage.getItem('cart'),
                fav = localStorage.getItem('fav')

            if (typeof cart !== 'object' && cart !== null){
                this.cart = JSON.parse(cart)
            } else {
                localStorage.setItem('cart', JSON.stringify([]))
            }

            if (typeof fav !== 'object' && fav !== null){
                this.fav = JSON.parse(fav)
            } else {
                localStorage.setItem('fav', JSON.stringify([]))
            }

        },
        manageStore( product , store){
            let repo = null
            switch (store) {
                case 'cart': repo = this.cart
                    break;
                case 'fav': repo = this.fav
                    break;
            }
            if (repo.findIndex( e => e.id === product.id) == -1){
                product.qty = 1
                repo.push(product)
            } else {
                repo.splice( repo.findIndex( e => e.id === product.id ) , 1)
            }
            localStorage.setItem(store, JSON.stringify(repo))
        },
        checkItemInStore( product, store ){
            let repo = null
            switch (store) {
                case 'cart': repo = this.cart
                    break;
                case 'fav': repo = this.fav
                    break;
            }

            if (repo.findIndex( e => e.id === product.id ) !== -1){
                return true
            } else {
                return false
            }
        }
    }
} )

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(localStore, import.meta.hot))
  }