<script>
import { object, string } from 'yup'
import { userStore } from '../../stores/userStore'
import { appStore } from '../../stores/appStorage'
import { mapStores } from 'pinia'
import moment from 'moment'
import { useToast } from 'vue-toastification'
import { Tooltip } from 'bootstrap/js/dist/tooltip'

const toast = useToast()

export default {
    mounted(){
        setTimeout(() => {
            if (this.userStore.authStatus) this.loadEditForm()
        }, 3000)
        
    },
    computed: {
        ...mapStores(userStore, appStore)
    },
    data: () => ({
        moment: moment,
        form: 'login',
        resetPassword: false,
        showPass: false,
        loginData: {
            email: null,
            password: null
        },
        registerData: {
            name: null,
            tel: null,
            email: null
        },
        loginSchema: object({
            email: string().required('Обязательное поле').email('Некорректный email'),
            password: string().required('Обязательное поле').min(6, "Минимум 6 символов")
        }),
        registerSchema: object({
            name: string().required('Обязатель��ое поле').min(3, "Минимум 3 символа").max(255, 'Максимум 255 символов'),
            tel: string().required('Обязательное поле').min(18, 'Номер 18 символов').max(18, 'Номер 18 символов'),
            email: string().required('Обязательное поле').email('Невалидный email адрес').max(255, 'Максимум 255 символов')
        }),
        // edit group
        editForm: {
            name: null,
            tel: null,
            email: null,
            dob: null
        },
        editSchema: object({
            name: string().required('Обязательное поле').min(3, "Минимум 3 символа").max(255, 'Максимум 255 символов'),
            tel: string().required('Обязательное поле').min(18, 'Номер 18 символов').max(18, 'Номер 18 символов'),
            email: string().required('Обязательное поле').email('Невалидный email адрес').max(255, 'Максимум 255 символов'),
            dob: string().required('Обязательное поле').transform(val => moment(val).toISOString())
        }),
        loginErrorBag: {},
        registerErrorBag: {},
        editErrorBag: {},
    }),
    methods: {
        validate(form) {
            if (form == 'login') {
                this.loginSchema.validate(this.loginData, { abortEarly: false })
                    .then(res => {
                        this.loginErrorBag = {}
                        this.userStore.auth(res)
                        this.loginData.password = null
                    })
                    .catch(err => {
                        this.loginErrorBag = {}
                        err.inner.forEach(e => { 
                            this.loginErrorBag[e.path] = e.message
                        })
                    })
                    .finally(() => {
                        this.loading = false; // Stop loading
                    });
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
                        err.inner.forEach(e => {
                            this.registerErrorBag[e.path] = e.message
                        })
                    })
            }
            if (form == 'edit') {
                this.editSchema.validate(this.editForm, { abortEarly: false })
                    .then(res => {
                        this.editErrorBag = {}
                        this.userStore.updateUser(res)
                    })
                    .catch(err => {
                        this.editErrorBag = {}
                        err.inner.forEach(e => {
                            this.editErrorBag[e.path] = e.message
                        })
                    })
            }
        },
        loadEditForm(){
            this.editForm.name = this.userStore.userData.name
            this.editForm.tel = this.userStore.userData.tel
            this.editForm.email = this.userStore.userData.email
            this.editForm.dob = moment(this.userStore.userData.dob).format('YYYY-MM-DD')
        },
        resetPass(email){
            object({ email: string().required('Введите Email').email('Некорретный email адрес')}).validate(this.loginData, { abortEarly: false })
            .then( res => {
                this.loginErrorBag = {}
                this.userStore.resetPassword(res.email)
            })
            .catch( err => {
            err.inner.forEach( e => { 
                this.loginErrorBag[e.path] = e.message 
                })                    
            })
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
                    <transition
                        enter-active-class="animate__animated animate__fadeIn"
                        leave-active-class="animate__animated animate__fadeOut"
                        mode="out-in"
                    >
                        <form @submit.prevent="validate('login')" v-if="form == 'login'">
                            <div class="form-group">
                                <div class="d-flex mb-2">
                                    <label for="email">Email адрес</label>
                                    <error-label :errorBag="loginErrorBag" name="email"></error-label>
                                </div>
                                <input type="text" name="email" id="email" class="form-control" placeholder="example@gangsters.pro " v-model="loginData.email">
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
                            <div class="form-group mt-4 d-flex align-items-center">
                                <button class="btn rounded" type="button" disabled v-if="userStore.loading">
                                    <span class="spinner-border spinner-border-sm" aria-hidden="true" style="margin-right: 6px;"></span>
                                    <span role="status">Загрузка...</span>
                                </button>
                                <button type="submit" class="btn rounded" :disabled="loading" v-else>
                                    <span >Отправить</span>
                                </button>
                                <a 
                                    href="" 
                                    @click.prevent="resetPass(loginData.email)" 
                                    class="text-md-dark text-light mx-3 text-primary"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-original-title="На ваш email отправлен код для восстановления пароля"
                                > Забыли пароль?</a>
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
                                <button class="btn rounded" type="button" disabled v-if="userStore.loading">
                                    <span class="spinner-border spinner-border-sm" aria-hidden="true" style="margin-right: 6px;"></span>
                                    <span role="status">Загрузка...</span>
                                </button>
                                <button type="submit" class="btn rounded btn-light" :disabled="loading" v-else>
                                    <span>Отправить</span>
                                </button>
                            </div>
                        </form>
                    </transition>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="row mb-3">
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between ">
                        <h5 class="mb-0">Привет, {{ userStore.userData.name }}</h5>
                        <button class="btn btn-danger btn-sm rounded logout" type="button" @click="userStore.logout()" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Выйти из аккаунта" style="background-color: #dc3545;">
                            <span style="margin-right: 6px;">Выйти</span>
                            <i class="fa fa-sign-out"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col d-flex align-items-center">
                    <div class="coin" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Количество койнов на счету">
                        <img src="/images/coin.png" alt="Кол-во койнов">
                        <span>Койнов: {{ userStore.userData.coins }}</span>
                    </div>
                    <p class="mb-0">
                        Копи койны и оплачивай ими покупки!
                    </p>
                </div>
            </div>
        </div>
    </transition>    
</template>

<style lang="sass" scoped>    
.coin
    background: $color-main
    margin-right: 12px
    width: fit-content
    color: #fff
    padding: 6px 15px
    border-radius: 16px
    display: flex
    align-items: center
    img
        width: 20px
        height: 20px
        margin-right: 8px
    span
        font-weight: 500
.btn
    background: $color-main
    color: #fff    
.btn-group
    button
        background: unset
        color: $color-main
        &:hover
            background: $color-main
            color: #fff
        &.active
            background: $color-main
            color: #fff
nav
    
    ul
        
        li
            
            a
                &::before
                    content: '|'
                    margin-right: 5px
.btn-group
    width: 100%
    button
        padding: 6px 15px !important
        border: 1px solid $color-main
        &:first-child
            border-radius: 16px 0 0 16px !important
        &:last-child
            border-radius: 0 16px 16px 0 !important
    .active
        background: $color-main
        color: #fff
.nav-link
    transition: color 0.3s ease
    &:hover
        color: orange
</style>