<script>
import gsap from "gsap";
import { mapStores } from "pinia";
import { object, string } from "yup";
import { appStore } from "../../stores/appStorage";
import { userStore } from "../../stores/userStore";

export default {
    mounted() {
        // this.initializeAnimations();
    },
    computed: {
        ...mapStores(userStore, appStore),
    },
    data() {
        return {
            form: "login",
            loginData: this.getLoginData(),
            registerData: this.getRegisterData(),
            loginSchema: this.getLoginSchema(),
            registerSchema: this.getRegisterSchema(),
            errors: [],
        };
    },
    methods: {
        getLoginData() {
            return {
                email: "",
                password: "",
            };
        },
        getRegisterData() {
            return {
                name: "",
                tel: "",
                email: "",
                password: "",
                password_confirmation: "",
            };
        },
        getLoginSchema() {
            return object({
                email: string()
                    .required("Обязательное поле")
                    .email("Некорректный email"),
                password: string()
                    .required("Обязательное поле")
                    .min(6, "Минимум 6 символов"),
            });
        },
        getRegisterSchema() {
            return object({
                name: string()
                    .required("Обязательное поле")
                    .min(3, "Минимум 3 символа")
                    .max(255, "Максимум 255 символов"),
                tel: string()
                    .required("Обязательное поле")
                    .min(18, "Номер 18 символов")
                    .max(18, "Номер 18 символов"),
                email: string()
                    .required("Обязательное поле")
                    .email("Некорректный email")
                    .max(255, "Максимум 255 символов"),
                password: string()
                    .required("Обязательное поле")
                    .min(6, "Минимум 6 символов")
                    .max(255, "Максимум 255 символов"),
                password_confirmation: string()
                    .required("Обязательное поле")
                    .min(6, "Минимум 6 символов")
                    .max(255, "Максимум 255 символов"),
            });
        },
        validate(form) {
            let schema =
                    form == "login" ? this.loginSchema : this.registerSchema,
                data = form == "login" ? this.loginData : this.registerData;

            schema
                .validate(data, { abortEarly: false })
                .then((res) => {
                    this.errors = [];
                    this.updateInputs();
                    this.userStore.auth(form, data);
                })
                .catch((err) => {
                    this.errors = [];
                    if (err.inner) {
                        err.inner.forEach(
                            (e) => (this.errors[e.path] = e.message)
                        );
                    } else {
                        console.error("Validation error:", err);
                    }
                });
        },
        updateInputs() {
            let inputs = document.querySelectorAll("form input");
            inputs.forEach((input) => {
                if (this.errors[input.name]) {
                    input.classList.add("border-red-500");
                    input.classList.remove("border-gray-300");
                } else {
                    input.classList.remove("border-red-500");
                    input.classList.add("border-gray-300");
                }
            });
        },
        changeForm(form) {
            gsap.to(this.$el.querySelector(".auth-forms > div"), {
                opacity: 0,
                y: 20,
                duration: 0.3,
                ease: "power3.in",
                onComplete: () => {
                    this.form = form;
                    gsap.to(this.$el.querySelector(".auth-forms > div"), {
                        opacity: 1,
                        y: 0,
                        duration: 0.3,
                        ease: "power3.out",
                    });
                },
            });
        },
    },
    watch: {
        errors: {
            handler(newVal) {
                this.updateInputs();
            },
            deep: true,
        },
    },
};
</script>

<template>
    <div class="p-4 bg-gray-50">
        <div v-if="!userStore.authStatus" class="space-y-6">
            <div class="grid grid-cols-2 gap-2">
                <button
                    :class="`rounded-xl py-3 transition-colors ${
                        form === 'login'
                            ? 'bg-black text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`"
                    @click="changeForm('login')"
                >
                    <span class="flex items-center justify-center gap-2">
                        <i class="mdi mdi-login"></i>
                        Авторизация
                    </span>
                </button>
                <button
                    :class="`rounded-xl py-3 transition-colors ${
                        form === 'register'
                            ? 'bg-black text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`"
                    @click="changeForm('register')"
                >
                    <span class="flex items-center justify-center gap-2">
                        <i class="mdi mdi-account-plus"></i>
                        Регистрация
                    </span>
                </button>
            </div>

            <div class="auth-form relative pb-12">
                <user-login-form
                    v-if="form === 'login'"
                    key="login"
                    @validate="validate('login')"
                    :data="loginData"
                    :schema="loginSchema"
                    :validate="validate"
                    @forgot="form = 'forgot'"
                    class="w-full"
                />
                <user-register-form
                    v-if="form === 'register'"
                    key="register"
                    @validate="validate('register')"
                    :data="registerData"
                    :schema="registerSchema"
                    :validate="validate"
                    :form="form"
                    class="w-full"
                />
                <forgot-password
                    v-if="form === 'forgot'"
                    key="forgot"
                    class="w-full"
                />
            </div>
        </div>
        <div v-else>
            <user-profile />
        </div>
    </div>
</template>

<style lang="sass" scoped>
.auth-forms
    min-height: 50vh
    position: relative

    > div
        position: absolute
        width: 100%
        will-change: transform, opacity
        opacity: 0
</style>
