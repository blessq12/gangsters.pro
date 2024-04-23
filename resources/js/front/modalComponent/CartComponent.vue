<script>
import { validate } from "vee-validate";
import { localStore } from "../../stores/localStore";
import { userStore } from "../../stores/userStore";
import { mapStores } from "pinia";
import { object, string } from "yup";
import moment from "moment";

export default {
    mounted(){
        if (this.userStore.authStatus){
            this.formData.name = this.userStore.userData.name;
            this.formData.tel = this.userStore.userData.tel;
            this.noDelForm.name = this.userStore.userData.name;
            this.noDelForm.tel = this.userStore.userData.tel;
        }
    },
    data: () => ({
        moment: moment,
        checkout: false,
        delivery: true,
        payType: "cash",
        schema: object({
            name: string()
                .required("Обязательно")
                .min(3, "Не менее 3 символов")
                .max(255, "Не более 255 символов"),
            tel: string()
                .required("Обязательно")
                .min(18, "Некорректный номер")
                .max(18, "Некорректный номер"),
            street: string()
                .required("Обязательно")
                .min(3, "Не менее 3 символов")
                .max(255, "Не более 255 символов"),
            house: string()
                .required("Обязательно")
                .max(255, "Не более 255 символов"),
            building: string().nullable(),
            staircase: string().nullable(),
            floor: string().nullable(),
            apartment: string().required("Обязательно"),
        }),
        formData: {
            name: null,
            tel: null,
            street: null,
            house: null,
            building: null,
            staircase: null,
            floor: null,
            apartment: null,
        },
        noDelForm: {
            name: null,
            tel: null,
        },
        noDelSchema: object({
            name: string()
                .required("Обязательно")
                .min(3, "Не менее 3 символов")
                .max(255, "Не более 255 символов"),
            tel: string()
                .required("Обязательно")
                .min(18, "Некорректный номер")
                .max(18, "Некорректный номер"),
        }),
        validatorBag: {},
    }),
    computed: {
        ...mapStores(localStore, userStore),
    },
    methods: {
        validate(form) {
            if (form == "delivery") {
                this.schema
                    .validate(this.formData, { abortEarly: false })
                    .then((res) => {
                        this.validatorBag = {};
                        let data = {
                            delivery: true,
                            cart: this.localStore.cart.map((item) => {
                                return {
                                    id: item.id,
                                    name: item.name,
                                    price: item.price,
                                    qty: item.qty,
                                };
                            }),
                            order: res
                        }
                        axios.post('/api/orders/create', data)
                        .then((res) => {console.log(res.data)})
                        .catch((err) => {console.log(err)})
                    })
                    .catch((err) => {
                        this.validatorBag = {};
                        // let et = {};
                        err.inner.forEach((e) => {
                            this.validatorBag[e.path] = e.message;
                            et[e.path] = e.message;
                        });
                        // console.table(et);
                    });
            }
            if (form == "noDelivery") {
                this.noDelSchema
                    .validate(this.noDelForm, { abortEarly: false })
                    .then((res) => {
                        this.validatorBag = {};
                        let data = {
                            delivery: false,
                            cart: this.localStore.cart.map((item) => {
                                return {
                                    id: item.id,
                                    name: item.name,
                                    price: item.price,
                                    qty: item.qty,
                                };
                            }),
                            order: res
                        }
                        axios.post('/api/orders/create', data)
                        .then((res) => {console.log(res.data)})
                        .catch((err) => {console.log(err)})
                    })
                    .catch((err) => {
                        this.validatorBag = {};
                        // let et = {};
                        err.inner.forEach((e) => {
                            this.validatorBag[e.path] = e.message;
                            et[e.path] = e.message;
                        });
                        // console.table(et);
                    });
            }
        },
        createOrder() {
            console.log("create order");
        },
    },
};

// localStore.cartTotal() - return a total cost from cart
</script>

<template>
    <transition
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
    >
        <div v-if="localStore.cart.length">
            <transition
                enter-active-class="animate__animated animate__fadeIn"
                leave-active-class="animate__animated animate__fadeOut"
                mode="out-in"
            >
                <div v-if="!checkout">
                    <transition-group
                        enter-active-class="animate__animated animate__fadeIn"
                        leave-active-class="animate__animated animate__fadeOut"
                        move-class=" move "
                        tag="ul"
                        class="cart-list"
                    >
                        <li v-for="item in localStore.cart" :key="item.id">
                            <div class="fav-card">
                                <div
                                    class="image bg-image rounded"
                                    style="
                                        background: url('http://via.placeholder.com/256x256');
                                    "
                                ></div>
                                <div class="d-blo">
                                    <div class="content">
                                        <span>{{ item.name }}</span>
                                        <p class="mb-0">{{ item.consist }}</p>
                                    </div>
                                    <div class="footer">
                                        <div class="qty">
                                            <button
                                                type="button"
                                                class="btn"
                                                @click="
                                                    localStore.manageQty(
                                                        false,
                                                        item
                                                    )
                                                "
                                            >
                                                <i class="fa fa-minus"></i>
                                            </button>
                                            <span>{{item.qty ? item.qty : "Нет"}}</span>
                                            <button type="button" class="btn" @click=" localStore.manageQty(true,item)">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            class="btn btn-danger rounded"
                                            @click="
                                                localStore.manageStore(
                                                    'cart',
                                                    item
                                                )
                                            "
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </transition-group>
                    <div class="cart-footer border-top pt-4 pb-2">
                        <ul class="list-unstyled">
                            <li>
                                <b>Стоимость: </b>
                                <transition
                                    enter-active-class="animate__animated animate__fadeIn"
                                    leave-active-class="animate__animated animate__fadeOut"
                                    mode="out-in"
                                >
                                    <span
                                        v-if="localStore.cartTotal"
                                        :key="localStore.cartTotal"
                                        >{{
                                            localStore.cartTotal + " рублей"
                                        }}</span
                                    >
                                </transition>
                            </li>
                            <li>
                                <b>Наборов: </b>
                                <transition
                                    enter-active-class="animate__animated animate__fadeIn"
                                    leave-active-class="animate__animated animate__fadeOut"
                                    mode="out-in"
                                >
                                    <span
                                        v-if="localStore.cartQty"
                                        :key="localStore.cartQty"
                                        >{{ localStore.cartQty + " шт" }}</span
                                    >
                                </transition>
                            </li>
                        </ul>
                    </div>
                    <button
                        class="btn rounded btn-secondary"
                        @click="checkout = !checkout"
                    >
                        Оформление
                    </button>
                    <button
                        class="btn rounded btn-danger mx-2"
                        @click="localStore.clearStore('cart')"
                    >
                        <i class="fa fa-trash"></i> Очистить корзину
                    </button>
                </div>
                <div v-else>
                    <button
                        type="btn"
                        aria-label="Назад"
                        class="btn p-0 mb-4"
                        @click="checkout = !checkout"
                    >
                        <i
                            class="fa fa-arrow-left"
                            style="margin-right: 6px"
                        ></i>
                        Назад в корзину
                    </button>

                    <div class="btn-group d-block mb-4">
                        <button
                            type="button"
                            :class="`btn ${delivery ? 'active' : ''}`"
                            @click="delivery = true"
                        >
                            Доставка
                        </button>
                        <button
                            type="button"
                            :class="`btn ${!delivery ? 'active' : ''}`"
                            @click="delivery = false"
                        >
                            Самовывоз
                        </button>
                    </div>

                    <transition
                        enter-active-class="animate__animated animate__fadeIn"
                        leave-active-class="animate__animated animate__fadeOut"
                        mode="out-in"
                    >
                        <div v-if="delivery">
                            <form ref="delivery">
                                <div class="form-group">
                                    <div class="row row-cols-1 g-2">
                                        <div class="col">
                                            <div
                                                class="d-flex"
                                                style="white-space: nowrap"
                                            >
                                                <label for="name"
                                                    >Ваше Имя</label
                                                >
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="name"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="name"
                                                id="name"
                                                class="form-control"
                                                v-model="formData.name"
                                            />
                                        </div>
                                        <div class="col">
                                            <div
                                                class="d-flex"
                                                style="white-space: nowrap"
                                            >
                                                <label for="tel"
                                                    >Номер телефона</label
                                                >
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="tel"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="tel"
                                                id="tel"
                                                class="form-control"
                                                v-maska
                                                data-maska="+7 (###) ###-##-##"
                                                v-model="formData.tel"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <div
                                                class="d-flex"
                                                style="white-space: nowrap"
                                            >
                                                <label for="name">Улица</label>
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="street"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="street"
                                                id="street"
                                                class="form-control"
                                                v-model="formData.street"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row row-cols-2 g-2">
                                        <div class="col">
                                            <div
                                                class="d-flex"
                                                style="white-space: nowrap"
                                            >
                                                <label for="name">Дом</label>
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="house"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="house"
                                                id="house"
                                                class="form-control"
                                                v-model="formData.house"
                                            />
                                        </div>
                                        <div class="col">
                                            <div
                                                class="d-flex"
                                                style="white-space: nowrap"
                                            >
                                                <label for="name"
                                                    >Строение</label
                                                >
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="building"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="building"
                                                id="building"
                                                class="form-control"
                                                v-model="formData.building"
                                            />
                                        </div>
                                        <div class="col">
                                            <div
                                                class="d-flex"
                                                style="white-space: nowrap"
                                            >
                                                <label for="name"
                                                    >Подъезд</label
                                                >
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="staircase"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="staircase"
                                                id="staircase"
                                                class="form-control"
                                                v-model="formData.staircase"
                                                v-maska
                                                data-maska="#####"
                                            />
                                        </div>
                                        <div class="col">
                                            <div
                                                class="d-flex"
                                                style="white-space: nowrap"
                                            >
                                                <label for="name">Этаж</label>
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="floor"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="floor"
                                                id="floor"
                                                class="form-control"
                                                v-model="formData.floor"
                                                v-maska
                                                data-maska="#####"
                                            />
                                        </div>
                                        <div class="col">
                                            <div
                                                class="d-flex"
                                                style="white-space: nowrap"
                                            >
                                                <label for="name"
                                                    >Квартира</label
                                                >
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="apartment"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="apartment"
                                                id="apartment"
                                                class="form-control"
                                                v-model="formData.apartment"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <p class="mb-2">Способ оплаты</p>
                                        <div class="col">
                                            <div class="btn-group">
                                                <button
                                                    type="button"
                                                    :class="`btn ${
                                                        payType == 'cash'
                                                            ? 'active'
                                                            : ''
                                                    }`"
                                                    @click="payType = 'cash'"
                                                >
                                                    Наличные
                                                </button>
                                                <button
                                                    type="button"
                                                    :class="`btn ${
                                                        payType == 'card'
                                                            ? 'active'
                                                            : ''
                                                    }`"
                                                    @click="payType = 'card'"
                                                >
                                                    Картой курьеру
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div v-else>
                            <form ref="noDelivery">
                                <div class="row mb-4">
                                    <div class="col">
                                        <div class="form-group">
                                            <div class="d-flex">
                                                <label for="name">Имя</label>
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="name"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="name"
                                                id="name"
                                                class="form-control"
                                                v-model="noDelForm.name"
                                            />
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <div class="d-flex">
                                                <label for="tel">Телефон</label>
                                                <error-label
                                                    :errorBag="validatorBag"
                                                    name="tel"
                                                ></error-label>
                                            </div>
                                            <input
                                                type="text"
                                                name="tel"
                                                id="tel"
                                                class="form-control"
                                                v-model="noDelForm.tel"
                                                v-maska
                                                data-maska="+7 (###) ###-##-##"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div style="position: relative; overflow: hidden">
                                <a
                                    href="https://yandex.ru/maps/org/gangster_s_sushi/82888444717/?utm_medium=mapframe&utm_source=maps"
                                    style="
                                        color: #eee;
                                        font-size: 12px;
                                        position: absolute;
                                        top: 0px;
                                    "
                                    >Gangster's sushi</a
                                ><a
                                    href="https://yandex.ru/maps/67/tomsk/category/food_and_lunch_delivery/184108273/?utm_medium=mapframe&utm_source=maps"
                                    style="
                                        color: #eee;
                                        font-size: 12px;
                                        position: absolute;
                                        top: 14px;
                                    "
                                    >Доставка еды и обедов в Томске</a
                                ><iframe
                                    src="https://yandex.ru/map-widget/v1/?ll=84.986330%2C56.513423&mode=search&oid=82888444717&ol=biz&z=16.61"
                                    width="100%"
                                    height="400"
                                    frameborder="0"
                                    allowfullscreen="true"
                                    style="position: relative"
                                ></iframe>
                            </div>
                        </div>
                    </transition>

                    <button
                        type="button"
                        class="btn rounded btn-warning mt-4"
                        @click="
                            delivery
                                ? validate('delivery')
                                : validate('noDelivery')
                        "
                    >
                        Заказать
                    </button>
                </div>
            </transition>
        </div>
        <div v-else>
            <div class="row">
                <div class="col text-center">
                    <p class="mb-0">Пусто</p>
                </div>
            </div>
        </div>
    </transition>
</template>

<style lang="sass" scoped>
.btn-group
    button
        border-color: #ffc107
        &.active
            background: #ffc107
.move
    position: absolute
</style>
