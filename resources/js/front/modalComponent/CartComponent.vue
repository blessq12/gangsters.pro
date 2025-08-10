<script>
import gsap from "gsap";
import moment from "moment";
import { mapStores } from "pinia";
import { useToast } from "vue-toastification";
import { object, string } from "yup";
import { appStore } from "../../stores/appStorage";
import { localStore } from "../../stores/localStore";
import { userStore } from "../../stores/userStore";

export default {
    created() {},
    mounted() {
        this.initializeFormData();
        this.initializeAnimations();
    },
    computed: {
        ...mapStores(localStore, userStore, appStore),
    },
    data() {
        return {
            toast: useToast(),
            moment,
            checkout: false,
            delivery: true,
            orderCreated: false,
            order: null,
            schema: this.createSchema(),
            formData: this.createFormData(),
            noDelForm: this.createNoDelForm(),
            noDelSchema: this.createNoDelSchema(),
            validatorBag: {},
            checkPerformed: false,
        };
    },
    methods: {
        initializeAnimations() {
            if (this.localStore.cart.length) {
                gsap.from(this.$refs.cartList, {
                    y: 20,
                    opacity: 0,
                    duration: 0.5,
                    stagger: 0.1,
                    ease: "power3.out",
                });
            }
        },
        initializeFormData() {
            if (this.userStore.authStatus) {
                const { name, tel } = this.userStore.userData;
                this.formData.name = name;
                this.formData.tel = tel;
                this.noDelForm.name = name;
                this.noDelForm.tel = tel;
            } else {
                this.formData.saveAddress = false;
            }

            if (
                this.userStore.authStatus &&
                this.userStore.userData.addresses.length > 0
            ) {
                this.formData.street =
                    this.userStore.userData.addresses[0].street;
                this.formData.house =
                    this.userStore.userData.addresses[0].house;
                this.formData.building =
                    this.userStore.userData.addresses[0].building;
                this.formData.staircase =
                    this.userStore.userData.addresses[0].staircase;
                this.formData.floor =
                    this.userStore.userData.addresses[0].floor;
                this.formData.apartment =
                    this.userStore.userData.addresses[0].apartment;

                this.formData.saveAddress = false;
            }
        },
        createSchema() {
            return object({
                name: string()
                    .required("Имя обязательно")
                    .min(3, "Не менее 3 символов")
                    .max(255, "Не более 255 символов"),
                tel: string()
                    .required("Телефон обязателен")
                    .min(18, "Некорректный номер")
                    .max(18, "Некорректный номер"),
                street: string()
                    .required("Улица обязательна")
                    .min(3, "Не менее 3 символов")
                    .max(255, "Не более 255 символов"),
                house: string()
                    .required("Дом обязателен")
                    .max(255, "Не более 255 символов"),
                building: string().nullable(),
                staircase: string().nullable(),
                floor: string().nullable(),
                apartment: string().required("Квартира обязательна"),
                payType: string().required("Тип оплаты обязателен"),
            });
        },
        createFormData() {
            return {
                name: null,
                tel: null,
                street: null,
                house: null,
                building: null,
                staircase: null,
                floor: null,
                apartment: null,
                personQty: 1,
                comment: null,
                payType: "cash",
                saveAddress: true,
            };
        },
        createNoDelForm() {
            return {
                name: "",
                tel: "",
                personQty: 1,
                comment: null,
                payType: "cash",
            };
        },
        createNoDelSchema() {
            return object({
                name: string()
                    .required("Обязательно")
                    .min(3, "Не менее 3 символов")
                    .max(255, "Не более 255 символов"),
                tel: string()
                    .required("Обязательно")
                    .min(18, "Некорректный номер")
                    .max(18, "Некорректный номер"),
                payType: string().required("Тип оплаты обязателен"),
            });
        },
        validate(form) {
            const schema = form === "delivery" ? this.schema : this.noDelSchema;
            const formData =
                form === "delivery" ? this.formData : this.noDelForm;

            console.log("=== VALIDATION DEBUG ===");
            console.log("Form type:", form);
            console.log("Schema:", schema);
            console.log("FormData before validation:", formData);
            console.log("payType before validation:", formData.payType);

            schema
                .validate(formData, { abortEarly: false })
                .then((res) => {
                    console.log("Validation result:", res);
                    console.log("payType after validation:", res.payType);
                    this.updateInputs();
                    this.validatorBag = {};
                    this.createOrder(res, form === "delivery");
                })
                .catch((err) => {
                    console.log("Validation error:", err);
                    this.validatorBag = {};
                    this.toast.error("Заполните необходимые поля");
                    err.inner.forEach((e) => {
                        this.validatorBag[e.path] = e.message;
                    });
                    this.updateInputs();
                });
        },
        createOrder(data, delivery) {
            // Логируем что отправляем на бэкенд
            console.log("=== ORDER CREATION DEBUG ===");
            console.log("Form data:", this.formData);
            console.log("Validated data:", data);
            console.log("Delivery:", delivery);

            const req = {
                delivery,
                cart: this.localStore.cart.map(
                    ({ id, name, price, qty, sku }) => ({
                        id,
                        name,
                        price,
                        qty,
                        sku,
                    })
                ),
                order: data,
            };

            console.log("Final request:", req);
            console.log("=== END DEBUG ===");
            this.localStore
                .createOrder(req)
                .then(() => {
                    this.orderCreated = true;
                    this.userStore.loadStore();
                })
                .catch(console.log);
        },
        updateInputs() {
            let inputs = document.querySelectorAll(".form-control");
            inputs.forEach((input) => {
                if (this.validatorBag[input.name]) {
                    input.classList.add("border-red-500");
                } else {
                    input.classList.remove("border-red-500");
                }
            });
        },
    },
    watch: {
        validatorBag: {
            handler(val) {
                console.log(val);
            },
            deep: true,
        },
        orderCreated(val) {
            if (val) {
                this.localStore.clearStore("cart");
                this.checkout = false;
                setTimeout(() => {
                    this.appStore.modal = false;
                    this.appStore.modalName = null;
                    this.orderCreated = false;
                }, 6000);
            }
        },
    },
};
</script>

<template>
    <div class="cart-wrapper py-6 rounded-2xl">
        <transition
            enter-active-class="animate__animated animate__fadeIn"
            leave-active-class="animate__animated animate__fadeOut"
            mode="out-in"
        >
            <div v-if="!orderCreated" class="space-y-6">
                <div v-if="localStore.cart.length">
                    <div v-if="!checkout" class="space-y-6">
                        <transition-group
                            tag="ul"
                            class="cart-list space-y-4"
                            enter-active-class="animate__animated animate__fadeInUp"
                            leave-active-class="animate__animated animate__fadeOutDown"
                            move-class="transition-all duration-300"
                        >
                            <li
                                v-for="item in localStore.cart"
                                :key="item.id"
                                class="bg-white rounded-xl shadow-md p-4"
                                ref="cartList"
                            >
                                <ProductComponentSmall
                                    :product="item"
                                    :is-favorite="false"
                                />
                            </li>
                        </transition-group>

                        <div
                            class="bg-white rounded-xl shadow-md p-5 space-y-3"
                        >
                            <div
                                class="flex justify-between items-center text-gray-700"
                            >
                                <span class="text-lg font-medium"
                                    >Стоимость:</span
                                >
                                <span
                                    class="text-xl font-bold text-primary-600"
                                >
                                    {{ localStore.cartTotal }} ₽
                                </span>
                            </div>
                            <div
                                class="flex justify-between items-center text-gray-700"
                            >
                                <span class="text-lg font-medium"
                                    >Наборов:</span
                                >
                                <span class="text-xl font-bold">
                                    {{ localStore.cartQty }} шт
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex flex-row sm:flex-row gap-2 sticky bottom-0 bg-white p-4 rounded-xl shadow-md"
                        >
                            <button
                                class="flex-1 btn bg-green-500 hover:bg-green-600 text-white rounded-xl py-2 px-4 md:py-4 md:px-6 transition-colors duration-200 font-semibold shadow-md hover:shadow-lg"
                                @click="checkout = !checkout"
                            >
                                <i class="mdi mdi-cart-arrow-right md:me-2"></i>
                                Оформление
                            </button>
                            <button
                                class="flex bg-red-500 hover:bg-red-600 text-white rounded-xl py-2 px-4 md:py-4 md:px-6 transition-colors duration-200 font-semibold shadow-md hover:shadow-lg"
                                @click="localStore.clearStore('cart')"
                            >
                                <i
                                    class="mdi mdi-trash-can-outline md:me-2"
                                ></i>
                                <div class="hidden md:block">Очистить</div>
                            </button>
                        </div>
                    </div>

                    <div v-else class="space-y-6">
                        <button
                            class="inline-flex items-center text-gray-600 hover:text-gray-800 transition-colors font-medium"
                            @click="checkout = !checkout"
                        >
                            <i class="mdi mdi-arrow-left mr-2"></i>
                            Назад в корзину
                        </button>

                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    :class="`btn rounded-xl py-4 px-6 transition-colors duration-200 font-semibold shadow-md hover:shadow-lg ${
                                        delivery
                                            ? 'bg-black text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    }`"
                                    @click="delivery = true"
                                >
                                    <i class="mdi mdi-truck mr-2"></i>
                                    Доставка
                                </button>
                                <button
                                    :class="`btn rounded-xl py-4 px-6 transition-colors duration-200 font-semibold shadow-md hover:shadow-lg ${
                                        !delivery
                                            ? 'bg-black text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    }`"
                                    @click="delivery = false"
                                >
                                    <i class="mdi mdi-shopping mr-2"></i>
                                    Самовывоз
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    :class="`btn rounded-xl py-4 px-6 transition-colors duration-200 font-semibold shadow-md hover:shadow-lg ${
                                        delivery
                                            ? formData.payType === 'cash'
                                            : noDelForm.payType === 'cash'
                                            ? 'bg-black text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    }`"
                                    @click="
                                        delivery
                                            ? (formData.payType = 'cash')
                                            : (noDelForm.payType = 'cash')
                                    "
                                >
                                    <i class="mdi mdi-cash-multiple mr-2"></i>
                                    Наличные
                                </button>
                                <button
                                    :class="`btn rounded-xl py-4 px-6 transition-colors duration-200 font-semibold shadow-md hover:shadow-lg ${
                                        delivery
                                            ? formData.payType === 'card'
                                            : noDelForm.payType === 'card'
                                            ? 'bg-black text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    }`"
                                    @click="
                                        delivery
                                            ? (formData.payType = 'card')
                                            : (noDelForm.payType = 'card')
                                    "
                                >
                                    <i class="mdi mdi-credit-card mr-2"></i>
                                    Картой курьеру
                                </button>
                            </div>
                        </div>

                        <div v-if="delivery">
                            <delivery-form
                                :form-data="formData"
                                :schema="schema"
                                :validator-bag="validatorBag"
                                :validate="validate"
                                @validate="validate('delivery')"
                            />
                        </div>
                        <div v-else class="space-y-6">
                            <how-to-get-to-us />
                            <no-delivery-form
                                :noDelForm="noDelForm"
                                :schema="noDelSchema"
                                :validator-bag="validatorBag"
                                :validate="validate"
                                @validate="validate('noDelivery')"
                            />
                        </div>

                        <button
                            class="w-full btn bg-green-500 hover:bg-green-600 text-white rounded-xl py-4 transition-colors duration-200 font-semibold shadow-md hover:shadow-lg"
                            @click="
                                delivery
                                    ? validate('delivery')
                                    : validate('noDelivery')
                            "
                        >
                            Заказать
                        </button>
                    </div>
                </div>
                <div v-else class="text-center">
                    <img
                        src="/images/placeholder/empty-cart.png"
                        alt="Пустая корзина"
                        class="mx-auto max-h-[320px]"
                    />
                    <div class="space-y-3">
                        <h5 class="text-3xl font-bold text-gray-800">
                            Твоя корзина пуста
                        </h5>
                        <p class="text-gray-500 text-sm max-w-[300px] mx-auto">
                            Чтобы оформить заказ - необходимо добавить товары в
                            корзину
                        </p>
                    </div>
                </div>
            </div>
            <div v-else class="text-center space-y-8 py-8">
                <img
                    src="//via.placeholder.com/512x512"
                    alt="Заказ оформлен"
                    class="mx-auto max-w-[240px]"
                />
                <h5 class="text-2xl font-bold text-gray-800">
                    Заказ успешно оформлен! Ожидайте звонка менеджера.
                </h5>
            </div>
        </transition>
    </div>
</template>

<style lang="sass" scoped>
.cart-list
    li
        transform-origin: center
        will-change: transform, opacity

.v-enter-active,
.v-leave-active
    transition: opacity 0.3s ease

.v-enter-from,
.v-leave-to
    opacity: 0
</style>
