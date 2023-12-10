<script>

import * as yup from 'yup';
import { ru } from 'yup-locales';
import { setLocale } from 'yup';
setLocale(ru);

import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
import { orderStore } from '../stores/orderStore';

export default{
    computed:{
        ...mapStores(localStore, orderStore)
    },
    data:()=>({
        order: false,
        validated: false,
        schema: yup.object({
            name: yup.string().required('Имя обязательно'),
            tel: yup.string().required('Телефон обязательно').min(18,'Некорректный телефон'),
            street: yup.string().required('Обязательное поле').min(10, 'Слишком короткое название улицы'),
            house: yup.string().required('Обязательное поле'),
            staircase: yup.number().typeError('Допускаются цифры').required('Обязательное поле'),
            floor: yup.number().typeError('Допускаются цифры'),
            appartment: yup.number().typeError('Допускаются цифры').required('Обязательное поле')
        })
    }),
    methods:{
        create(){
            console.log('asd')
        }
    },
    mounted(){},
    watch:{}
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
                    <Form @submit="create" :validation-schema="schema" >
                        <div class="row row-cols g-2 mb-2">
                            <div class="col">
                                <label for="name">Имя <ErrorMessage name="name" class="text-danger mx-2" style="font-size: 10px;"/></label>
                                <field name="name" id="name" v-slot="{ meta, field }">
                                    <input type="text" v-bind="field" class="form-control" :class=" meta.valid ? 'border-success' : 'border-danger' ">
                                </field>
                            </div>
                            <div class="col">
                                <label for="tel">Телефон <ErrorMessage name="tel" class="text-danger mx-2" style="font-size: 10px;"/></label>
                                <field name="tel" v-slot="{ meta, field }">
                                    <input type="text" class="form-control" :class="meta.valid ? 'border-success' : 'border-danger'" v-bind="field" v-maska data-maska="+7 (###) ###-##-##">
                                </field>
                            </div>
                        </div>
                        <div class="row row-cols mb-2">
                            <div class="col-12">
                                <label for="street">Улица(переулок) <ErrorMessage name="street" class="text-danger mx-2" style="font-size: 10px;"/></label>
                                <Field name="street" v-slot="{ meta, field }">
                                    <input type="text" v-bind="field" class="form-control" :class="meta.valid ? 'border-success' : 'border-danger'">
                                </Field>
                            </div>
                        </div>
                        <div class="row row-cols g-2 mb-4">
                            <div class="col-6">
                                <label for="house">Номер дома <ErrorMessage name="house" class="text-danger mx-2" style="font-size: 10px;"/></label>
                                <Field name="house" v-slot="{ meta, field }">
                                    <input type="text" v-bind="field" class="form-control" :class="meta.valid ? 'border-success' : 'border-danger'">
                                </Field>
                                
                            </div>
                            <div class="col-6">
                                <label for="staircase">Подъезд <ErrorMessage name="staircase" class="text-danger mx-2" style="font-size: 10px;"/></label>
                                <Field name="staircase" v-slot="{ meta, field }">
                                    <input type="text" v-bind="field" class="form-control" :class="meta.valid ? 'border-success' : 'border-danger'">
                                </Field>
                            </div>
                            <div class="col-6">
                                <label for="floor">Этаж <ErrorMessage name="floor" class="text-danger mx-2" style="font-size: 10px;"/></label>
                                <Field name="floor" v-slot="{ meta, field }">
                                    <input type="text" v-bind="field" class="form-control" :class="meta.valid ? 'border-success' : 'border-danger' ">
                                </Field>    
                            </div>
                            <div class="col-6">
                                <label for="appartment">Квартира <ErrorMessage name="appartment" class="text-danger mx-2" style="font-size: 10px;"/></label>
                                <Field name="appartment" v-slot="{ meta, field }">
                                    <input type="text" v-bind="field" class="form-control" :class="meta.valid ? 'border-success' : 'border-danger' ">
                                </Field>
                            </div>
                            <div class="col-12">
                                <div class="row row-cols g-2">
                                    <div class="col">
                                        <button type="button" class="btn btn-primary w-100">
                                            Наличными
                                            <i class="fa fa-money"></i>
                                        </button>
                                    </div>
                                    <div class="col">
                                        <button type="button" class="btn btn-outline-primary w-100">
                                            Картой курьеру
                                            <i class="fa fa-credit-card"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">asdsd</button>
                    </Form>
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