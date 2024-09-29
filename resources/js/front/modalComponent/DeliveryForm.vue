<script>
export default {
  props: {
    formData: {
      type: Object,
      required: true,
    },
    validatorBag: {
      type: Object,
      required: true,
    },
    validate: {
      type: Function,
      required: true,
    },
    schema: {
      type: Object,
      required: true,
    },
  },
};
</script>

<template>
  <form ref="delivery">
    <div class="form-group">
      <div class="row row-cols-1 g-2">
        <div class="col">
          <label for="name">Ваше Имя</label>
          <input type="text" name="name" id="name" class="form-control" v-model="formData.name" />
        </div>
        <div class="col">
          <label for="tel">Номер телефона</label>
          <input type="text" name="tel" id="tel" class="form-control" v-maska data-maska="+7 (###) ###-##-##" v-model="formData.tel" />
        </div>
      </div>
    </div>
    <div class="form-group">
      <div class="row g-2">
        <div class="col-12">
          <label for="street">Улица</label>
          <input type="text" name="street" id="street" class="form-control" v-model="formData.street" />
        </div>
      </div>
    </div>
    <div class="form-group">
      <div class="row row-cols-2 row-cols-lg-3 g-2">
        <div class="col">
          <label for="house">Дом</label>
          <input type="text" name="house" id="house" class="form-control" v-model="formData.house" />
        </div>
        <div class="col">
          <label for="building">Строение</label>
          <input type="text" name="building" id="building" class="form-control" v-model="formData.building" />
        </div>
        <div class="col">
          <label for="staircase">Подъезд</label>
          <input type="text" name="staircase" id="staircase" class="form-control" v-model="formData.staircase" v-maska data-maska="#####" />
        </div>
        <div class="col">
          <label for="floor">Этаж</label>
          <input type="text" name="floor" id="floor" class="form-control" v-model="formData.floor" v-maska data-maska="#####" />
        </div>
        <div class="col">
          <label for="apartment">Квартира</label>
          <input type="text" name="apartment" id="apartment" class="form-control" v-model="formData.apartment" />
        </div>
        <div class="col">
          <label for="personQty">Количество персон</label>
          <div class="input-group" style="max-height: 37px;">
            <button class="btn btn-qty" type="button" style="padding: 0 16px !important;" @click="formData.personQty--" :disabled="formData.personQty <= 1">-</button>
            <input type="text" name="personQty" id="personQty" class="form-control text-center fw-bold" v-model="formData.personQty" />
            <button class="btn btn-qty" style="padding: 0 16px !important;" type="button" @click="formData.personQty++">+</button>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group">
      <div class="row">
        <div class="col">
          <label for="comment">Комментарий</label>
          <textarea name="comment" id="comment" class="form-control" v-model="formData.comment"></textarea>
        </div>
      </div>
    </div>
  </form>
</template>

<style scoped lang="sass">
</style>
