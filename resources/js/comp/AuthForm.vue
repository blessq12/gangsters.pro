<script>

import { mapStores } from 'pinia';
import { userStore } from '../stores/userStore';
import * as yup from 'yup';

export default{
    computed:{
        ...mapStores(userStore)
    },
    data:()=>({
        form: 'login',
        formData:{
            login:{},
            register:{}
        },
        loginSchema: yup.object({
            email: yup.string().required('Обязательное поле').email('Невалидный email'),
            password: yup.string().required('Обязательное поле').min(6, 'Минимум 6 символов')
        }),
        registerSchema: yup.object({
            email: yup.string().required('Обязательное поле').email('Невалидный email'),
            name: yup.string().required('Обязательное поле').min( 3, 'Минимум 3 символа'),
            tel: yup.string().required('Обязательное поле').min(18, 'Некорретный номер'),
            password: yup.string().required('Обязательное поле').min(6, 'Минимум 6 символов')
        }),
        formErrors:{}
    }),
    methods:{
        forgetPassword(event){
            // event.target[0].value
            yup.object({forget: yup.string().required('Обязательное поле').email('Невалидный адрес')})
            .validate({ forget: event.target[0].value})
                .then( res => {
                    this.formErrors = {}
                    console.log(res)
                } )
                .catch( err => {
                    this.formErrors[err.path] = err.message 
                    })
        },
        validate(form){
            if ( form == 'login' ){
                this.loginSchema.validate(this.formData.login, { abortEarly: false })
                .then(res => {
                    this.formErrors = {}
                    console.log(res)
                })
                .catch(err => {
                    this.formErrors = {}
                    err.inner.forEach( e => {
                        this.formErrors[e.path] = e.message
                    } )
                })
            }
            if ( form == 'register' ){
                this.registerSchema.validate(this.formData.register, { abortEarly: false })
                .then( res => {
                    this.formErrors = {}
                    console.log(res)
                } )
                .catch( err => {
                    this.formErrors = {}
                    err.inner.forEach( e => 
                        this.formErrors[e.path] = e.message
                    )
                } )
            }
        }
    },
    watch:{
        form(val){
            this.formErrors = {}
            this.formData.login.password = null
            this.formData.register.password = null
        },
    }
}
</script>

<template>
<div class="d-flex justify-content-center mb-4">
    <div class="btn-group">
        <button 
            type="button" 
            class="btn btn-outline-primary"
            @click="form = 'login'"
            :class="form == 'login' ? 'active' : ''"
        >Авторизация</button>
        <button 
            type="button" 
            class="btn btn-outline-primary"
            @click="form = 'register'"
            :class="form == 'register' ? 'active' : ''"
        >Регистрация</button>
    </div> 
</div>
<transition
    enter-active-class="animate__animated animate__fadeIn"
    leave-active-class="animate__animated animate__fadeOut"
    mode="out-in"
>
        <!-- login form -->
        <form v-if="form == 'login'" key="login" @submit.prevent="validate('login')">
            <div class="form-group mb-2">
                <label for="email">
                    Введите Email
                    <error-message name="email" :errors="formErrors" class="text-danger mx-2"></error-message>
                </label>
                <input type="text" name="email" id="email" class="form-control" autocomplete="email" v-model="formData.login.email">
            </div>
            <div class="form-group mb-4">
                <label for="password">
                    Введите пароль
                    <error-message name="password" :errors="formErrors" class="text-danger mx-2"></error-message>
                </label>
                <div class="input-group">
                    <input type="password" ref="pass" name="password" id="password" class="form-control" autocomplete="current-password" v-model="formData.login.password">
                    <span class="input-group-text cursor-pointer" @click="$refs.pass.type == 'password' ? $refs.pass.type = 'text' : $refs.pass.type = 'password'">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button type="submit" class="btn btn-primary">Авторизоваться</button>
                <button class="btn text-muted mx-4" @click="form = 'forget'">Забыли пароль?</button>
            </div>
        </form>
        <!-- register form -->
        <form v-else-if="form == 'register'" key="register" @submit.prevent="validate('register')" novalidate>
        
            <div class="form-group mb-2">
                <label for="name">Имя <error-message name="name" :errors="formErrors" class="text-danger mx-2"></error-message></label>
                <input type="text" name="name" id="name" class="form-control" autocomplete="additional-name" v-model="formData.register.name">
            </div>
            <div class="form-group mb-2">
                <label for="email">Email <error-message name="email" :errors="formErrors" class="text-danger mx-2"></error-message></label>
                <input type="text" name="email" id="email" class="form-control" autocomplete="additional-name" v-model="formData.register.email">
            </div>
            <div class="form-group mb-2">
                <label for="tel">Телефон <error-message name="tel" :errors="formErrors" class="text-danger mx-2"></error-message></label>
                <input type="text" name="tel" id="tel" class="form-control" autocomplete="additional-name" v-model="formData.register.tel">
            </div>
            <div class="form-group mb-4">
                <label for="password">Пароль <error-message name="password" :errors="formErrors" class="text-danger mx-2"></error-message></label>
                <div class="input-group">
                    <input type="password" ref="regPass" name="password" id="password" class="form-control" autocomplete="additional-name" v-model="formData.register.password">
                    <span class="input-group-text cursor-pointer" @click="$refs.regPass.type == 'password' ? $refs.regPass.type = 'text' : $refs.regPass.type = 'password'">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Регистрация</button>
        </form>
        <form v-else-if="form == 'forget'" @submit.prevent="forgetPassword">
            <p>Для восстановления пароля введите Email учетной записи. Мы отправим ссылку на восстановление пароля по адресу в учетной записи.</p>
            <div class="form-group mb-4">
                <label for="login">Введите Email <error-message name="forget" :errors="formErrors" class="text-danger mx-2"></error-message> </label>
                <input type="text" name="login" id="login" class="form-control" autocomplete="username">
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary w-100">Отправить</button>
            </div>
        </form>
    </transition>
</template>

<style lang="sass">

</style>