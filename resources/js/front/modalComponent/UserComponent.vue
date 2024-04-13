<script>
import { object, string } from 'yup'
import { userStore } from '../../stores/userStore'
import { mapStores } from 'pinia'

export default {
    computed: {
        ...mapStores(userStore)
    },
    data: () => ({
        form: 'login',
        showPass: false,
        loginData: {
            tel: null,
            password: null
        },
        registerData: {
            name: null,
            tel: null,
            email: null
        },
        loginSchema: object({
            tel: string().required('Обязательное поле').min(18, 'Номер 18 символов').max(18, 'Номер 18 символов'),
            password: string().required('Обязательное поле').min(6, "Минимум 6 символов")
        }),
        registerSchema: object({
            name: string().required('Обязательное поле').min(3, "Минимум 3 символа").max(255, 'Максимум 255 символов'),
            tel: string().required('Обязательное поле').min(18, 'Номер 18 символов').max(18, 'Номер 18 символов'),
            email: string().required('Обязательное поле').email('Невалидный email адрес').max(255, 'Максимум 255 символов')
        }),
        loginErrorBag: {},
        registerErrorBag: {},
    }),
    methods: {
        validate(form) {
            if (form == 'login') {
                this.loginSchema.validate(this.loginData, { abortEarly: false })
                    .then( res => {
                        this.loginErrorBag = {}
                        this.userStore.auth(res)
                        this.loginData = {
                            tel: null,
                            password: null
                        }
                    })
                    .catch( err => {
                        this.loginErrorBag = {}
                        err.inner.forEach( e => { 
                            this.loginErrorBag[e.path] = e.message
                     })})
            }
            if (form == 'register') {
                this.registerSchema.validate(this.registerData, { abortEarly: false })
                    .then(res => {
                        this.registerErrorBag = {}
                        this.userStore.register(res)
                        this.registerData = {
                            name: null,
                            tel: null,
                            email: null
                        }
                    })
                    .catch(err => {
                        this.registerErrorBag = {}
                        err.inner.forEach( e => {
                            this.registerErrorBag[e.path] = e.message
                        })
                    })
            }
        },
        showToast() {
        }
    }
}
</script>

<template>
    <div v-if="!userStore.authStatus">
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
                <form @submit.prevent="validate('login')" v-if="form == 'login'">
                    <div class="form-group">
                        <div class="d-flex mb-2">
                            <label for="tel">Номер телефона</label>
                            <error-label :errorBag="loginErrorBag" name="tel"></error-label>
                        </div>
                        <input type="text" name="tel" id="tel" class="form-control" v-maska data-maska="+7 (###) ###-##-##" placeholder="+7 " v-model="loginData.tel">
                    </div>
                    <div class="form-group">
                        <div class="d-flex mb-2">
                            <label for="password">Пароль</label>
                            <error-label :errorBag="loginErrorBag" name="password"></error-label>
                        </div>
                        <div class="input-group">
                            <input :type="showPass ? 'text' : 'password'" class="form-control" name="password" id="password" v-model="loginData.password">
                            <span class="input-group-text" @click="showPass = !showPass">
                                <i class="fa fa-eye" v-if="!showPass"></i>
                                <i class="fa fa-eye-slash" v-else></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" class="btn rounded btn-light">
                            Отправить
                        </button>
                    </div>
                </form>
                <form @submit.prevent="validate('register')" v-else>
                    <div class="form-group">
                        <div class="d-flex mb-2">
                            <label for="name">Имя</label>
                            <error-label :errorBag="registerErrorBag" name="name"></error-label>
                        </div>
                        <input type="text" name="name" id="name" class="form-control" v-model="registerData.name">
                    </div>
                    <div class="form-group">
                        <div class="d-flex mb-2">
                            <label for="tel">Номер телефона</label>
                            <error-label :errorBag="registerErrorBag" name="tel"></error-label>
                        </div>
                        <input type="text" name="tel" id="tel" class="form-control" v-maska data-maska="+7 (###) ###-##-##" placeholder="+7 " v-model="registerData.tel">
                    </div>
                    <div class="form-group">
                        <div class="d-flex mb-2">
                            <label for="email">Email адрес</label>
                            <error-label :errorBag="registerErrorBag" name="email"></error-label>
                        </div>
                        <input type="text" name="email" id="email" class="form-control" v-model="registerData.email">
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" class="btn rounded btn-light">
                            Отправить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div v-else>
        <div class="row">
            <div class="col">
                <div class="d-flex align-items-center mb-4">
                    <h5 class="mb-0">Привет, {{ userStore.userData.name }}</h5>
                    <a href="javascript:void(0)" @click="userStore.logout()" class="text-danger mx-4" style="font-size: 12px;"> <i class="fa fa-sign-out"></i>Выйти</a>
                </div>

                <p>На данный момент на счету {{ userStore.userData.coins }} койнов. Продолжать совершать заказы, чтобы накопить больше</p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <button type="button" class="btn btn-secondary w-100" data-bs-toggle="collapse" data-bs-target="#orders">
                    Мои заказы
                </button>
                <div class="collapse mt-2" id="orders">
                    <p>Нет ни одного заказа в истории</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="sass" scoped>
.btn-group
    button
        border: 0
        background: #fff
    button:first-child
        border-radius: 18px 0 0 18px !important
    button:last-child
        border-radius: 0 18px 18px 0 !important
    .active
        background: orange
</style>