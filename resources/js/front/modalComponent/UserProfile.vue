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
                        input.classList.add("is-invalid");
                    } else {
                        input.classList.remove("is-invalid");
                    }
                });
            },
            deep: true,
        },
    },
};
</script>

<template>
    <div class="header p-2">
        <div class="row align-items-center">
            <div class="col">
                <h4 class="mb-0">Привет, {{ userStore.userData.name }}</h4>
            </div>
            <div class="col text-end">
                <button class="btn-exit" @click="userStore.logout">
                    Выйти
                    <i class="fa fa-sign-out ms-1"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="content p-2">
        <div class="row mb-4">
            <div class="col-12 mb-2 mb-lg-4">
                <ul class="btn-group">
                    <button
                        class="btn list-group-item"
                        :class="{ active: activeTab == 'personal_data' }"
                        @click="activeTab = 'personal_data'"
                    >
                        Личные данные
                    </button>
                    <button
                        class="btn list-group-item"
                        :class="{ active: activeTab == 'orders' }"
                        @click="activeTab = 'orders'"
                    >
                        Заказы
                    </button>
                    <button
                        class="btn list-group-item"
                        :class="{ active: activeTab == 'gcoins' }"
                        @click="activeTab = 'gcoins'"
                    >
                        G-Coins
                    </button>
                    <button class="btn list-group-item" disabled>
                        Мои адреса
                    </button>
                </ul>
            </div>
            <div class="col-12">
                <div class="card" v-if="activeTab == 'personal_data'">
                    <div class="card-body" v-if="!editMode">
                        <h5 class="card-title mb-3">Мои данные</h5>
                        <p class="card-text">
                            <b>Имя:</b> {{ userStore.userData.name }}
                        </p>
                        <p class="card-text">
                            <b>Дата рождения:</b>
                            {{
                                userStore.userData.dob
                                    ? userStore.userData.dob
                                    : "Не указано"
                            }}
                        </p>
                        <p class="card-text">
                            <b>Телефон:</b> {{ userStore.userData.tel }}
                        </p>
                        <p class="card-text">
                            <b>Email:</b> {{ userStore.userData.email }}
                        </p>
                        <button
                            class="btn-exit fw-light"
                            @click="editMode = true"
                        >
                            Изменить данные
                        </button>
                    </div>
                    <div class="card-body" v-else>
                        <h5 class="card-title mb-3">Изменение данных</h5>
                        <form @submit.prevent="update" ref="form">
                            <div class="mb-3">
                                <label for="name" class="form-label">Имя</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="name"
                                    v-model="formData.name"
                                />
                            </div>
                            <div class="mb-3">
                                <label for="dob" class="form-label"
                                    >Дата рождения</label
                                >
                                <input
                                    type="text"
                                    class="form-control"
                                    id="dob"
                                    v-maska
                                    data-maska="##-##-####"
                                    v-model="formData.dob"
                                    placeholder="01-01-1990"
                                />
                            </div>
                            <div class="mb-3">
                                <label for="tel" class="form-label"
                                    >Телефон</label
                                >
                                <input
                                    type="tel"
                                    class="form-control"
                                    id="tel"
                                    v-maska
                                    data-maska="+7 (###) ###-##-##"
                                    v-model="formData.tel"
                                />
                            </div>
                            <div class="mb-3">
                                <button class="btn-exit fw-light" type="submit">
                                    Сохранить
                                </button>
                                <button
                                    class="btn-exit fw-light ms-2"
                                    type="button"
                                    @click="editMode = false"
                                >
                                    Отменить
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card" v-if="activeTab == 'orders'">
                    <div class="card-body" v-if="orders.length > 0">
                        <h5 class="card-title mb-3">Мои заказы</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Дата</th>
                                        <th>Статус</th>
                                        <th>Сумма</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in orders">
                                        <td>{{ order.id }}</td>
                                        <td>{{ order.created }}</td>
                                        <td>{{ order.status_text }}</td>
                                        <td>{{ order.total }} руб.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-body" v-else>
                        <h5 class="card-title mb-3">Мои заказы</h5>
                        <p class="card-text">У вас нет заказов</p>
                    </div>
                </div>
                <div class="card" v-if="activeTab == 'gcoins'">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Мои G-Coins</h5>
                        <p class="card-text">
                            У вас <b>{{ coins }}</b> G-Coins
                        </p>
                        <p class="card-text text-muted mb-4">
                            У нас есть система кешбека, которая позволяет вам
                            зарабатывать деньги с каждой покупки. Каждый раз,
                            когда вы совершаете покупку, определенный процент от
                            суммы будет начислен на ваш кешбек-аккаунт. Вы
                            можете использовать накопленный кешбек для частичной
                            оплаты вашего следующего заказа, что делает ваши
                            покупки еще более выгодными. Не упустите возможность
                            сэкономить!
                        </p>
                        <a href="/loyalty" class="btn-exit fw-light"
                            >Полная информация о кешбеке</a
                        >
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-if="userStore.userData.dob == null">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="background: #f5f5f5">
                        <h5 class="card-title mb-3">Укажи дату рождения</h5>
                        <p class="card-text">
                            Это необходимо для корректной работы системы
                            кешбека. Чтобы мы могли отправлять акции и скидки
                            именно тебе.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer"></div>
</template>

<style scoped lang="sass">
.btn-exit
    background: #dedede
    color: #fff
    border-radius: 10px
    padding: 10px 16px
    font-size: 16px
    font-weight: 500
    border: none
    outline: none
    cursor: pointer
.btn-group
    overflow-x: scroll !important
    white-space: nowrap !important
    width: 100% !important
    @media (min-width: 992px)
        width: fit-content !important
    padding: 0
    &::-webkit-scrollbar
        display: none
.list-group-item
    cursor: pointer
    white-space: nowrap
    &.active
        background: #dedede
        color: #fff
        border-color: #dedede
</style>
