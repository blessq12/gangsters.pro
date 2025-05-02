<script>
import { mapStores } from "pinia";
import { appStore } from "../../stores/appStorage";
import { userStore } from "../../stores/userStore";
export default {
    computed: {
        ...mapStores(userStore, appStore),
    },
    data: () => ({
        links: [],
        company: null,
    }),
    mounted() {
        this.getLinks();
        this.getCompany();
    },
    methods: {
        async getLinks() {
            const response = await axios.get("/api/get-routes");
            this.links = response.data;
        },
        async getCompany() {
            const response = await axios.get("/api/get-company");
            this.company = response.data;
        },
    },
};
</script>

<template>
    <div class="w-full mb-6" v-if="company">
        <h5
            class="text-xl font-semibold mb-6 pb-2 border-b border-gray-200 dark:border-gray-700"
        >
            Контакты
        </h5>
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
                <h2
                    class="text-2xl font-bold text-gray-900 dark:text-white mb-2"
                >
                    {{ company.name }} - {{ company.city }}
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    {{ company.description }}
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <a
                    :href="'tel:' + company.phone"
                    class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-shadow"
                >
                    <i
                        class="fa fa-phone text-2xl text-gray-600 dark:text-gray-300 mr-4"
                    ></i>
                    <span class="text-gray-800 dark:text-gray-200">{{
                        company.phone
                    }}</span>
                </a>

                <a
                    href="https://yandex.ru/maps/67/tomsk/?ll=84.986330%2C56.513423&mode=routes&rtext=~56.513356%2C84.986301&rtt=auto&ruri=~ymapsbm1%3A%2F%2Forg%3Foid%3D82888444717&z=16.61"
                    class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-shadow"
                    title="Построить маршрут на Яндекс.Картах"
                >
                    <i
                        class="fa fa-map-marker text-2xl text-gray-600 dark:text-gray-300 mr-4"
                    ></i>
                    <span class="text-gray-800 dark:text-gray-200">
                        {{ company.city }}, {{ company.street }},
                        {{ company.house }}
                    </span>
                </a>
            </div>
        </div>
    </div>
    <div class="w-full animate-pulse" v-else>
        <h5 class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-6"></h5>
        <div class="space-y-6">
            <div class="h-32 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
                <div class="h-16 bg-gray-200 dark:bg-gray-700 rounded-xl"></div>
            </div>
        </div>
    </div>
    <div class="w-full space-y-8 mb-24">
        <div class="w-full" v-if="links.length > 0">
            <h5
                class="text-xl font-semibold mb-4 pb-2 border-b border-gray-200 dark:border-gray-700"
            >
                Навигация по сайту
            </h5>
            <div class="grid gap-3">
                <a
                    :href="link.url"
                    v-for="link in links"
                    class="block p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
                >
                    <span class="text-gray-800">{{ link.name }}</span>
                </a>
            </div>
        </div>
        <div class="w-full animate-pulse" v-else>
            <h5
                class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-4"
            ></h5>
            <div class="space-y-3">
                <div
                    v-for="i in 3"
                    class="h-14 bg-gray-200 dark:bg-gray-700 rounded"
                ></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Удалены все стили, так как теперь используется Tailwind CSS */
</style>
