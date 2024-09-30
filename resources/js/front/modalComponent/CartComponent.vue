<script>
import { localStore } from "../../stores/localStore";
import { userStore } from "../../stores/userStore";
import { appStore } from "../../stores/appStorage";
import { mapStores } from "pinia";
import { object, string } from "yup";
import moment from "moment";

export default {
  mounted() {
    this.initializeFormData();
  },
  data() {
    return {
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
    }
  },
  computed: {
    ...mapStores(localStore, userStore, appStore),
  },
  methods: {
    initializeFormData() {
      if (this.userStore.authStatus) {
        const { name, tel } = this.userStore.userData;
        this.formData.name = name;
        this.formData.tel = tel;
        this.noDelForm.name = name;
        this.noDelForm.tel = tel;
      }
    },
    createSchema() {
      return object({
        name: string().required("Обязательно").min(3, "Не менее 3 символов").max(255, "Не более 255 символов"),
        tel: string().required("Обязательно").min(18, "Некорректный номер").max(18, "Некорректный номер"),
        street: string().required("Обязательно").min(3, "Не менее 3 символов").max(255, "Не более 255 символов"),
        house: string().required("Обязательно").max(255, "Не более 255 символов"),
        building: string().nullable(),
        staircase: string().nullable(),
        floor: string().nullable(),
        apartment: string().required("Обязательно"),
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
        payType: 'cash',
      };
    },
    createNoDelForm() {
      return {
        name: '',
        tel: '',
        personQty: 1,
        comment: null,
      };
    },
    createNoDelSchema() {
      return object({
        name: string().required("Обязательно").min(3, "Не менее 3 символов").max(255, "Не более 255 символов"),
        tel: string().required("Обязательно").min(18, "Некорректный номер").max(18, "Некорректный номер"),
      });
    },
    validate(form) {
      const schema = form === "delivery" ? this.schema : this.noDelSchema;
      const formData = form === "delivery" ? this.formData : this.noDelForm;
      schema.validate(formData, { abortEarly: false })
        .then((res) => {
          this.updateInputs()
          this.validatorBag = {};
          this.createOrder(res, form === "delivery");
        })
        .catch((err) => {
          this.validatorBag = {};
          err.inner.forEach((e) => {
            this.validatorBag[e.path] = e.message;
          });
          this.updateInputs()
        });
    },
    createOrder(data, delivery) {
      const req = {
        delivery,
        cart: this.localStore.cart.map(({ id, name, price, qty, sku }) => ({ id, name, price, qty, sku })),
        order: data,
      };
      this.localStore.createOrder(req)
        .then(() => {
          this.orderCreated = true;
        })
        .catch(console.log);
    },
    updateInputs() {
      let inputs = document.querySelectorAll('.form-control')
      inputs.forEach(input => {
        if (this.validatorBag[input.name]) {
          input.classList.add('is-invalid')
        } else {
          input.classList.remove('is-invalid')
        }
      })
    }
  },
  watch: {
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
  <transition enter-active-class="animate__animated animate__fadeIn" leave-active-class="animate__animated animate__fadeOut" mode="out-in">
    <!-- transition betwee order create state -->
    <div v-if="!orderCreated">
      <transition enter-active-class="animate__animated animate__fadeIn" leave-active-class="animate__animated animate__fadeOut" mode="out-in">
        <div v-if="localStore.cart.length">
          <!-- cart content checkout or cart -->
          <div v-if="!checkout">
            <transition-group enter-active-class="animate__animated animate__fadeIn" leave-active-class="animate__animated animate__fadeOut" move-class="move" tag="ul" class="cart-list">
              <li v-for="item in localStore.cart" :key="item.id">
                <ProductComponentSmall :product="item" :is-favorite="false" />
              </li>
            </transition-group>
            <div class="cart-footer border-top pt-4 pb-2">
              <ul class="list-unstyled">
                <li>
                  <b>Стоимость: </b>
                  <span v-if="localStore.cartTotal">{{ localStore.cartTotal + " рублей" }}</span>
                </li>
                <li>
                  <b>Наборов: </b>
                  <span v-if="localStore.cartQty">{{ localStore.cartQty + " шт" }}</span>
                </li>
              </ul>
            </div>
            <button class="btn rounded btn-main" @click="checkout = !checkout">Оформление</button>
            <button class="btn rounded btn-danger mx-2" @click="localStore.clearStore('cart')">
              <i class="fa fa-trash"></i> Очистить
            </button>
          </div>
          <div v-else>
            <button class="btn rounded btn-main btn-sm mb-4" @click="checkout = !checkout">
              <i class="fa fa-arrow-left" style="margin-right: 6px"></i> Назад в корзину
            </button>

            <div class="row row-cols-1 mb-4">
              <!-- First button group for delivery options -->
              <div class="btn-group rounded d-block col mb-3 mb-lg-0">
                <button type="button" :class="`btn btn-main ${delivery ? 'active' : ''}`" @click="delivery = true">
                  <i class="fa fa-truck"></i> Доставка
                </button>
                <button type="button" :class="`btn btn-main ${!delivery ? 'active' : ''}`" @click="delivery = false">
                  <i class="fa fa-shopping-cart"></i> Самовывоз
                </button>
              </div>
              <!-- Second button group for payment options -->
              <div class="btn-group rounded d-block col">
                <button type="button" :class="`btn ${formData.payType == 'cash' ? 'active' : ''}`" @click="formData.payType = 'cash'">Наличные</button>
                <button type="button" :class="`btn ${formData.payType == 'card' ? 'active' : ''}`" @click="formData.payType = 'card'">Картой курьеру</button>
              </div>
            </div>
            
            <!-- delivery or not section -->
            <transition enter-active-class="animate__animated animate__fadeIn" leave-active-class="animate__animated animate__fadeOut" mode="out-in">
              <div v-if="delivery">
                <delivery-form
                  :form-data="formData"
                  :schema="schema"
                  :validator-bag="validatorBag"
                  :validate="validate"
                  @validate="validate('delivery')"
                />
              </div>
              <div v-else>

                <!-- how to get to us -->
                 <how-to-get-to-us />
                <!-- end how to get to us -->

                <no-delivery-form
                  :noDelForm="noDelForm"
                  :schema="noDelSchema"
                  :validator-bag="validatorBag"
                  :validate="validate"
                  @validate="validate('noDelivery')"
                />
              </div>
            </transition>
            <!-- end delivery or not section -->
            <button type="button" class="btn rounded btn-main mt-2" @click="delivery ? validate('delivery') : validate('noDelivery')">Заказать</button>
          </div>
          <!-- /end cart content checkout or cart -->
        </div>
        <div v-else>
          <div class="row row-cols-1 align-items-center">
            <div class="col text-center">
              <img src="/images/placeholder/empty-storage-cart.png" alt="" class="img-fluid" style="max-height: 280px" />
            </div>
            <div class="col text-center">
              <h5 class="fw-semibold">Твоя корзина пуста</h5>
              <p>Чтобы оформить заказ - необходимо добавить товары в корзину</p>
            </div>
          </div>
        </div>
      </transition>
    </div>
    <div v-else>
      <div class="row">
        <div class="col">
          <div class="text-center">
            <img src="//via.placeholder.com/512x512" alt="" class="img-flluid mb-3" style="max-width: 200px" />
            <h5>Заказ успешно оформлен! Ожидайте звонка менеджера.</h5>
          </div>
        </div>
      </div>
    </div>
    <!-- /end transition between order create state -->
  </transition>
</template>

<style lang="sass">
.btn-group
  button:first-child
    border-top-left-radius: 16px !important
    border-bottom-left-radius: 16px !important
  button:last-child
    border-top-right-radius: 16px !important
    border-bottom-right-radius: 16px !important
.btn-qty
  background: lighten($color-main, 10%)
  border: unset
  color: #fff
  padding: 0 16px !important
.btn-group
  border-radius: 16px !important
  overflow: hidden
  width: fit-content
  .btn
    background: lighten($color-main, 40%)
    border: unset
    color: #fff
    padding: 8 16px !important
    &.active
      background: $color-main
      color: #fff
.btn-main
  background: $color-main
  border: unset
  color: #fff
.cart-list
  li
    margin-bottom: 0
.btn-group
  button
    border-color: #ffc107
    &.active
      background: #ffc107
.move
  position: absolute
</style>