<script>
import { object, string } from "yup";
export default {
    props: {
        //
    },
    data() {
        return {
            email: "",
            schema: object().shape({
                email: string().email().required(),
            }),
            errors: [],
            isLoading: false,
            issetEmail: false,
            finish: false,
            message: "",
        };
    },
    methods: {
        send() {
            this.schema
                .validate({ email: this.email }, { abortEarly: false })
                .then(() => {
                    this.errors = [];
                    this.isLoading = true;
                    axios
                        .post("/api/auth/forgot-password", {
                            email: this.email,
                        })
                        .then((res) => {
                            this.issetEmail = true;
                            this.finish = true;
                            this.message = res.data.message;
                        })
                        .catch((err) => {
                            this.issetEmail = false;
                            this.finish = true;
                            this.message = err.response.data.message;
                            console.log(err.response.data);
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                })
                .catch((err) => {
                    this.errors = [];
                    err.inner.forEach((error) => {
                        this.errors[error.path] = error.message;
                    });
                });
        },
        updateInputs() {
            let inputs =
                this.$refs.forgotPasswordForm.querySelectorAll("input");
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
    },
    mounted() {},
    watch: {
        errors(val) {
            this.updateInputs();
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-xl font-semibold">Восстановление пароля</h3>
            <p class="text-gray-600 mt-2">
                Введите ваш email, чтобы получить ссылку для восстановления
                пароля
            </p>
        </div>

        <form @submit.prevent="send" ref="forgotPasswordForm" class="space-y-4">
            <div class="relative rounded-lg">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                    <i class="mdi mdi-email text-gray-400"></i>
                </div>
                <input
                    type="text"
                    class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                    name="email"
                    placeholder="Электронная почта"
                    v-model="email"
                />
                <div
                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                >
                    <span class="text-gray-500">@</span>
                </div>
            </div>

            <div v-if="isLoading" class="flex items-center space-x-2">
                <svg
                    class="animate-spin h-5 w-5 text-primary-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
                <span v-if="!finish" class="text-gray-600"
                    >Поиск учетной записи ...</span
                >
            </div>

            <p
                v-if="finish"
                :class="issetEmail ? 'text-green-600' : 'text-red-600'"
            >
                {{ message }}
            </p>

            <button
                type="submit"
                class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-black/90 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="isLoading"
            >
                <i class="mdi mdi-email-send mr-2"></i>
                Отправить
            </button>
        </form>
    </div>
</template>

<style scoped lang="sass">

.input-group-text
    background-color: #dedede
    color: #fff
.btn
    background-color: #dedede
    color: #fff
</style>
