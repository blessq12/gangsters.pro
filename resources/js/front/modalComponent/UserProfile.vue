<script>
import { userStore } from "@/stores/userStore";
import { mapStores } from "pinia";
import { useToast } from "vue-toastification";
import { object, string } from "yup";

const toast = useToast();

export default {
    setup() {
        //
    },
    mounted() {
        //
    },
    computed: {
        ...mapStores(userStore),
    },
    data() {
        return {
            activeTab: "personal_data",
            editMode: false,
            orders: [],
            coins: 0,
            loading: false,
            formData: {
                name: "",
                dob: "",
                tel: "",
            },
            schema: object({
                name: string().required(),
                dob: string()
                    .required()
                    .matches(
                        /^\d{2}-\d{2}-\d{4}$/,
                        "Дата должна быть в формате ДД-ММ-ГГГГ"
                    )
                    .test(
                        "is-today-or-past",
                        "Дата не может быть больше сегодняшнего дня",
                        (value) => {
                            if (!value) return true;
                            const dateParts = value.split("-");
                            const date = new Date(
                                `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`
                            );
                            const today = new Date();
                            return (
                                date <= today &&
                                today.getFullYear() - date.getFullYear() >= 16
                            );
                        }
                    ),
                tel: string().required(),
            }),
            errors: [],
        };
    },
    methods: {
        async getMyOrders() {
            try {
                const response = await axios.get("/api/orders/get-my-orders");
                this.orders = response.data;
            } catch (error) {
                console.error("Error fetching orders:", error);
            }
        },
        async getMyCoins() {
            try {
                const response = await axios.get("/api/orders/get-my-coins");
                this.coins = response.data;
            } catch (error) {
                console.error("Error fetching coins:", error);
            }
        },
        async getUserData() {
            try {
                const response = await axios.get("/api/auth/get-user-data");
                this.formData.name = response.data.user.name;
                this.formData.dob = response.data.user.dob;
                this.formData.tel = response.data.user.tel;
            } catch (error) {
                console.error("Error fetching user data:", error);
            }
        },
        update() {
            this.schema
                .validate(this.formData, { abortEarly: false })
                .then((res) => {
                    this.errors = [];
                    axios
                        .patch("/api/auth/update-user", this.formData)
                        .then((res) => {
                            this.editMode = false;
                            this.userStore.userData = res.data.user;
                            toast.success("Данные успешно обновлены");
                        })
                        .catch((err) => {
                            toast.error("Ошибка при обновлении данных");
                        });
                })
                .catch((err) => {
                    this.errors = [];
                    err.inner.forEach((el) => {
                        this.errors[el.path] = el.message;
                    });
                });
        },
    },
    watch: {
        activeTab(val) {
            if (val == "orders") {
                this.getMyOrders();
            }
            if (val == "gcoins") {
                this.getMyCoins();
            }
        },
        editMode(val) {
            if (val) {
                this.getUserData();
            }
        },
        errors: {
            handler(val) {
                let inputs = this.$refs.form.querySelectorAll("input");
                inputs.forEach((input) => {
                    if (this.errors[input.id]) {
                        input.classList.add("border-red-500");
                        input.classList.remove("border-gray-300");
                    } else {
                        input.classList.remove("border-red-500");
                        input.classList.add("border-gray-300");
                    }
                });
            },
            deep: true,
        },
    },
};
</script>

<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h4 class="text-xl font-semibold">
                    Привет, {{ userStore.userData.name }}
                </h4>
                <button
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none"
                    @click="userStore.logout"
                >
                    Выйти
                    <i class="mdi mdi-logout ml-2"></i>
                </button>
            </div>
        </div>

        <div class="p-4">
            <div class="space-y-6">
                <div class="flex flex-nowrap overflow-x-auto scrollbar-hide">
                    <button
                        v-for="(tab, name) in {
                            personal_data: 'Личные данные',
                            orders: 'Заказы',
                            gcoins: 'G-Coins',
                            addresses: 'Мои адреса',
                        }"
                        :key="name"
                        :class="`px-4 py-2 text-sm font-medium rounded-lg mr-2 whitespace-nowrap ${
                            activeTab === name
                                ? 'bg-gray-600 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        } ${
                            name === 'addresses'
                                ? 'opacity-50 cursor-not-allowed'
                                : ''
                        }`"
                        @click="activeTab = name"
                        :disabled="name === 'addresses'"
                    >
                        <span class="flex items-center">
                            <i
                                :class="`mdi ${
                                    name === 'personal_data'
                                        ? 'mdi-account'
                                        : name === 'orders'
                                        ? 'mdi-shopping'
                                        : name === 'gcoins'
                                        ? 'mdi-coin'
                                        : 'mdi-map-marker'
                                } mr-2`"
                            ></i>
                            {{ tab }}
                        </span>
                    </button>
                </div>

                <div
                    v-if="activeTab === 'personal_data'"
                    class="bg-white rounded-lg border border-gray-200"
                >
                    <div class="p-4">
                        <div v-if="!editMode">
                            <div class="flex items-center justify-between mb-4">
                                <h5 class="text-lg font-semibold">
                                    Мои данные
                                </h5>
                                <button
                                    class="text-sm text-gray-600 hover:text-gray-900 flex items-center"
                                    @click="editMode = true"
                                >
                                    <i class="mdi mdi-pencil mr-1"></i>
                                    Изменить данные
                                </button>
                            </div>
                            <div class="space-y-3">
                                <p class="flex items-center">
                                    <i
                                        class="mdi mdi-account text-gray-400 mr-2"
                                    ></i>
                                    <span class="font-medium mr-2">Имя:</span>
                                    {{ userStore.userData.name }}
                                </p>
                                <p class="flex items-center">
                                    <i
                                        class="mdi mdi-calendar text-gray-400 mr-2"
                                    ></i>
                                    <span class="font-medium mr-2"
                                        >Дата рождения:</span
                                    >
                                    {{ userStore.userData.dob || "Не указано" }}
                                </p>
                                <p class="flex items-center">
                                    <i
                                        class="mdi mdi-phone text-gray-400 mr-2"
                                    ></i>
                                    <span class="font-medium mr-2"
                                        >Телефон:</span
                                    >
                                    {{ userStore.userData.tel }}
                                </p>
                                <p class="flex items-center">
                                    <i
                                        class="mdi mdi-email text-gray-400 mr-2"
                                    ></i>
                                    <span class="font-medium mr-2">Email:</span>
                                    {{ userStore.userData.email }}
                                </p>
                            </div>
                        </div>
                        <div v-else>
                            <h5 class="text-lg font-semibold mb-4">
                                Изменение данных
                            </h5>
                            <form
                                @submit.prevent="update"
                                ref="form"
                                class="space-y-4"
                            >
                                <div class="space-y-2">
                                    <label
                                        for="name"
                                        class="block text-sm font-medium text-gray-700"
                                        >Имя</label
                                    >
                                    <div class="relative rounded-lg">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <i
                                                class="mdi mdi-account text-gray-400"
                                            ></i>
                                        </div>
                                        <input
                                            type="text"
                                            id="name"
                                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                            v-model="formData.name"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        for="dob"
                                        class="block text-sm font-medium text-gray-700"
                                        >Дата рождения</label
                                    >
                                    <div class="relative rounded-lg">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <i
                                                class="mdi mdi-calendar text-gray-400"
                                            ></i>
                                        </div>
                                        <input
                                            type="text"
                                            id="dob"
                                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                            v-maska
                                            data-maska="##-##-####"
                                            v-model="formData.dob"
                                            placeholder="01-01-1990"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        for="tel"
                                        class="block text-sm font-medium text-gray-700"
                                        >Телефон</label
                                    >
                                    <div class="relative rounded-lg">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <i
                                                class="mdi mdi-phone text-gray-400"
                                            ></i>
                                        </div>
                                        <input
                                            type="tel"
                                            id="tel"
                                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                            v-maska
                                            data-maska="+7 (###) ###-##-##"
                                            v-model="formData.tel"
                                        />
                                    </div>
                                </div>

                                <div class="flex items-center space-x-4 pt-4">
                                    <button
                                        type="submit"
                                        class="flex-1 flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                    >
                                        <i
                                            class="mdi mdi-content-save mr-2"
                                        ></i>
                                        Сохранить
                                    </button>
                                    <button
                                        type="button"
                                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 focus:outline-none"
                                        @click="editMode = false"
                                    >
                                        Отмена
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div
                    v-if="activeTab === 'orders'"
                    class="bg-white rounded-lg border border-gray-200"
                >
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-lg font-semibold">Мои заказы</h5>
                            <button
                                class="text-sm text-gray-600 p-2 rounded-lg bg-blue-100 hover:text-gray-900 flex items-center justify-center hover:bg-blue-200 cursor-pointer"
                                @click=""
                            >
                                <i class="mdi mdi-refresh mr-1"></i>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div v-if="orders.length == 0">
                                <p class="text-gray-600">
                                    У вас пока нет заказов
                                </p>
                            </div>
                            <div
                                v-else
                                v-for="order in orders"
                                :key="order.id"
                                class="bg-gray-100 p-4 rounded-lg"
                            >
                                <div class="flex items-center justify-between">
                                    <p class="text-gray-600">
                                        Заказ №{{ order.id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-if="userStore.userData.dob == null"
                    class="bg-gray-50 rounded-lg p-4"
                >
                    <div class="flex items-start space-x-4">
                        <i
                            class="mdi mdi-information text-primary-600 text-xl"
                        ></i>
                        <div>
                            <h5 class="text-lg font-semibold mb-2">
                                Укажи дату рождения
                            </h5>
                            <p class="text-gray-600">
                                Это необходимо для корректной работы системы
                                кешбека. Чтобы мы могли отправлять акции и
                                скидки именно тебе.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
