<script>
export default {
    props: {
        noDelForm: {
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
    <form ref="noDelivery" class="mb-4">
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="name">Имя</label>
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
                    <label for="tel">Телефон</label>
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
                    <label for="personQty">Количество персон</label>
                    <div class="input-group">
                        <button
                            type="button"
                            class="btn btn-qty"
                            style="padding: 0 16px !important"
                            @click="noDelForm.personQty--"
                            :disabled="noDelForm.personQty < 2"
                        >
                            -
                        </button>
                        <input
                            type="text"
                            name="personQty"
                            id="personQty"
                            class="form-control text-center fw-bold"
                            v-model="noDelForm.personQty"
                            min="1"
                            max="10"
                        />
                        <button
                            type="button"
                            class="btn btn-qty"
                            style="padding: 0 16px !important"
                            @click="noDelForm.personQty++"
                        >
                            +
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="comment">Комментарий</label>
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
</template>

<style lang="sass" scoped>
button:disabled
  opacity: 0.5
  background: #dedede
  color: #fff
</style>
