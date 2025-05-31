<script>
import gsap from "gsap";
import { mapStores } from "pinia";
import { appStore } from "../../stores/appStorage";
import { localStore } from "../../stores/localStore";
import { userStore } from "../../stores/userStore";

export default {
    mounted() {
        if (this.userStore.authStatus) {
            this.cart = true;
        }
        window.addEventListener("scroll", this.updateScrollTop);
        this.updateScrollTop();

        gsap.set(this.$refs.topButton, {
            yPercent: 100,
            opacity: 0,
            display: "none",
        });

        this.cartTimeline = gsap
            .timeline({ paused: true })
            .to(this.$refs.cartIcon, {
                scale: 1.2,
                duration: 0.2,
                ease: "back.out(1.7)",
            })
            .to(this.$refs.cartIcon, {
                scale: 1,
                duration: 0.15,
                ease: "back.in(1.7)",
            });
    },
    beforeDestroy() {
        window.removeEventListener("scroll", this.updateScrollTop);
        if (this.cartTimeline) {
            this.cartTimeline.kill();
        }
    },
    data: () => ({
        cart: false,
        cartContent: false,
        contentHide: false,
        cartTimeline: null,
        scrolled: false,
        scrollTop: 0,
    }),
    computed: {
        ...mapStores(localStore, userStore, appStore),
        scrollHeight() {
            return window.scrollY;
        },
    },
    methods: {
        openModal(name) {
            if (this.cartTimeline) {
                this.cartTimeline.restart();
            }
            this.appStore.modal = true;
            this.appStore.modalName = name;
        },
        updateScrollTop() {
            requestAnimationFrame(() => {
                this.scrollTop = window.pageYOffset;
            });
        },
        scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        },
        animateCartContent(show) {
            const content = this.$refs.cartContent;
            if (show) {
                gsap.fromTo(
                    content,
                    { width: 0, opacity: 0 },
                    {
                        width: "auto",
                        opacity: 1,
                        duration: 0.3,
                        ease: "power2.inOut",
                    }
                );
            } else {
                gsap.to(content, {
                    width: 0,
                    opacity: 0,
                    duration: 0.3,
                    ease: "power2.inOut",
                });
            }
        },
        animateToTop(show) {
            const button = this.$refs.topButton;
            if (!button) return;

            gsap.to(button, {
                yPercent: show ? 0 : 100,
                opacity: show ? 1 : 0,
                duration: 0.5,
                ease: show ? "back.out(1.7)" : "power2.in",
                display: show ? "block" : "none",
            });
        },
    },
    watch: {
        cart(val) {
            this.contentHide = true;
            setTimeout(() => {
                this.contentHide = false;
                this.cartContent = val;
                this.animateCartContent(val);
            }, 100);
        },
        "localStore.cart": {
            handler(newVal) {
                this.cart = newVal.length > 0;
                if (newVal.length > 0 && this.cartTimeline) {
                    this.cartTimeline.restart();
                }
            },
            deep: true,
        },
        scrollTop: {
            handler(newVal) {
                if (newVal > 800 && !this.scrolled) {
                    this.scrolled = true;
                    this.animateToTop(true);
                } else if (newVal <= 800 && this.scrolled) {
                    this.scrolled = false;
                    this.animateToTop(false);
                }
            },
            immediate: true,
        },
    },
};
</script>

<template>
    <div class="fixed bottom-8 left-8 z-[51] flex flex-col gap-6">
        <div class="group relative">
            <div
                class="relative overflow-hidden bg-white rounded-lg shadow-lg p-4 transition-all duration-300 ease-out transform hover:scale-105 hover:shadow-2xl cursor-pointer"
                :class="{
                    'ring-2 ring-green-400 bg-gradient-to-br from-green-50 to-white':
                        cart,
                }"
                @click="openModal('cart')"
            >
                <div class="relative z-10 flex items-center justify-center">
                    <i
                        ref="cartIcon"
                        class="mdi mdi-cart text-3xl"
                        :class="cart ? 'text-green-500' : 'text-gray-600'"
                    ></i>
                </div>
                <div
                    class="absolute inset-0 bg-gradient-to-tr from-green-100 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                ></div>
            </div>

            <div
                class="absolute left-full ml-4 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-0 translate-x-2"
            >
                <div
                    class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm whitespace-nowrap shadow-xl"
                >
                    {{ cart ? "Перейти в корзину" : "Корзина пуста" }}
                    <div
                        class="absolute left-0 top-1/2 -translate-y-1/2 w-2 h-2 bg-gray-800 transform rotate-45 -translate-x-1"
                    ></div>
                </div>
            </div>
        </div>

        <button
            ref="topButton"
            @click="scrollToTop()"
            class="fixed bottom-8 right-8 bg-white rounded-lg p-4 shadow-lg transition-all duration-300 hover:shadow-2xl group"
            :class="{
                'translate-y-0 opacity-100': scrolled,
                'translate-y-20 opacity-0': !scrolled,
            }"
        >
            <div class="relative overflow-hidden">
                <div
                    class="flex flex-col items-center justify-center gap-1 relative z-10"
                >
                    <i
                        class="mdi mdi-arrow-up text-2xl text-green-500 transition-transform duration-300 group-hover:-translate-y-1"
                    ></i>
                    <span
                        class="text-sm font-medium text-gray-600 hidden md:block transition-all duration-300 group-hover:text-green-500"
                        >Наверх</span
                    >
                </div>
                <div
                    class="absolute inset-0 bg-gradient-to-t from-green-50 via-white to-white transform scale-y-0 group-hover:scale-y-100 transition-transform duration-300 origin-bottom"
                ></div>
            </div>
        </button>
    </div>
</template>

<style scoped>
@import "@mdi/font/css/materialdesignicons.css";

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
