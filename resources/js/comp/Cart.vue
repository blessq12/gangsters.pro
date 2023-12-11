<script>
import * as yup from 'yup';

import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';

export default{
    computed:{
        ...mapStores(localStore)
    },
    data:()=>({
        order: true,
        schema: yup.object({
            name: yup.string().required('Обязательное поле').min(3, 'минимум 3 символа'),
            tel: yup.string().required('Обязательное поле').min(18, 'Некорректный номер'),
            street: yup.string().required('Обязательное поле').min(10, 'Слишком короткое название'),
            house: yup.string().required('Обязательное поле'),
            staircase: yup.number('Допускаются цифры').required('Обязательное поле'),
            floor: yup.number('Допускаются цифры'),
            appartment: yup.string().required('Обязательное поле'),
            payType: yup.string(),
            comment: yup.string()
        }),
        formInput:{
            payType: 'cash'
        },
        formErrors:{}
    }),
    methods:{
        validateForm(){
            this.schema.validate(this.formInput, {abortEarly: false}).then(res => {
                this.formErrors = {}
                console.log(res)
            }).catch(err => {
                this.formErrors = {}
                err.inner.forEach(element => {
                    this.formErrors[element.path] = element.message
                    console.log(element.message)
                });
            })
        }
    },
    mounted(){}
}
</script>

<template>
    <transition
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
        mode="out-in"
    >
    <div class="row" v-if="localStore.cart.length" key="cart">
        <div class="col-6">
            <h4>Корзина</h4>
            <p class="mb-0">Стоимость: <b>{{ localStore.totalCart }} RUR</b></p>
            <p class="mb-0">Кол-во наборов: <b>{{ localStore.totalKits }} шт</b></p>
            {{ formErrors }}
            <div class="d-flex mt-4">
                <div v-if="!order">
                    <button type="button" class="btn btn-outline-primary" @click="order=!order">К оформлению</button>
                    <button type="button" class="btn btn-danger mx-2" @click="localStore.clearStore('cart')">Очистить</button>
                </div>
                <div v-else>
                    <button type="button" class="btn btn-outline-primary" @click="order=!order">Назад в корзину</button>
                </div>
            </div>
        </div>
        <div class="col-6">
            <transition
                enter-active-class="animate__animated animate__fadeIn"
                leave-active-class="animate__animated animate__fadeOut"
                mode="out-in"
            >
                <div v-if="!order">
                    <transition-group
                    class="list-unstyled p-0 m-0"
                    tag="ul"
                    enter-active-class="animate__animated animate__fadeInRight"
                    leave-active-class="animate__animated animate__fadeOutRight"
                    >
                        <li v-for="product in localStore.cart" :key="product.id">
                            <single-product :prod="product" comp="cart"></single-product>
                        </li>
                    </transition-group>
                </div>
                <div v-else>
                    <form @submit.prevent="validateForm" novalidate>
                        <!-- clietn -->
                        <div class="row row-cols mb-4 g-2">
                            <div class="col-12">
                                <h4>Данные клиента</h4>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="name">Имя <span v-if="formErrors.name" class="text-danger">{{ formErrors.name }}</span></label>
                                    <input type="text" name="name" id="name" class="form-control" v-model="formInput.name">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="tel">Телефон <span v-if="formErrors.tel" class="text-danger">{{ formErrors.tel }}</span></label>
                                    <input type="text" name="tel" id="tel" class="form-control" v-maska data-maska="+7 (###) ###-##-##" v-model="formInput.tel">
                                </div>
                            </div>
                        </div>
                        <!-- address -->
                        <div class="row row-cols g-2 mb-4">
                            <div class="col-12">
                                <h4>Данные о доставке</h4>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="street">Улица/ Переулок/ Проспект</label>
                                    <input type="text" name="street" id="street" class="form-control" v-model="formInput.street">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="house">Номер дома</label>
                                    <input type="text" name="house" id="house" class="form-control" v-model="formInput.house">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="staircase">Подъезд</label>
                                    <input type="text" name="staircase" id="staircase" class="form-control" v-model="formInput.staircase">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="floor">Этаж</label>
                                    <input type="text" name="floor" id="floor" class="form-control" v-model="formInput.floor">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-grou">
                                    <label for="appartment">Квартира</label>
                                    <input type="text" name="appartment" id="appartment" class="form-control" v-model="formInput.appartment">
                                </div>
                            </div>
                        </div>
                        <!-- paytype -->
                        <div class="row row-cols g-2 mb-4">
                            <div class="col-12">
                                <h4>Способ оплаты</h4>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-outline-primary w-100"
                                    :class="formInput.payType == 'cash' ? 'active' : '' "
                                    @click="formInput.payType = 'cash'"
                                >
                                    Наличными
                                    <i class="fa fa-money mx-2"></i>
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-outline-primary w-100"
                                    :class="formInput.payType == 'credit-card' ? 'active' : '' "
                                    @click="formInput.payType = 'credit-card'"
                                >
                                    Картой курьеру
                                    <i class="fa fa-credit-card mx-2"></i>
                                </button>
                            </div>
                        </div>
                        <!-- comment -->
                        <div class="row row-cols g-2 mb-4">
                            <div class="col">
                                <div class="accordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#comm">
                                                Добавить комментарий к заказу
                                            </button>
                                        </h2>
                                        <div class="accordion-collapse collapse " id="comm">
                                            <div class="accordion-body">
                                                <textarea name="comment" id="comment" cols="30" rows="4" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row row-cols">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">Оформить заказ</button>
                                <button type="button" class="btn btn-outline-primary mx-2" 
                                    @click="formInput = {payType: 'cash'}"
                                >Очистить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </transition>
        </div>
    </div>
    <div class="row" v-else key="emptyCart">
        <div class="col-12 text-center">
            <h2>В вашей корзине ничего нет</h2>
        </div>
    </div>
    </transition>
</template>

<style lang="sass">
</style>