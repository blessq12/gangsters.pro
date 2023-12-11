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
        registerSchema: yup.object({}),
        formErrors:{}
    }),
    methods:{
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
            if ( form == 'register' ){}
        }
    },
    watch:{
        formData:{
            deep: true,
            handler(val){
                console.log(val)
            }
        }
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
            <button type="submit" class="btn btn-primary">Авторизоваться</button>
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
                <input type="text" name="password" id="password" class="form-control" autocomplete="additional-name" v-model="formData.register.password">
            </div>
            <button type="submit" class="btn btn-primary">Регистрация</button>
        </form>
    </transition>
</template>

<style lang="sass">

</style>