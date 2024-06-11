<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
import { localStore } from '../../stores/localStore';

export default {
    props: {
        goods: Object
    },
    data() {
        return {
            currentCategory: null,
            observer: null
        };
    },
    computed: {
        ...mapStores(appStore, localStore)
    },
    methods: {
        showImage(prod) {
            if (prod.images.length) {
                return prod.thumbs[0].medium
            } else {
                return 'https://via.placeholder.com/512x512'
            }
        },
        scrollToCategory(uri) {
            const categoryElement = this.$refs[uri][0];
            
            if (categoryElement) {
                categoryElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        },
        scrollCategoryBarToCurrent() {
            let li = this.$refs[this.currentCategory + '-button'][0];
            
            if (li) {
                const parentUl = li.closest('ul');
                if (parentUl) {
                    parentUl.scrollTo({
                        left: li.offsetLeft - parentUl.offsetLeft,
                        behavior: 'smooth'
                    });
                }
            }
        },
        createObserver() {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        
                        this.currentCategory = entry.target.dataset.uri;
                        
                    }
                });
            }, {
                root: null,
                rootMargin: this.appStore.device === 'phone' ? '0px 0px -50% 0px ' : '-20% 0px -20% 0px',
                threshold: this.appStore.device === 'phone' ? 1 : 0.1,
            });

            this.goods.forEach( category => {
                const categoryElement = this.$refs[category.uri + '-section'][0];
                if (categoryElement) {
                    this.observer.observe(categoryElement);
                }
            });
        }
    },
    mounted() {
        this.currentCategory = this.goods[0].uri;
        this.createObserver();
    },
    beforeDestroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
    },
    watch: {
        currentCategory() {
            this.scrollCategoryBarToCurrent();
        }
    }
}
</script>

<template>
    <!-- category bar -->
    <div class="category-bar sticky-top" style="z-index: 1;">
        <div class="container">

            <ul id="ddddd">
                <li 
                    v-for="el in goods" 
                    :key="el.uri" 
                    :ref="el.uri + '-button'"
                >
                    <button 
                        type="button" 
                        class="btn rounded btn-secondary" 
                        @click="scrollToCategory(el.uri), currentCategory=el.uri" 
                        
                        :class="{ active: el.uri === currentCategory }"
                        >
                        {{ el.name }}
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <!-- category bar -->
    <!-- catalog -->
    <div class="container mt-5">
        <div class="category" v-for="category in goods" :key="category.uri" :data-uri="category.uri" :ref="category.uri + '-section'">
            <div class="scrooll-point" :ref="category.uri"></div>
            <div class="row mb-4">
                <div class="col">
                    <div class="section-title">
                        <h2>
                            {{ category.name }}
                        </h2>
                    </div>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3 mb-4 category-list">
                <div class="col rounded" v-for="product in category.products" :key="product.id">
                    <div class="product">
                        <div class="header bg-image rounded" :style="'background: url('+ showImage(product) +')'">
                            <div class="badge">
                                <button 
                                    type="button" 
                                    :class="['favorite', 'rounded', { active: localStore.checkExist('fav', product) }]" 
                                    @click="localStore.manageStore('fav', product)"
                                >
                                    <i :class="localStore.checkExist('fav', product) ? 'fa fa-heart' : 'fa fa-heart-o'"></i>
                                </button>
                                <button
                                    type="button" 
                                    class="additional rounded"
                                    @click="appStore.additional = !appStore.additional; appStore.currentAdditional = product"
                                >i</button>
                            </div>
                        </div>
                        <div class="content">
                            <span>{{ product.name }}</span>
                        </div>
                        <div class="footer">

                            <button type="button" class="btn rounded" @click="localStore.manageStore('cart', product)" v-if="!localStore.checkExist('cart', product)">
                                + В корзину
                            </button>
                            <button type="button" class="btn rounded active" @click="localStore.manageStore('cart', product)" v-else>
                                В корзине
                            </button>
                            <div class="d-block">
                                <span class="d-block price">{{ product.price ?? 'Не указано' }}</span>
                                <span class="d-block weight">{{ product.weight ?? 'Не указано' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end catalog -->
</template>

<style lang="sass" scoped>
.favorite
    background: transparent
    border: unset
    padding: unset
    margin: unset
    background: $color-main
    padding: 8px
    margin-right: 8px
    font-size: 1.2rem
    &:hover    
        background: darken($color-main, 10%)
        color: #fff
    &.active
        color: red
.additional
    background: transparent
    border: unset
    padding: unset
    margin: unset
    background: $color-main
    padding: 8px 16px
    font-size: 1.2rem
    &:hover    
        background: darken($color-main, 10%)
        color: #fff
.category
    position: relative
    .scrooll-point
        position: absolute
        top: -100px
        left: 0
    .col
        max-width: 320px

.category-bar
    padding: 10px 0px
    background: #fff
    ul
        display: flex
        align-items: center
        list-style: none
        padding: 0
        margin: 0
        overflow: hidden
        overflow-x: auto
        scrollbar-width: none
        -ms-overflow-style: none
        &::-webkit-scrollbar
            display: none
        li
            margin-right: 12px
</style>