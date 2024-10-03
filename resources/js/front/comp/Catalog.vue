<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
import { localStore } from '../../stores/localStore';
import Tooltip from 'bootstrap/js/dist/tooltip';

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
        },
        hideTooltip(event) {
            const tooltipTrigger = event.currentTarget;
            const tooltipInstance = Tooltip.getInstance(tooltipTrigger);
            if (tooltipInstance) {
                tooltipInstance.hide();
            }
        }
    },
    mounted() {
        this.currentCategory = this.goods[0].uri;
        this.categoryBarObserver();
        this.categoryObserver();
        new Tooltip(document.body, {
            selector: "[data-bs-toggle='tooltip']",
        });
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
            <div class="fav-wrap d-flex align-items-center" ref="favWrap" :style="{ marginRight: localStore.fav.length > 9 ? '8px' : '0' }">
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
    <div class="container mt-5" id="catalog-container">
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
                <ProductCard v-for="product in category.products" :key="product.id" :product="product" />
            </div>
        </div>
    </div>
    <!-- end catalog -->
</template>

<style lang="sass" scoped>
#catalog-container
    margin-bottom: 50px
    @media(min-width: 992px)
        margin-bottom: 150px
.btn-main
    background: $color-main !important
    color: #fff
    &:hover
        background: darken($color-main, 60%)

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
            min-width: 320px
            width: fit-content
            position: relative
            overflow: hidden
            @media(min-width: 768px)
                min-width: 260px
            @media(min-width: 992px)
                min-width: 25%
                width: 25%
            .hover-over
                z-index: -1
                opacity: 0
                position: absolute
                transition: all .7s ease-in-out
                top: 0
                left: 0
                width: 100%
                height: 100%
                background: url('/images/placeholder/pattern-50.png') no-repeat center center / cover
            &:hover
                .hover-over
                    opacity: .4
    
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
.category
    position: relative
    .scrooll-point
        position: absolute
        top: -100px
        left: 0
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
        z-index: 2
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