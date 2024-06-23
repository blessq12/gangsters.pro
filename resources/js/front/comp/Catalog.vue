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
        categoryBarObserver() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach( e => {
                    if (!e.isIntersecting) {
                        this.$refs.favWrap.classList.add('show')
                        this.$refs.categoryBar.classList.add('scrolled')
                    } else {
                        this.$refs.favWrap.classList.remove('show')
                        this.$refs.categoryBar.classList.remove('scrolled')
                    }
                });
            }, {
                threshold: [1],
                rootMargin: '0px 0px 200px 0px',
                
            });
            observer.observe(this.$refs.categoryBar);
        },
        categoryObserver() {
            const isMobile = window.innerWidth < 992;
            const threshold = isMobile ? [0.5] : [0.1];
            this.goods.forEach(category => {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.currentCategory = category.uri;
                        }
                    });
                }, {
                    threshold: threshold,
                    rootMargin: '-100px 0px -300px 0px',
                });
                observer.observe(this.$refs[category.uri + '-section'][0]);
            });
        }
    },
    mounted() {
        this.currentCategory = this.goods[0].uri;
        this.categoryBarObserver();
        this.categoryObserver();
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
    <div class="category-bar scrolled" ref="categoryBar">
        <div class="container d-flex">
            <div class="fav-wrap d-flex align-items-center" ref="favWrap">
                <button 
                type="button"
                class="btn rounded btn-danger d-flex align-items-center" 
                @click="appStore.modal=true, appStore.modalName='fav'"
                >
                <i class="fa fa-heart" style="margin-right: 6px;"></i>
                <transition
                    enter-active-class="animate__animated animate__faster animate__fadeIn"
                    leave-active-class="animate__animated animate__faster animate__fadeOut"
                    mode="out-in"
                >
                    {{ localStore.fav.length }}
                </transition>
            </button>
        </div>
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
                                    {{ localStore.checkExist('fav', product) ? '' : '+' }}
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
.category
    .col
        max-width: unset !important
    .category-list
        flex-wrap: nowrap
        overflow: hidden
        overflow-x: scroll
        @media(min-width: 992px)
            flex-wrap: wrap
        &::-webkit-scrollbar
            display: none
        .col
            max-width: unset
            padding: 12px
            &:hover
                background: rgba(212, 212, 212, 0.265)
    .product
        .header
            min-height: 160px
            position: relative
            padding: 6px
        .content
            margin: 12px 0
            min-height: 45px
            span
                font-weight: 400
                font-size: 1.1rem
                display: block
                line-height: 1
        .footer
            display: flex
            align-items: center
            justify-content: space-between
            button
                background: #dedede
                border: unset
                padding: 14px 18px
                white-space: nowrap
                margin-right: 12px
                &:hover
                    background: #2e2e2e
                    color: #fff
                &.active
                    background: $color-main
                    color: #fff
            span
                &.price
                    font-size: 1.1rem
                    font-weight: 700
                    margin-bottom: -4px
                    &::after
                       content: 'руб.'
                       padding-left: 4px 
                       font-weight: 200
                &.weight
                    font-size: .8rem
                    font-weight: 500
                    &::after
                       content: 'гр.'
                       padding-left: 4px
                       font-weight: 200 
.section-title
    border-bottom: 1px solid #dedede
    width: 100%
    h2
        font-size: 1.5rem
        font-weight: 500
        margin-bottom: 12px
        @media(min-width: 768px)
            font-size: 2rem
            font-weight: 700

.fav-wrap
    min-width: 0px 
    width: 0px
    overflow: hidden
    transition: all 0.3s
    &.show
        min-width: 74px
.favorite
    border: unset
    padding: unset
    margin: unset
    background: #dedede
    padding: 8px
    margin-right: 8px
    font-size: 1.2rem
    transition: all .3s
    &:hover    
        background: darken($color-main, 10%)
        color: #fff
    &.active
        background: red
        color: #fff
        padding: 8px 15px
.additional
    background: transparent
    border: unset
    padding: unset
    margin: unset
    background: #dedede
    padding: 8px 22px
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
    position: sticky
    top: -1px
    z-index: 1
    transition: all .3s
    &.scrolled
        border-bottom: 1px solid #dedede
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.4)
    ul
        display: flex
        align-items: center
        list-style: none
        padding: 5px 0
        margin: 0
        overflow: hidden
        overflow-x: auto
        scrollbar-width: none
        -ms-overflow-style: none
        &::-webkit-scrollbar
            display: none
        li
            margin-right: 12px
            button
                background: transparent
                border: 1px solid #dedede
                color: $color-secondary
                &:hover
                    background: #dedede
                    color: $color-main
                &.active
                    background: $color-main
                    color: #fff
</style>