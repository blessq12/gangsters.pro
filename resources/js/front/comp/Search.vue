<script>
import { localStore } from "../../stores/localStore";
import { appStore } from "../../stores/appStorage";
import { mapStores } from "pinia";
import gsap from "gsap";

export default {
    computed: {
        ...mapStores(localStore, appStore),
    },
    data() {
        return {
            search: "",
            results: [],
            isLoading: false,
            isModalOpen: false,
        };
    },

    watch: {
        search: {
            handler: "searchItems",
            immediate: false,
        },
    },

    methods: {
        async searchItems() {
            if (this.search.length < 2) {
                this.results = [];
                return;
            }
            this.isLoading = true;
            axios
                .get(`/api/goods/search?q=${this.search}`)
                .then((res) => {
                    console.log(res.data);
                    this.results = res.data;
                })
                .catch((err) => {
                    console.error(err);
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        openModal() {
            this.isModalOpen = true;
            setTimeout(() => {
                document.querySelector("#searchInput")?.focus();
            }, 100);
        },

        closeModal() {
            this.isModalOpen = false;
            this.search = "";
            this.results = [];
        },
    },
    watch: {
        isModalOpen(val) {
            if (val) {
                document.body.style.overflow = "hidden";
            } else {
                document.body.style.overflow = "auto";
            }
        },
        search(val) {
            if (val.length < 2) {
                this.results = [];
            }
            this.searchItems();
        },
    },
};
</script>

<template>
    <!-- Кнопка открытия поиска -->
    <button
        @click="openModal"
        class="flex items-center space-x-2 text-gray-600 hover:text-gray-900"
    >
        <i class="mdi mdi-magnify text-xl"></i>
        <span>Поиск</span>
    </button>

    <div
        v-if="isModalOpen"
        @click="closeModal"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-start justify-center pt-20 z-100"
    >
        <div
            @click.stop
            class="bg-white w-full max-w-2xl mx-4 rounded-lg shadow-xl"
        >
            <div class="p-4 border-b relative">
                <i
                    class="mdi mdi-magnify absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 text-xl"
                ></i>
                <input
                    id="searchInput"
                    v-model="search"
                    type="text"
                    class="w-full pl-12 pr-4 py-2 outline-none text-lg"
                    placeholder="Введите название или ингридиент"
                />
                <button
                    @click="closeModal"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                    <i class="mdi mdi-close text-xl"></i>
                </button>
            </div>

            <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
                <div
                    v-if="isLoading && results.length === 0"
                    class="p-8 text-center text-gray-500"
                >
                    Ищем...
                </div>

                <div
                    v-else-if="results.length === 0 && search.length >= 2"
                    class="p-8 text-center text-gray-500"
                >
                    Ничего не найдено {{ search }}
                </div>

                <div
                    v-for="item in results"
                    :key="item.id"
                    class="p-4 hover:bg-gray-50 cursor-pointer flex items-center border-b"
                    @click="appStore.currentAdditional = item"
                >
                    <img
                        v-if="item.images"
                        :src="item.images[0]"
                        class="w-16 h-16 object-cover rounded mr-4"
                        :alt="item.name"
                    />
                    <div class="flex-1 min-w-0">
                        <div class="font-medium truncate">{{ item.name }}</div>
                        <div class="text-sm text-gray-500 truncate">
                            {{ item.consist }}
                        </div>
                        <div class="text-sm font-medium mt-1">
                            {{ item.price }} ₽
                        </div>
                    </div>
                    <div
                        class="flex-1 min-w-0 flex items-center justify-end gap-2"
                    >
                        <button
                            v-if="!localStore.checkExist('cart', item)"
                            class="btn-add-to-cart"
                            @click="localStore.manageStore('cart', item)"
                        >
                            <i class="mdi mdi-cart-plus text-2xl"></i>
                        </button>
                        <button
                            v-if="localStore.checkExist('cart', item)"
                            class="btn-add-to-cart"
                            @click="localStore.manageStore('cart', item)"
                        >
                            <i
                                class="mdi mdi-cart-plus text-2xl text-green-500"
                            ></i>
                        </button>
                        <button
                            v-if="!localStore.checkExist('fav', item)"
                            class="btn-add-to-cart"
                            @click="localStore.manageStore('fav', item)"
                        >
                            <i class="mdi mdi-heart text-2xl"></i>
                        </button>
                        <button
                            v-if="localStore.checkExist('fav', item)"
                            class="btn-add-to-cart"
                            @click="localStore.manageStore('fav', item)"
                        >
                            <i class="mdi mdi-heart text-2xl text-red-500"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
