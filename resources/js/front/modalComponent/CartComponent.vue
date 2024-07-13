<script>
import { validate } from "vee-validate";
import { localStore } from "../../stores/localStore";
import { userStore } from "../../stores/userStore";
import { appStore } from "../../stores/appStorage";
import { mapStores } from "pinia";
import { object, string } from "yup";
import moment from "moment";

export default {
  mounted() {
    if (this.userStore.authStatus) {
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
    orderCreated: false,
    order: null,
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
      personQty: 1,
      comment: null,
      payType: 'cash',
    },
    noDelForm: {
      name: null,
      tel: null,
      personQty: 1,
      comment: null,
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
    ...mapStores(localStore, userStore, appStore),
  },
  methods: {
    validate(form) {
      const schema = form === "delivery" ? this.schema : this.noDelSchema;
      const formData = form === "delivery" ? this.formData : this.noDelForm;
      schema
        .validate(formData, { abortEarly: false })
        .then((res) => {
          this.validatorBag = {};
          this.createOrder(res, form === "delivery");
        })
        .catch((err) => {
          this.validatorBag = {};
          err.inner.forEach((e) => {
            this.validatorBag[e.path] = e.message;
          });
        });
    },
    createOrder(data, delivery) {
      const req = {
        delivery: delivery,
        cart: this.localStore.cart.map((item) => ({
          id: item.id,
          name: item.name,
          price: item.price,
          qty: item.qty,
          sku: item.sku,
        })),
        order: data,
      };
      this.localStore.createOrder(req)
        .then(() => {
        this.orderCreated = true;
      })
      .catch((err) => {
        console.log(err);
      });
    },
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
  <transition
    enter-active-class="animate__animated animate__fadeIn"
    leave-active-class="animate__animated animate__fadeOut"
    mode="out-in"
  >
    <div v-if="!orderCreated">
      <transition
        enter-active-class="animate__animated animate__fadeIn"
        leave-active-class="animate__animated animate__fadeOut"
        mode="out-in"
      >
        <div v-if="localStore.cart.length">
          <div v-if="!orderCreated">
            <transition
              enter-active-class="animate__animated animate__fadeIn"
              leave-active-class="animate__animated animate__fadeOut"
              mode="out-in"
            >
              <div v-if="!checkout">
                <transition-group
                  enter-active-class="animate__animated animate__fadeIn"
                  leave-active-class="animate__animated animate__fadeOut"
                  move-class="move"
                  tag="ul"
                  class="cart-list"
                >
                  <li v-for="item in localStore.cart" :key="item.id">
                    <ProductComponentSmall :product="item" :is-favorite="false" />
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
                        <span v-if="localStore.cartTotal" :key="localStore.cartTotal">
                          {{ localStore.cartTotal + " рублей" }}
                        </span>
                      </transition>
                    </li>
                    <li>
                      <b>Наборов: </b>
                      <transition
                        enter-active-class="animate__animated animate__fadeIn"
                        leave-active-class="animate__animated animate__fadeOut"
                        mode="out-in"
                      >
                        <span v-if="localStore.cartQty" :key="localStore.cartQty">
                          {{ localStore.cartQty + " шт" }}
                        </span>
                      </transition>
                    </li>
                  </ul>
                </div>
                <button class="btn rounded btn-main" @click="checkout = !checkout">
                  Оформление
                </button>
                <button class="btn rounded btn-danger mx-2" @click="localStore.clearStore('cart')">
                  <i class="fa fa-trash"></i> Очистить
                </button>
              </div>
              <div v-else>
                <button class="btn rounded btn-main btn-sm mb-4" @click="checkout = !checkout">
                  <i class="fa fa-arrow-left" style="margin-right: 6px"></i>
                  Назад в корзину
                </button>

                <div class="btn-group rounded d-block mb-4">
                  <button
                    type="button"
                    :class="`btn btn-main ${delivery ? 'active' : ''}`"
                    @click="delivery = true"
                  >
                    <i class="fa fa-truck"></i>
                    Доставка
                  </button>
                  <button
                    type="button"
                    :class="`btn btn-main ${!delivery ? 'active' : ''}`"
                    @click="delivery = false"
                  >
                    <i class="fa fa-shopping-cart"></i>
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
                        <div class="row">
                          <p class="mb-2">Способ оплаты</p>
                          <div class="col">
                            <div class="btn-group">
                              <button
                                type="button"
                                :class="`btn ${formData.payType == 'cash' ? 'active' : ''}`"
                                @click="formData.payType = 'cash'"
                              >
                                Наличные
                              </button>
                              <button
                                type="button"
                                :class="`btn ${formData.payType == 'card' ? 'active' : ''}`"
                                @click="formData.payType = 'card'"
                              >
                                Картой курьеру
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="row row-cols-1 g-2">
                          <div class="col">
                            <div class="d-flex" style="white-space: nowrap">
                              <label for="name">Ваше Имя</label>
                              <error-label :errorBag="validatorBag" name="name"></error-label>
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
                            <div class="d-flex" style="white-space: nowrap">
                              <label for="tel">Номер телефона</label>
                              <error-label :errorBag="validatorBag" name="tel"></error-label>
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
                            <div class="d-flex" style="white-space: nowrap">
                              <label for="name">Улица</label>
                              <error-label :errorBag="validatorBag" name="street"></error-label>
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
                        <div class="row row-cols-2 row-cols-lg-3 g-2">
                          <div class="col">
                            <div class="d-flex" style="white-space: nowrap">
                              <label for="name">Дом</label>
                              <error-label :errorBag="validatorBag" name="house"></error-label>
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
                            <div class="d-flex" style="white-space: nowrap">
                              <label for="name">Строение</label>
                              <error-label :errorBag="validatorBag" name="building"></error-label>
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
                            <div class="d-flex" style="white-space: nowrap">
                              <label for="name">Подъезд</label>
                              <error-label :errorBag="validatorBag" name="staircase"></error-label>
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
                            <div class="d-flex" style="white-space: nowrap">
                              <label for="name">Этаж</label>
                              <error-label :errorBag="validatorBag" name="floor"></error-label>
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
                            <div class="d-flex" style="white-space: nowrap">
                              <label for="name">Квартира</label>
                              <error-label :errorBag="validatorBag" name="apartment"></error-label>
                            </div>
                            <input
                              type="text"
                              name="apartment"
                              id="apartment"
                              class="form-control"
                              v-model="formData.apartment"
                            />
                          </div>
                          <div class="col">
                          <div class="d-flex" style="white-space: nowrap">
                            <label for="personQty">Количество персон</label>
                            <error-label :errorBag="validatorBag" name="personQty"></error-label>
                          </div>
                          <div class="input-group" style="max-height: 37px;">
                            <button class="btn btn-qty" type="button" style="padding: 0 16px !important;" @click="formData.personQty--" :disabled="formData.personQty <= 1">-</button>
                            <input
                              type="text"
                              name="personQty"
                              id="personQty"
                              class="form-control text-center fw-bold"
                              v-model="formData.personQty"
                            />
                            <button class="btn btn-qty" style="padding: 0 16px !important;" type="button" @click="formData.personQty++">+</button>
                          </div>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="row">
                          <div class="col">
                            <div class="d-flex" style="white-space: nowrap">
                              <label for="name">Комментарий</label>
                              <error-label :errorBag="validatorBag" name="comment"></error-label>
                            </div>
                            <textarea
                              name="comment"
                              id="comment"
                              class="form-control"
                              v-model="formData.comment"
                            ></textarea>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                  <div v-else>
                    <button class="btn w-100 btn-main btn-sm rounded mb-3" data-bs-toggle="collapse" data-bs-target="#map-collapse">
                      Как к нам проехать
                      <i class="fa fa-chevron-down"></i>
                    </button>
                    <div style="position: relative; overflow: hidden" id="map-collapse" class="collapse overflow-hidden mt-4 rounded">
                      <a
                        href="https://yandex.ru/maps/org/gangster_s_sushi/82888444717/?utm_medium=mapframe&utm_source=maps"
                        style="color: #eee; font-size: 12px; position: absolute; top: 0px;"
                      >
                        Gangster's sushi
                      </a>
                      <a
                        href="https://yandex.ru/maps/67/tomsk/category/food_and_lunch_delivery/184108273/?utm_medium=mapframe&utm_source=maps"
                        style="color: #eee; font-size: 12px; position: absolute; top: 14px;"
                      >
                        Доставка еды и обедов в Томске
                      </a>
                      <iframe
                        src="https://yandex.ru/map-widget/v1/?ll=84.986330%2C56.513423&mode=search&oid=82888444717&ol=biz&z=16.61"
                        width="100%"
                        height="400"
                        frameborder="0"
                        allowfullscreen="true"
                        style="position: relative"
                      ></iframe>
                    </div>
                    <form ref="noDelivery" class="mb-4">
                      <div class="row">
                        <div class="col">
                          <div class="form-group">
                            <div class="d-flex">
                              <label for="name">Имя</label>
                              <error-label :errorBag="validatorBag" name="name"></error-label>
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
                              <error-label :errorBag="validatorBag" name="tel"></error-label>
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
                      <div class="row">
                        <div class="col">
                          <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center">
                              <label for="personQty">Количество персон</label>
                              <error-label :errorBag="validatorBag" name="personQty"></error-label>
                            </div>
                            <div class="input-group">
                              <button type="button" class="btn btn-qty" style="padding: 0 16px!important;" @click="noDelForm.personQty--" :disabled="noDelForm.personQty < 2">-</button>
                              <input
                                type="text"
                                name="personQty"
                                id="personQty"
                                class="form-control text-center fw-bold"
                                v-model="noDelForm.personQty"
                                min="1"
                                max="10"
                              />
                              <button type="button" class="btn btn-qty" style="padding: 0 16px!important;" @click="noDelForm.personQty++">+</button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col">
                          <div class="form-group">
                            <div class="d-flex">
                              <label for="comment">Комментарий</label>
                              <error-label :errorBag="validatorBag" name="comment"></error-label>
                            </div>
                            <textarea
                              name="comment"
                              id="comment"
                              class="form-control"
                              v-model="noDelForm.comment"
                            ></textarea>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                </transition>

                <button
                  type="button"
                  class="btn rounded btn-main mt-2"
                  @click="delivery ? validate('delivery') : validate('noDelivery')"
                >
                  Заказать
                </button>
              </div>
            </transition>
          </div>
        </div>
        <div v-else>
          <div class="row row-cols-1 align-items-center">
            <div class="col text-center">
              <img
                src="/images/placeholder/empty-storage-cart.png"
                alt=""
                class="img-fluid"
                style="max-height: 280px"
              />
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
            <img
              src="//via.placeholder.com/512x512"
              alt=""
              class="img-flluid mb-3"
              style="max-width: 200px"
            />
            <h5>Заказ успешно оформлен! Ожидайте звонка менеджера.</h5>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<style lang="sass" scoped>
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
