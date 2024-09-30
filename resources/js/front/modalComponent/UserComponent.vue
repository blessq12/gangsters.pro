<script>
import { object, string } from 'yup'
import { userStore } from '../../stores/userStore'
import { appStore } from '../../stores/appStorage'
import { mapStores } from 'pinia'

export default {
    mounted() {
        //
    },
    computed: {
        ...mapStores(userStore, appStore)
    },
    data() {
        return {
            form: 'login',
            loginData: this.getLoginData(),
            registerData: this.getRegisterData(),
            loginSchema: this.getLoginSchema(),
            registerSchema: this.getRegisterSchema(),
            errors: [],
        }
    },
    watch: {
        errors:{
            handler(newVal) {
                this.updateInputs()
            },
            deep: true
        },
    },
    methods: {
        getLoginData() {
            return {
                email: '',
                password: ''
            }
        },
        getRegisterData() {
            return {
                name: '',
                tel: '',
                email: ''
            }
        },
        getLoginSchema() {
            return object({
                email: string().required('Обязательное поле').email('Некорректный email'),
                password: string().required('Обязательное поле').min(6, "Минимум 6 символов")
            })
        },
        getRegisterSchema() {
            return object({
                name: string().required('Обязательное поле').min(3, "Минимум 3 символа").max(255, 'Максимум 255 символов'),
                tel: string().required('Обязательное поле').min(18, 'Номер 18 символов').max(18, 'Номер 18 символов'),
                email: string().required('Обязательное поле').email('Некорректный email').max(255, 'Максимум 255 символов')
            })
        },
        validate(form) {
            let schema = form == 'login' ? this.loginSchema : this.registerSchema
            let data = form == 'login' ? this.loginData : this.registerData

            schema.validate(data, { abortEarly: false })
                .then(res => {
                    this.errors = []
                    this.updateInputs()
                    console.log(res)
                })
                .catch(err => {
                    this.errors = err.inner.map(error => ({
                        path: error.path, 
                        message: error.message
                    }));
                })
        },
        updateInputs() {
            let inputs = document.querySelectorAll('form input');
            inputs.forEach(input => {
                if (this.errors.some(error => error.path === input.name)) {
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
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
            <!-- form switch -->
            <div class="row mb-4">
                <div class="col">
                    <div class="btn-group w-100">
                        <button :class="` btn ${form == 'login' ? 'active' : ''}`" type="button" @click="form = 'login'">
                            Авторизация
                        </button>
                        <button :class="` btn ${form == 'register' ? 'active' : ''}`" type="button" @click="form = 'register'">
                            Регистрация
                        </button>
                    </div>
                </div>
            </div>
            <!-- end form switch -->

            <!-- form components panel -->
            <div class="row">
                <div class="col">
                    <transition
                        enter-active-class="animate__animated animate__fadeIn"
                        leave-active-class="animate__animated animate__fadeOut"
                        mode="out-in"
                    >
                        <user-login-form 
                            @validate="validate('login')"
                            :data="loginData"
                            :schema="loginSchema"
                            :validate="validate"
                            v-if="form == 'login'" 
                        />
                        <user-register-form 
                            @validate="validate('register')"
                            :data="registerData"
                            :schema="registerSchema"
                            :validate="validate"
                            v-else
                        />
                    </transition>
                    
                </div>
            </div>
            <!-- end form components panel -->
        </div>
        <div v-else>
            <user-profile/>
        </div>
        
    </transition>
</template>

<style lang="sass">

</style>