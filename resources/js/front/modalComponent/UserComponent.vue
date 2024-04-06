<script>
import { object, string } from 'yup'
export default {
    data: () => ({
        form: 'login',
        showPass: false
    })
}
</script>

<template>
    <div class="row">
        <div class="col">
            <p>Авторизуйтесь или зарегистрируйте новую учетную запись</p>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col">
            <div class="btn-group">
                <button :class="`rounded btn ${form == 'login' ? 'active' : ''}`" type="button" @click="form = 'login'">
                    Авторизация
                </button>
                <button :class="`rounded btn ${form == 'register' ? 'active' : ''}`" type="button" @click="form = 'register'">
                    Регистрация
                </button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <form @submit.prevent="validate" v-if="form == 'login'">
                <div class="form-group">
                    <label for="tel">Номер телефона</label>
                    <input type="text" name="tel" id="tel" class="form-control" v-maska data-maska="+7 (###) ###-##-##" placeholder="+7 ">
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <div class="input-group">
                        <input :type="showPass ? 'text' : 'password'" class="form-control" name="password" id="password">
                        <span class="input-group-text" @click="showPass = !showPass">
                            <i class="fa fa-eye" v-if="!showPass"></i>
                            <i class="fa fa-eye-slash" v-else></i>
                        </span>
                    </div>
                </div>
            </form>
            <form @submit.prevent="validate" v-else>
                <div class="form-group">
                    <label for="name">Имя</label>
                    <input type="text" name="name" id="name" class="form-control">
                </div>
                <div class="form-group">
                    <label for="tel">Номер телефона</label>
                    <input type="text" name="tel" id="tel" class="form-control" v-maska data-maska="+7 (###) ###-##-##" placeholder="+7 ">
                </div>
                <div class="form-group">
                    <label for="email">Email адрес</label>
                    <input type="text" name="email" id="email" class="form-control">
                </div>
            </form>
        </div>
    </div>
</template>

<style lang="sass" scoped>
.btn-group
    button
        border: 0
    button:first-child
        border-radius: 18px 0 0 18px !important
    button:last-child
        border-radius: 0 18px 18px 0 !important
    .active
        background: orange
</style>