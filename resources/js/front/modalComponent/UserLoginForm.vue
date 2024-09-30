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
    },
    data() {
        return {
            showPass: false
        }
    }
}
</script>

<template>
    <form @submit.prevent="validate('login')">
        <div class="form-group">
            <label for="email">Email адрес</label>
            <input type="text" name="email" id="email"  class="form-control"  placeholder="example@gangsters.pro" v-model="data.email">
        </div>
        <div class="form-group">
            <label for="password">Пароль</label>
            <div class="input-group">
                <input :type="showPass ? 'text' : 'password'" class="form-control" name="password" id="password" v-model="data.password">
                <span class="input-group-text cursor-pointer" @click="showPass = !showPass">
                    <i class="fa fa-eye" v-if="!showPass"></i>
                    <i class="fa fa-eye-slash" v-else></i>
                </span>
            </div>
        </div>
        <div class="row mt-4 d-flex align-items-center">            
            <div class="col">
                <button type="submit" class="btn rounded w-100">
                    <span>Войти</span>
                </button>
            </div>
            <div class="col text-start">
                <a href="javascript:void(0)" class="text-muted">Забыли пароль?</a>
            </div>
        </div>
    </form>
</template>

<style scoped lang="sass">
.btn
    background: $color-main 
    color: #fff 
    &:hover
        background: lighten($color-main, 20%)
</style>