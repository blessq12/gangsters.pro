<script>
import gsap from "gsap";
import { mapStores } from "pinia";
import { appStore } from "../../stores/appStorage";
import { localStore } from "../../stores/localStore";

export default {
    props: {
        links: {
            type: Object,
            required: false,
        },
    },
    data: () => ({
        currentItem: null,
        modalElements: null,
    }),
    computed: {
        ...mapStores(appStore, localStore),
        rootPage() {
            return window.location.pathname == "/";
        },
    },
    methods: {
        initModalElements() {
            this.modalElements = {
                overlay: document.querySelector(".modal-overlay"),
                modal: document.querySelector(".modal-menu"),
                content: document.querySelector(".modal-content"),
            };
        },
        scrollToActiveItem() {
            if (this.currentItem) {
                const bo = this.$refs.backoverlay;
                const activeItem = this.$refs[this.currentItem];
                if (bo && activeItem) {
                    bo.style.maxWidth = activeItem.offsetWidth + "px";
                    bo.style.left = activeItem.offsetLeft + "px";
                }
            }
        },
        animateModalIn() {
            this.initModalElements();
            if (
                !this.modalElements.overlay ||
                !this.modalElements.modal ||
                !this.modalElements.content
            )
                return;

            const { overlay, modal, content } = this.modalElements;

            gsap.set([overlay, modal, content], {
                autoAlpha: 0,
            });

            const tl = gsap.timeline({
                defaults: { duration: 0.4, ease: "power3.out" },
            });

            tl.to(overlay, {
                autoAlpha: 1,
            })
                .fromTo(
                    modal,
                    {
                        xPercent: 100,
                        autoAlpha: 0,
                    },
                    {
                        xPercent: 0,
                        autoAlpha: 1,
                    },
                    "-=0.2"
                )
                .fromTo(
                    content,
                    {
                        yPercent: 20,
                        autoAlpha: 0,
                    },
                    {
                        yPercent: 0,
                        autoAlpha: 1,
                    },
                    "-=0.2"
                );

            return tl;
        },
        animateModalOut() {
            if (
                !this.modalElements?.overlay ||
                !this.modalElements?.modal ||
                !this.modalElements?.content
            ) {
                this.initModalElements();
            }

            if (
                !this.modalElements.overlay ||
                !this.modalElements.modal ||
                !this.modalElements.content
            )
                return;

            const { overlay, modal, content } = this.modalElements;

            const tl = gsap.timeline({
                defaults: { duration: 0.3, ease: "power3.in" },
            });

            tl.to(content, {
                yPercent: 20,
                autoAlpha: 0,
            })
                .to(
                    modal,
                    {
                        xPercent: 100,
                        autoAlpha: 0,
                    },
                    "-=0.1"
                )
                .to(
                    overlay,
                    {
                        autoAlpha: 0,
                    },
                    "-=0.2"
                );

            return tl;
        },
    },
    watch: {
        "appStore.modal": {
            handler(newVal) {
                if (this.rootPage) {
                    const bodyClassList = document.body.classList;

                    if (newVal) {
                        bodyClassList.add("overflow-hidden");
                        this.$nextTick(() => {
                            this.animateModalIn();
                            this.scrollToActiveItem();
                        });
                    } else {
                        const tl = this.animateModalOut();
                        if (tl) {
                            tl.then(() => {
                                bodyClassList.remove("overflow-hidden");
                            });
                        }
                    }
                }
            },
            deep: true,
        },
    },
};
</script>

<template>
    <teleport to="body">
        <div
            class="modal-overlay fixed inset-0 bg-black/30 backdrop-blur-sm z-[52] transition-opacity duration-300"
            v-show="appStore.modal"
        ></div>
        <div
            class="fixed inset-0 z-[53] min-h-screen w-full overflow-y-auto"
            v-show="appStore.modal"
            @click.self="appStore.modal = false"
        >
            <div
                class="modal-menu fixed right-0 top-0 h-full w-full max-w-lg bg-white shadow-xl transform transition-transform duration-300"
            >
                <div class="modal-container h-full flex flex-col">
                    <div
                        class="modal-header flex items-center justify-between border-b border-gray-100 px-6 py-4"
                    >
                        <h4 class="text-xl font-semibold text-gray-800">
                            Меню
                        </h4>
                        <button
                            class="rounded-full p-2 hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            @click.stop="appStore.modal = false"
                        >
                            <i
                                class="mdi mdi-close text-md md:text-2xl text-gray-600"
                            ></i>
                        </button>
                    </div>

                    <nav
                        class="modal-navigation grid grid-cols-4 gap-2 sm:gap-4 p-3 sm:p-6 border-b border-gray-200"
                        v-if="rootPage"
                    >
                        <div
                            class="nav-background absolute bg-primary-600/15 transition-all duration-300 rounded-xl"
                            ref="backoverlay"
                        ></div>

                        <div
                            class="nav-item relative flex flex-col items-center justify-center p-2 sm:p-4 rounded-xl cursor-pointer transition-all duration-200 hover:bg-primary-100"
                            @click="appStore.modalName = 'cart'"
                            :class="{
                                'text-primary-700 bg-primary-100 shadow-sm':
                                    appStore.modalName === 'cart',
                            }"
                            ref="cart"
                        >
                            <div class="nav-icon relative">
                                <i class="mdi mdi-cart text-xl sm:text-2xl"></i>
                                <div
                                    class="absolute -top-2 -right-2 min-w-[18px] sm:min-w-[20px] h-4 sm:h-5 flex items-center justify-center bg-red-600 text-white text-xs font-medium rounded-full px-1"
                                    v-if="localStore.cart.length > 0"
                                >
                                    {{ localStore.cart.length }}
                                </div>
                            </div>
                            <span
                                class="mt-1 sm:mt-2 text-xs sm:text-sm font-medium"
                                >Корзина</span
                            >
                        </div>

                        <div
                            class="nav-item relative flex flex-col items-center justify-center p-2 sm:p-4 rounded-xl cursor-pointer transition-all duration-200 hover:bg-primary-100"
                            @click="appStore.modalName = 'fav'"
                            :class="{
                                'text-primary-700 bg-primary-100 shadow-sm':
                                    appStore.modalName === 'fav',
                            }"
                            ref="fav"
                        >
                            <div class="nav-icon relative">
                                <i
                                    class="mdi mdi-heart text-xl sm:text-2xl"
                                ></i>
                                <div
                                    class="absolute -top-2 -right-2 min-w-[18px] sm:min-w-[20px] h-4 sm:h-5 flex items-center justify-center bg-red-600 text-white text-xs font-medium rounded-full px-1"
                                    v-if="localStore.fav.length > 0"
                                >
                                    {{ localStore.fav.length }}
                                </div>
                            </div>
                            <span
                                class="mt-1 sm:mt-2 text-xs sm:text-sm font-medium"
                                >Избранное</span
                            >
                        </div>

                        <div
                            class="nav-item relative flex flex-col items-center justify-center p-2 sm:p-4 rounded-xl cursor-pointer transition-all duration-200 hover:bg-primary-100"
                            @click="appStore.modalName = 'user'"
                            :class="{
                                'text-primary-700 bg-primary-100 shadow-sm':
                                    appStore.modalName === 'user',
                            }"
                            ref="user"
                        >
                            <div class="nav-icon">
                                <i
                                    class="mdi mdi-account text-xl sm:text-2xl"
                                ></i>
                            </div>
                            <span
                                class="mt-1 sm:mt-2 text-xs sm:text-sm font-medium"
                                >Профиль</span
                            >
                        </div>

                        <div
                            class="nav-item relative flex flex-col items-center justify-center p-2 sm:p-4 rounded-xl cursor-pointer transition-all duration-200 hover:bg-primary-100"
                            @click="appStore.modalName = 'menu'"
                            :class="{
                                'text-primary-700 bg-primary-100 shadow-sm':
                                    appStore.modalName === 'menu',
                            }"
                            ref="menu"
                        >
                            <div class="nav-icon">
                                <i class="mdi mdi-menu text-xl sm:text-2xl"></i>
                            </div>
                            <span
                                class="mt-1 sm:mt-2 text-xs sm:text-sm font-medium"
                                >Меню</span
                            >
                        </div>
                    </nav>

                    <div class="modal-content flex-1 overflow-y-auto px-6 py-4">
                        <component
                            :is="appStore.modalName + '-component'"
                        ></component>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style lang="sass" scoped>
.modal-overlay
    @apply opacity-0

.modal-menu
    @apply translate-x-full
</style>
