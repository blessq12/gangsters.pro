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
                email: '',
                password: '',
                password_confirmation: ''
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
                email: string().required('Обязательное поле').email('Некорректный email').max(255, 'Максимум 255 символов'),
                password: string().required('Обязательное поле').min(6, "Минимум 6 символов").max(255, 'Максимум 255 символов'),
                password_confirmation: string().required('Обязательное поле').min(6, "Минимум 6 символов").max(255, 'Максимум 255 символов')
            })
        },
        validate(form) {
            let schema = form == 'login' ? this.loginSchema : this.registerSchema,
                data = form == 'login' ? this.loginData : this.registerData

            schema.validate(data, { abortEarly: false })
                .then(res => {
                    this.errors = []
                    this.updateInputs()
                    this.userStore.auth(form, data)
                })
                .catch(err => {
                    this.errors = []
                    if (err.inner) {
                        err.inner.forEach(e => (
                            this.errors[e.path] = e.message
                    ));
                    } else {
                        console.error("Validation error:", err); // Log the error if inner is undefined
                    }
                })
        },
        updateInputs() {
            let inputs = document.querySelectorAll('form input');
            inputs.forEach(input => {
                if (this.errors[input.name]) {
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
        },
        changeForm(form) {
            //
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
            <div class="row row-cols-1" >
                <div class="col">
                    <div class="card card-coin mb-4 border-0">
                        <div class="card-body p-0">
                            <small>
                                <h5 class="card-title">Получайте кэшбэк с Gangster Coin!</h5>
                                <p class="card-text">
                                    <strong>Присоединяйтесь к нашей программе кэшбека!</strong> <br />
                                    Чтобы начать получать кэшбэк, вам необходимо зарегистрироваться и совершать заказы. <br />
                                    Не упустите возможность экономить на своих покупках!
                                </p>
                            </small>
                        </div>
                    </div>

                    <transition-group
                        enter-active-class="animate__animated animate__fadeIn"
                        leave-active-class="animate__animated animate__fadeOut"
                        class="position-relative h-100 d-block"
                        tag="div"
                        style="min-height: 50vh;"
                    >
                        <user-login-form 
                            @validate="validate('login')"
                            :data="loginData"
                            :schema="loginSchema"
                            :validate="validate"
                            v-if="form == 'login'" 
                            key="login"
                            @forgot="form = 'forgot'"
                            class="position-absolute w-100"
                        />
                        <user-register-form 
                            @validate="validate('register')"
                            :data="registerData"
                            :schema="registerSchema"
                            :validate="validate"
                            :form="form"
                            v-if="form == 'register'"
                            key="register"
                            class="position-absolute w-100"
                        />
                        <forgot-password
                            v-if="form == 'forgot'"
                            key="forgot"
                            class="position-absolute w-100"
                        />
                    </transition-group>
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
.card-coin
    color: $color-main
    border-radius: 16px
</style>