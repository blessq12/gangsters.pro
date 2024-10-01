<script>
export default {
    props: {
        data: {
            type: Object,
            required: true
        },
        validate: {
            type: Function,
            required: true
        }
    }
}
</script>

<template>
    <form @submit.prevent="validate('register')">
        <div class="form-group">
            <label for="name">Имя</label>
            <div class="input-group">
                <input type="text" name="name" id="name" class="form-control" v-model="data.name">
                <span class="input-group-text fw-bold">
                    <i class="fa fa-user"></i>
                </span>
            </div>
        </div>
        <div class="form-group">
            <label for="tel">Номер телефона</label>
            <div class="input-group">
                <input type="text" name="tel" id="tel" class="form-control" v-maska data-maska="+7 (###) ###-##-##" placeholder="+7 " v-model="data.tel">
                <span class="input-group-text fw-bold">
                    <i class="fa fa-phone"></i>
                </span>
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email адрес</label>
            <div class="input-group">
                <input type="text" name="email" id="email" class="form-control" v-model="data.email">
                <span class="input-group-text fw-bold">
                    <i class="fa fa-envelope"></i>
                </span>
            </div>
        </div>
        <div class="form-group mt-4">
            <button type="submit" class="btn rounded w-50">
                <span>Отправить</span>
            </button>
        </div>
    </form>
</template>

<style lang="sass" scoped>
.input-group-text 
    background-color: $color-main
    color: #fff
.btn
    background: $color-main 
    color: #fff 
    &:hover
        background: lighten($color-main, 20%)
</style>