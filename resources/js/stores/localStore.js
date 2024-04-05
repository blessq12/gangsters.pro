import { defineStore, acceptHMRUpdate } from "pinia";
import { array } from "yup";

export const localStore = defineStore('local', {
    state: () => ({
        cart: [],
        fav: []
    }),
    actions: {
        loadStorage() {

            let localCart = JSON.parse(localStorage.getItem('cart')),
                localFav = JSON.parse(localStorage.getItem('fav'))
            
            if (localStorage.getItem('cart') == null) {
                localStorage.setItem('cart', JSON.stringify([]))
                this.cart = []
            }
            if (localStorage.getItem('fav') == null) {
                localStorage.setItem('fav', JSON.stringify([]))
                this.cart = []
            }

            this.cart = localCart == null ? [] : localCart
            this.fav = localFav == null ? [] : localFav

        },
        manageStore(storeName, product) {
            
            let store = null

            if (storeName == 'cart') store = this.cart
            if (storeName == 'fav') store = this.fav
            
            if (store.findIndex(e => e.id == product.id) == -1) {
                if (storeName == 'cart') {
                    product.qty = 1
                }
                store.push(product)
            } else {
                store.splice(store.findIndex(e => e.id == product.id), 1)
            }

            localStorage.setItem(storeName, JSON.stringify(store))
        },
        checkExist(storeName, product) {
            
            let store = null

            if (storeName == 'cart') store = this.cart
            if (storeName == 'fav') store = this.fav

            if (store.findIndex(e => e.id == product.id) !== -1) {
                return true
            } else {
                return false
            }

        },
        manageQty( direction, product ) {
            let index = this.cart.findIndex(e => e.id == product.id)
            if (direction) {
                this.cart[index].qty++
            } else {
                this.cart[index].qty--
            }
            if (this.cart[index].qty < 1) {
                this.cart.splice(index, 1)    
            }
            localStorage.setItem('cart', JSON.stringify(this.cart))
        }
    },
    getters: {
        cartTotal() {
            return this.cart.reduce( (acc, item) => { return acc += parseInt(item.qty) * parseInt(item.price) }, 0 )
        }
    }
})

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(localStore, import.meta.hot))
}