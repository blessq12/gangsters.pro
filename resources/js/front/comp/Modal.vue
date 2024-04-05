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
                <div class="container invisible position-relative">
                    <div class="row row-cols-1 row-cols-md-1 row-cols-lg-2 justify-content-center">
                        <div class="col">
                            <div class="modal-win p-5 rounded bg-light visible">
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
</style>