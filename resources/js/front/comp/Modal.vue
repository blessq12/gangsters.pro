<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
export default {
    computed: {
        ...mapStores( appStore )
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
            <div class="wrap" v-if="appStore.modal" @click="appStore.modal = !appStore.modal">
                <div class="container invisible">
                    <div class="row row-cols-1 row-cols-md-1 row-cols-lg-2 justify-content-center">
                        <div class="col">
                            <div class="modal-win p-5 rounded bg-light visible">
                                Окно для корзины/избранного/клиентской инфы
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
</style>