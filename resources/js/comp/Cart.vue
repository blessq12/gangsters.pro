<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
export default{
    computed:{
        ...mapStores(localStore)
    },
    data:()=>({
        order: false,
        orderData: {
            name: '',
            tel: '',
            street: '',
            house: '',
            staircase: '',
            floor: '',
            appartment: '',
            payType: 'cash',
            comment: ''
        }
    }),
    methods:{
        validate(){},
        resetState(){
            this.orderData = {
                name: '',
                tel: '',
                street: '',
                house: '',
                staircase: '',
                floor: '',
                apartment: '',
                payType: 'cash',
                comment: ''
            }
        }
    }
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
                    <form action="">
                        <div class="row row-cols g-2 mb-4">
                            <div class="col">
                                <label for="name">Имя:</label>
                                <input type="text" name="name" id="name" class="form-control" v-model="orderData.name">
                            </div>
                            <div class="col">
                                <label for="tel">Номер телефона</label>
                                <input type="text" name="tel" id="tel" class="form-control" v-model="orderData.tel" v-maska data-maska="+7 (###) ###-##-##">
                            </div>
                        </div>
                        <div class="row row-cols g-2 mb-4">
                            <div class="col-12 mb-2">
                                <label for="street">Улица:</label>
                                <input type="text" name="street" id="street" class="form-control" v-model="orderData.street">
                            </div>
                            <div class="col">
                                <label for="">Дом</label>
                                <input type="text" name="" id="" class="form-control" v-model="orderData.house">
                            </div>
                            <div class="col">
                                <label for="">Подъезд</label>
                                <input type="text" name="" id="" class="form-control" v-model="orderData.staircase">
                            </div>
                            <div class="col">
                                <label for="">Этаж</label>
                                <input type="text" name="" id="" class="form-control" v-model="orderData.floor">
                            </div>
                            <div class="col">
                                <label for="">Квартира</label>
                                <input type="text" name="" id="" class="form-control" v-model="orderData.appartment">
                            </div>
                        </div>
                        <div class="row row-cols g-2 mb-4">
                            <label for="payType">Способ оплаты:</label>
                            <div class="col">
                                <button type="button" class="btn btn-outline-primary w-100" 
                                    :class="orderData.payType == 'cash' ? 'active' : ''"
                                    @click="orderData.payType = 'cash'"
                                >
                                    Наличными
                                    <i class="fa fa-money mx-2"></i>
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-outline-primary w-100"
                                    :class="orderData.payType == 'creditCard' ? 'active' : ''"
                                    @click="orderData.payType = 'creditCard'"
                                >
                                    Картой курьеру
                                    <i class="fa fa-credit-card mx-2"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row row-cols mb-4">
                            <div class="col">
                                <div class="accordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingOne">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                                Добавить комментарий к заказу
                                                <i class="fa fa-plus mx-2"></i>
                                            </button>
                                        </h2>
                                        <div id="collapseOne" class="accordion-collapse collapse">
                                            <div class="accordion-body">
                                                <textarea name="comment" id="comment" cols="30" rows="5" class="form-control" v-model="orderData.comment"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row row-cols">
                            <div class="col">
                                <button type="button" class="btn btn-primary" @click="validate">
                                    Создать заказ
                                    <div class="fa fa-send mx-2"></div>
                                </button>
                                <button type="button" class="btn btn-outline-primary mx-2" @click="resetState">
                                    Очистить
                                </button>
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