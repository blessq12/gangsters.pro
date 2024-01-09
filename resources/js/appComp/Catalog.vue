<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
export default{
    props:{
        goods: Object
    },
    data:()=>({}),
    computed:{
        ...mapStores(localStore)
    }
}
</script>

<template>
    <div class="container" v-if="goods.length">
        <div class="row">
            <div class="col-12" style="margin-bottom: 48px;">
                <ul class="list-unstyled p-0 m-0 categories">
                    <li v-for="category in goods" :key="category.id">
                        <button>
                            {{ category.name }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-12" v-for="category in goods" :key="category.id">
                <h4>{{ category.name }}</h4>
                <ul class="list-unstyled p-0 category-list" v-if="category.products.length">
                    <li v-for="product in category.products" :key="product.id">
                        <div class="product">
                            <div class="header bg-image" v-if="product.thumb_medium.length" :style="'background:url('+ product.thumb_medium[0].path +')'">
                            </div>
                            <div class="header bg-image" style="background: url('/images/placeholder/product-image-empty-512x512.jpg');" v-else></div>
                            <div class="content">
                                <h5>{{ product.name }}</h5>
                                <p>{{ product.consist }}</p>
                            </div>
                            <div class="footer">
                                <button type="button" class="btn btn-primary"
                                    @click="localStore.manageStore(product, 'cart')"
                                >
                                    {{ localStore.checkItemInStore( product, 'cart') ? 'In' : 'Add to' }} cart
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-danger mx-1"
                                    @click="localStore.manageStore(product, 'fav')"
                                >
                                    {{ localStore.checkItemInStore(product, 'fav') ? 'In' : 'Add to' }} fav
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button class="btn btn-outline-primary rounded-circle px-3 fw-bold"
                                    @click="$emit('toggleAdditional', product)"
                                >i</button>
                            </div>
                        </div>
                    </li>
                </ul>
                <ul v-else class="list-unstyled p-0 category-list">
                    <li v-for="e in 4">
                        <div class="product placeholder-glow">
                            <div class="header bg-dark placeholder"></div>
                            <div class="content">
                                <h5 class="placeholder col-7 rounded"></h5>
                                <p class="placeholder col-12 mb-0 rounded"></p>
                                <p class="placeholder col-8 rounded"></p>
                            </div>
                            <div class="footer">
                                <button type="button" class="btn placeholder col-4" style="margin-right: 6px;"></button>
                                <button type="button" class="btn placeholder col-2"></button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container" v-else>
        <div class="row">
            <div class="col-12" style="margin-bottom: 48px;">
                <ul class="list-unstyled p-0 m-0 categories placeholder-glow">
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-12 placeholder-glow" v-for="item in pholder" :key="item">
                <h4 class="placeholder col-5 mb-4"></h4>
                <ul class="category-list list-unstyled p-0">
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>