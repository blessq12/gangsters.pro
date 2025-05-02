<script>
import { useToast } from "vue-toastification";
import { object } from "yup";
const toast = useToast();

export default {
    props: {
        token: String,
    },
    data() {
        return {
            loading: false,
            data: {
                password: "",
                password_confirmation: "",
                privacy: true,
                passwordVisible: false,
            },
            schema: object({
                //
            }),
        };
    },
    methods: {
        resetPassword() {
            this.loading = true;
            let data = {
                token: this.token,
                password: this.data.password,
            };
            axios
                .post("/api/auth/change-password", data)
                .then((response) => {
                    toast.success(response.data.message);
                    window.location.href = "/";
                })
                .catch((error) => {
                    toast.error(error.response.data.message);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>

<template>
    <div class="min-h-[80vh] flex items-center justify-center">
        <div class="w-full max-w-md px-4">
            <div class="bg-white rounded-lg shadow-lg p-6 space-y-6">
                <div>
                    <h4 class="text-xl font-semibold">Сброс пароля</h4>
                    <div class="mt-2 space-y-2 text-sm text-gray-600">
                        <p>
                            Пароль должен содержать не менее 6 символов, включая
                            заглавные и строчные буквы, цифры и специальные
                            символы.
                        </p>
                        <p>
                            После сброса пароля вы сможете войти в свою учетную
                            запись с новым паролем.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="resetPassword" class="space-y-4">
                    <div class="space-y-4">
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <i class="mdi mdi-lock text-gray-400"></i>
                            </div>
                            <input
                                :type="
                                    data.passwordVisible ? 'text' : 'password'
                                "
                                class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Пароль"
                                v-model="data.password"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                @click="
                                    data.passwordVisible = !data.passwordVisible
                                "
                            >
                                <i
                                    :class="`mdi ${
                                        data.passwordVisible
                                            ? 'mdi-eye-off'
                                            : 'mdi-eye'
                                    } text-gray-400 hover:text-gray-600`"
                                ></i>
                            </button>
                        </div>

                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <i class="mdi mdi-lock-check text-gray-400"></i>
                            </div>
                            <input
                                :type="
                                    data.passwordVisible ? 'text' : 'password'
                                "
                                class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Подтвердите пароль"
                                v-model="data.password_confirmation"
                            />
                        </div>
                    </div>

                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            class="form-checkbox h-4 w-4 text-primary-600 transition duration-150 ease-in-out"
                            v-model="data.privacy"
                        />
                        <span class="ml-2 text-sm text-gray-600">
                            Я прочитал и принимаю
                            <a
                                href="/privacy"
                                target="_blank"
                                class="text-primary-600 hover:text-primary-700"
                                >условия использования</a
                            >
                        </span>
                    </label>

                    <div class="flex items-center space-x-4">
                        <button
                            type="submit"
                            class="flex-1 flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="
                                !data.privacy ||
                                !data.password ||
                                !data.password_confirmation ||
                                data.password !== data.password_confirmation ||
                                loading
                            "
                        >
                            <template v-if="loading">
                                <svg
                                    class="animate-spin h-5 w-5 text-white"
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
                            </template>
                            <template v-else>
                                <i class="mdi mdi-lock-reset mr-2"></i>
                                Сбросить пароль
                            </template>
                        </button>
                        <a
                            href="/"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 focus:outline-none"
                        >
                            Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped lang="sass"></style>
