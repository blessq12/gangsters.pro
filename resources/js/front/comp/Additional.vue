<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
import { localStore } from '../../stores/localStore';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Pagination, Navigation, Autoplay } from 'swiper/modules';

export default {
    setup(){
        return {
            Pagination,
            Navigation,
            Autoplay
        }
    },
    components: {
        Swiper,
        SwiperSlide
    },
    computed: {
        ...mapStores(appStore, localStore)
    },
    data() {
        return {
            product: { name: '' } // Initialize product with a name property
        }
    },
    watch: {
        'appStore.currentAdditional': {
            handler(val) {
                this.product = val || { name: '' }; // Ensure product is an object
            },
            immediate: true
        }
    }
};
</script>

<template>
<div class="modal modal-xl fade" id="additional" tabindex="-1" aria-labelledby="additionalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content rounded">
      <div class="modal-header">
        <h4 class="modal-title fs-5" id="additionalLabel">{{ product.name + ' - ' + product.price + ' руб. (' + product.weight + ' гр.)' || 'Не известно' }}</h4> 
        <button 
            type="button" 
            class="btn-close" 
            data-bs-dismiss="modal" 
            aria-label="Close"
            @click="appStore.currentAdditional = null" 
        ></button>
      </div>
      <div class="modal-body pb-4">
        <div class="row">
            <div class="col-12 col-lg-6 mb-4 mb-lg-0" v-if="product && product.images && product.images.length > 0">
                <swiper
                    :modules="[Pagination, Navigation, Autoplay]"
                    :pagination="{ clickable: true }"
                    :navigation="true"
                    :autoplay="true"
                >
                    <swiper-slide v-for="image in product.images" :key="image">
                        <img 
                            v-lazy="image"
                            :alt="product.name"
                            class="w-100 img-fluid rounded"
                        >
                    </swiper-slide>
                </swiper>
            </div>
            <div class="col-12 col-lg-6 mb-4 mb-lg-0" v-else>
                <img 
                    src="/images/placeholder/product-image-empty-1024x1024.jpg" 
                    alt=""
                    class="w-100 img-fluid rounded"
                >
            </div>
            <div class="col">
                <div class="row">
                    <div class="col-12">
                        <p>
                            <span class="fs-5 d-block fw-bold ">Состав: </span>
                            {{ product.consist }}
                        </p>
                    </div>
                    <div class="col-12 mb-4">
                        <p class="fs-5 mb-2 fw-bold">Дополнительная информация</p>
                        <ul class="list-unstyled p-0 m-0">
                            <li><b>Острый: </b>{{ product.spicy ? 'Да' : 'Нет' }}</li>
                            <li><b>Хит: </b>{{ product.hit ? 'Да' : 'Нет' }}</li>
                            <li><b>Лук: </b>{{ product.onion ? 'Да' : 'Нет' }}</li>
                            <li><b>Чеснок: </b>{{ product.garic ? 'Да' : 'Нет' }}</li>
                            <li><b>Можно для детей: </b>{{ product.kidsAllow ? 'Да' : 'Нет' }}</li>
                        </ul>
                    </div>
                    <div class="col-12 mb-2">
                        <ul class="list-unstyled d-flex flex-row gap-3 fs-4">
                            <li> <b>Цена: </b> {{ product.price }} руб.</li>
                            <li> <b>Вес: </b> {{ product.weight }} гр.</li>
                        </ul>
                    </div>
                    <div class="col-12 d-flex flex-row gap-2">
                        <transition
                            enter-active-class="animate__animated animate__fadeIn"
                            leave-active-class="animate__animated animate__fadeOut" 
                            mode="out-in"
                        >
                            <button 
                                class="btn btn-main btn-sm rounded" 
                                v-if="!localStore.checkExist('cart', product)"
                                @click="localStore.manageStore('cart', product)"
                            >
                                <i class="fa fa-shopping-cart" style="margin-right: 6px;"></i>
                                В корзину
                            </button>

                            <!-- qty manage -->
                            <div class="prod-qty" v-else>
                                <button class="btn rounded" @click="localStore.manageQty(false, product)">-</button>
                                <span>{{ localStore.getQty(product) }}</span>
                                <button class="btn rounded" @click="localStore.manageQty(true, product)">+</button>
                            </div>
                            <!-- end qty manage -->
                        </transition>
                        <button 
                            class="btn btn-sm rounded" 
                            :class="{
                                'btn-outline-danger': !localStore.checkExist('fav', product),
                                'btn-danger': localStore.checkExist('fav', product)
                            }"
                            @click="localStore.manageStore('fav', product)"

                        >
                            <i 
                                class="fa"
                                :class="{
                                    'fa-heart': localStore.checkExist('fav', product),
                                    'fa-heart-o': !localStore.checkExist('fav', product)
                                }"
                            ></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<style scoped lang="sass">
.prod-qty
    display: flex
    align-items: center
    height: 46px
    span
        display: flex
        align-items: center
        justify-content: center
        min-width: 60px
        height: 100%
        border: $color-main 1px solid
        margin-right: 0 !important
        color: $color-main
        transition: all .3s
    button
        border: $color-main 1px solid
        background: $color-main !important
        margin-right: 0 !important
        color: #fff
        transition: all .3s
        min-width: 40px
        height: 100%
        padding: unset
        display: flex
        align-items: center
        justify-content: center
        font-size: 1.3rem
        &:hover
            background: lighten($color-main, 30%) !important
        &:first-child
            border-radius: 12px 0 0 12px !important
            margin: 0
        &:last-child
            border-radius: 0 12px 12px 0 !important
        &:hover
            background: $color-main
            color: #fff

.btn-close-modal
    background: #fff
    color: #000
    border: 1px solid #000
    padding: 12px 16px
    font-size: 14px
    font-weight: 500
    transition: all .3s
    &:hover
        background: #000
        color: #fff
</style>