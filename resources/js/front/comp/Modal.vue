<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
import { localStore } from '../../stores/localStore';
export default {
    mounted() {
        
    },
    computed: {
        ...mapStores( appStore, localStore )
    },
    watch: {
        'appStore.modal': {
            handler(val) {
                if (val){ document.body.classList.add('overflow-hidden') } else { document.body.classList.remove('overflow-hidden') }
            }
        }
    }
}
</script>

<template>

    <teleport to="body">
        <transition
            enter-active-class="animate__animated animate__fadeIn"
            leave-active-class="animate__animated animate__fadeOut"
            mode="out-in"
        >
            <div class="wrap" v-if="appStore.modal">
                <div class="overlay" @click="appStore.modal = !appStore.modal"></div>
                <div class="container position-relative">
                    <div class="row row-cols-1 row-cols-md-1 row-cols-lg-2 justify-content-center">
                        <div class="col">
                            <div class="modal-win p-4 rounded bg-light visible">
                                <div class="mb-4 d-flex align-items-center justify-content-between">
                                    <h4 class="mb-0">
                                        {{ appStore.modalName == 'cart' ? 'Корзина' : '' }}
                                        {{ appStore.modalName == 'fav' ? 'Избранное' : '' }}
                                        {{ appStore.modalName == 'user' ? 'Пользователь' : '' }}
                                    </h4>
                                    <button class="btn" type="button" @click="appStore.modal = !appStore.modal">
                                        <i class="fa fa-close"></i>
                                    </button>
                                </div>
                                
                                <component :is="appStore.modalName + '-component'"></component>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

    </teleport>
</template>

<style lang="sass" scoped>
.wrap
    display: flex
    align-items: center
    padding: 48px
    .modal-win
        max-height: calc( 100vh - 48px )
        overflow: hidden
        overflow-y: scroll
        position: relative
        z-index: 22
</style>