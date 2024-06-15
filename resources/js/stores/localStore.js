import { defineStore, acceptHMRUpdate } from "pinia";
import { array } from "yup";
import { useToast } from "vue-toastification";
const toast = useToast();

export const localStore = defineStore('local', {
    state: () => ({
        cart: [],
        fav: []
    }),
    actions: {
        loadStorage() {
            console.log('load local store')
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
                if (storeName == 'cart'){
                    // toast.success( product.name + ' добавлен в корзину')
                }
                if (storeName == 'fav'){
                    // toast.success( product.name + ' добавлен в избранное')
                }
            } else {
                store.splice(store.findIndex(e => e.id == product.id), 1)
                if (storeName == 'cart'){
                    // toast.warning( product.name + ' удалено из корзины')
                }
                if (storeName == 'fav'){
                    // toast.warning( product.name + ' удалено из избранного')
                }
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
                toast.warning( product.name + ' удалено из корзины')
            }
            localStorage.setItem('cart', JSON.stringify(this.cart))
        },
        clearStore(storeName){
            if (storeName == 'cart'){
                this.cart = []
            }
            if (storeName == 'fav'){
                this.fav = []
            }
            localStorage.setItem(storeName, JSON.stringify([]))
            toast.success( storeName == 'cart' ? 'Корзина очищена' : 'Избранное очищено' )
        },
        async createOrder(data)
        {
            try {
                const response = await axios.post('/api/orders/create', data)
                return true
                // return response
            } catch (error) {
                return false
            }
        }
    },
    getters: {
        cartTotal()
        {
            return this.cart.reduce( (acc, item) => { return acc += parseInt(item.qty) * parseInt(item.price) }, 0 )
        },
        cartQty()
        {
            return this.cart.reduce( (acc, item) => { return acc += parseInt(item.qty) }, 0 )
        },
        favTotal()
        {
            return this.fav.reduce( ( acc, item ) => { return acc+= parseInt(item.price)}, 0 )
        }
    }
})

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(localStore, import.meta.hot))
}