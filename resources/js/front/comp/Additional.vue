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
            <div class="col-12 col-lg-6" v-if="product && product.images && product.images.length > 0">
                <swiper
                    :modules="[Pagination, Navigation, Autoplay]"
                    :pagination="{ clickable: true }"
                    :navigation="true"
                    :autoplay="true"
                >
                    <swiper-slide v-for="image in product.images" :key="image">
                        <img 
                            :src="image" 
                            :alt="product.name"
                            class="w-100 img-fluid rounded"
                            
                        >
                    </swiper-slide>
                </swiper>
            </div>
            <div class="col-12 col-lg-6" v-else>
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
                            <b>Состав: </b>
                            {{ product.consist }}
                        </p>
                        <ul class="list-unstyled d-flex flex-row gap-3">
                            <li> <b>Цена: </b> {{ product.price }} </li>
                            <li> <b>Вес: </b> {{ product.weight }} </li>
                        </ul>
                    </div>
                    <div class="col-12 mb-4">
                        <p>Дополнительная информация</p>
                        <ul class="list-unstyled p-0 m-0">
                            <li><b>Острый: </b>{{ product.spicy ? 'Да' : 'Нет' }}</li>
                            <li><b>Хит: </b>{{ product.hit ? 'Да' : 'Нет' }}</li>
                            <li><b>Лук: </b>{{ product.onion ? 'Да' : 'Нет' }}</li>
                            <li><b>Чеснок: </b>{{ product.garic ? 'Да' : 'Нет' }}</li>
                            <li><b>Можно для детей: </b>{{ product.kidsAllow ? 'Да' : 'Нет' }}</li>
                        </ul>
                    </div>
                    <div class="col-12 d-flex flex-row gap-3">
                        <button class="btn btn-primary btn-sm rounded">
                            В корзину
                            <i class="fa fa-shopping-cart ms-2"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm rounded">
                            В избранное
                            <i class="fa fa-heart ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <!-- <div class="modal-footer border-0">
        <button 
            @click="appStore.currentAdditional = null" 
            type="button" 
            class="btn-close-modal rounded" 
            data-bs-dismiss="modal"
        >
            Закрыть
        </button>
      </div> -->
    </div>
  </div>
</div>
</template>

<style scoped lang="sass">
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