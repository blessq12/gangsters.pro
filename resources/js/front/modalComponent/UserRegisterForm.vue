<script>
export default {
    props: {
        data: {
            type: Object,
            required: true,
        },
        validate: {
            type: Function,
            required: true,
        },
    },
    data() {
        return {
            showPassword: false,
        };
    },
    watch: {
        "data.tel"(newVal) {
            if (newVal && typeof newVal === "string") {
                // Проверяем, если первая цифра после +7 ( это 7 или 8, удаляем её
                if (newVal.startsWith("+7 (7") || newVal.startsWith("+7 (8")) {
                    // Удаляем первую цифру после +7 (
                    this.data.tel = newVal.replace(/^\+7 \(([78])/, "+7 (");
                }
            }
        },
    },
};
</script>

<template>
    <form @submit.prevent="validate('register')" class="space-y-4">
        <div class="space-y-2">
            <label for="name" class="block text-sm font-medium text-gray-700"
                >Имя</label
            >
            <div class="relative rounded-lg">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                    <i class="mdi mdi-account text-gray-400"></i>
                </div>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                    v-model="data.name"
                />
            </div>
        </div>

        <div class="space-y-2">
            <label for="tel" class="block text-sm font-medium text-gray-700"
                >Номер телефона</label
            >
            <div class="relative rounded-lg">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                    <i class="mdi mdi-phone text-gray-400"></i>
                </div>
                <input
                    type="text"
                    name="tel"
                    id="tel"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                    v-maska
                    data-maska="+7 (###) ###-##-##"
                    placeholder="+7 "
                    v-model="data.tel"
                />
            </div>
        </div>

        <div class="space-y-2">
            <label for="email" class="block text-sm font-medium text-gray-700"
                >Email адрес</label
            >
            <div class="relative rounded-lg">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                    <i class="mdi mdi-email text-gray-400"></i>
                </div>
                <input
                    type="text"
                    name="email"
                    id="email"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                    v-model="data.email"
                />
            </div>
        </div>

        <div class="space-y-2">
            <label
                for="password"
                class="block text-sm font-medium text-gray-700"
            >
                Пароль
                <span class="text-sm text-gray-500 ml-1"
                    >Минимум 6 символов</span
                >
            </label>
            <div class="relative rounded-lg">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                    <i class="mdi mdi-lock text-gray-400"></i>
                </div>
                <input
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    id="password"
                    class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                    v-model="data.password"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                    @click="showPassword = !showPassword"
                >
                    <i
                        :class="`mdi ${
                            showPassword ? 'mdi-eye-off' : 'mdi-eye'
                        } text-gray-400 hover:text-gray-600`"
                    ></i>
                </button>
            </div>
        </div>

        <div class="space-y-2">
            <label
                for="password_confirmation"
                class="block text-sm font-medium text-gray-700"
                >Повторите пароль</label
            >
            <div class="relative rounded-lg">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                    <i class="mdi mdi-lock-check text-gray-400"></i>
                </div>
                <input
                    :type="showPassword ? 'text' : 'password'"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                    v-model="data.password_confirmation"
                />
            </div>
        </div>

        <div class="flex items-center justify-between">
            <p
                class="text-sm text-red-600"
                v-if="data.password !== data.password_confirmation"
            >
                Пароли не совпадают
            </p>
            <label class="inline-flex items-center">
                <input
                    type="checkbox"
                    class="form-checkbox h-4 w-4 text-primary-600 transition duration-150 ease-in-out"
                    v-model="showPassword"
                />
                <span class="ml-2 text-sm text-gray-600">Показать пароль</span>
            </label>
        </div>

        <div class="mt-6">
            <button
                type="submit"
                class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-black hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
                <i class="mdi mdi-account-plus mr-2"></i>
                Зарегистрироваться
            </button>
        </div>
    </form>
</template>

<style lang="sass" scoped>
.input-group-text
    background-color: #dedede
    color: #fff
.btn
    background: #dedede
    color: #fff
    &:hover
        background: #2e2e2e
        color: #fff
</style>
