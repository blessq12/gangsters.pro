<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage'
export default {
    computed: {
        ...mapStores( appStore )
    },
    watch: {
        'appStore.additional': {
            handler(val) {
                if (val) { document.body.classList.add('overflow-hidden') }
                else { document.body.classList.remove('overflow-hidden') }
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
        >
            <div class="wrap" v-if="appStore.additional" >
                <div class="overlay" @click="appStore.additional = !appStore.additional"></div>
                <div class="container invisible position-relative">
                    <div class="row row-cols-1 row-cols-md-1 row-cols-lg-2 justify-content-center">
                        <div class="col d-flex justify-content-center align-items-center">
                            <div class="additional-modal visible bg-light p-5 rounded">
                                <div class="row">
                                    <div class="col">
                                        <div class="h-100 w-100 bg-image rounded" style="background: url('http://via.placeholder.com/512x512');"></div>
                                    </div>
                                    <div class="col">
                                        <div class="content">
                                            <h4 class="fw-semibold">{{ appStore.currentAdditional.name }}</h4>
                                            <p>
                                                <span class="fw-semibold">Состав:</span>
                                                {{ appStore.currentAdditional.consist }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
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