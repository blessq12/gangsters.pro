<script>
import { mapStores } from 'pinia';
import { appStore } from '../../stores/appStorage';
import { localStore } from '../../stores/localStore';


export default {
    props: {
        goods: Object,
        required: true
    },
    data() {
        return {
            currentCategory: this.goods[0].uri,
            scrollPoint: null,
            intersectedCategory: null
        };
    },
    computed: {
        ...mapStores(appStore, localStore)
    },
    methods: {
        scrollToCategory(category) {
            this.$nextTick(() => {
                const element = document.querySelector(`[data-category="${category}"]`); // Get the reference directly
                if (element) { // Check if the element exists
                    const targetPosition = element.getBoundingClientRect().top + window.scrollY; // Calculate target position
                    window.scrollTo({
                        top: targetPosition - 100, // Adjust for any offset if needed
                        behavior: 'smooth'
                    });
                }
            });
        },
        positionObserver() {
            //
            const options = {
                threshold: 0.9
            }
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.currentCategory = entry.target.dataset.category
                    }
                })
            }, options)
            this.$refs.category.forEach((category) => {
                observer.observe(category)
            })
        },
        categooryBarObserver() {

            const categoryBarContainer = document.querySelector('#category-bar-container')
            const categoryBar = document.querySelector('.category-bar')

            const options = {
                threshold: 1
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        categoryBar.classList.add('scrolled')
                    }else {
                        categoryBar.classList.remove('scrolled')
                    }
                })
            }, options)
            observer.observe(categoryBar)
        },
        leftScrollCategory() {
            const categoryList = document.querySelector('#category-list');
            const categoryButton = document.querySelector(`#categoryButton-${this.currentCategory}`);
            
            const scrollAmount = categoryButton.offsetLeft; 
            // Smooth scrolling effect
            const startScrollLeft = categoryList.scrollLeft;
            const distance = scrollAmount - startScrollLeft;
            const duration = 300; // Duration in milliseconds
            let startTime = null;

            const animation = (currentTime) => {
                if (!startTime) startTime = currentTime;
                const timeElapsed = currentTime - startTime;
                const progress = Math.min(timeElapsed / duration, 1);
                categoryList.scrollLeft = startScrollLeft + distance * progress;
                if (timeElapsed < duration) {
                    requestAnimationFrame(animation);
                }
            };

            requestAnimationFrame(animation); 
        }
    },
    mounted() {
        this.positionObserver();
        this.categooryBarObserver();
    },
    watch: {
        currentCategory(newVal) {
            this.leftScrollCategory()
        }
    }
}
</script>

<template>
    <!-- category bar -->
        <div class="category-bar">
            <div class="container d-flex" id="category-bar-container">
            <ul class="position-relative" id="category-list">
                <li v-for="el in goods" :key="el.uri">
                    <a 
                        :id="'categoryButton-' + el.uri"
                        @click="scrollToCategory(el.uri)" 
                        class="btn rounded btn-secondary" 
                        :class="{ active: el.uri === currentCategory }"
                    >
                        {{ el.name }}
                    </a>
                </li>
                </ul>
            </div>
        </div>
    <!-- end category bar -->

    <!-- catalog -->
    <div class="container mt-5" id="catalog-container">
        <div class="category" v-for="category in goods" :key="category.uri" :data-category="category.uri" ref="category">
            <div class="scroll-point" :data-category="category.uri"></div>
            <div class="row mb-4">
                <div class="col">
                    <div class="section-title">
                        <h2>{{ category.name }}</h2>
                    </div>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3 mb-4 category-list">
                <ProductCard v-for="product in category.products" :key="product.id" :product="product"/>
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
    z-index: 5
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
            a
                background: transparent
                white-space: nowrap
                border: 1px solid #dedede
                color: $color-secondary
                &:hover
                    background: #dedede
                    color: $color-main
                &.active
                    background: $color-main
                    color: #fff
</style>