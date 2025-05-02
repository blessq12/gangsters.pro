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
            showPass: false,
        };
    },
};
</script>

<template>
    <form @submit.prevent="validate('login')" class="space-y-4">
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
                    class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                    placeholder="example@gangsters.pro"
                    v-model="data.email"
                />
                <div
                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                >
                    <span class="text-gray-500">@</span>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <label
                for="password"
                class="block text-sm font-medium text-gray-700"
                >Пароль</label
            >
            <div class="relative rounded-lg">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                    <i class="mdi mdi-lock text-gray-400"></i>
                </div>
                <input
                    :type="showPass ? 'text' : 'password'"
                    name="password"
                    id="password"
                    class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                    v-model="data.password"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                    @click="showPass = !showPass"
                >
                    <i
                        :class="`mdi ${
                            showPass ? 'mdi-eye-off' : 'mdi-eye'
                        } text-gray-400 hover:text-gray-600`"
                    ></i>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <button
                type="submit"
                class="px-4 py-2 bg-black/90 text-white rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 flex items-center gap-2"
            >
                <i class="mdi mdi-login"></i>
                <span>Войти</span>
            </button>
            <button
                type="button"
                class="text-sm text-gray-500 hover:text-gray-700"
                @click="$emit('forgot')"
            >
                Забыли пароль?
            </button>
        </div>
    </form>
</template>

<style scoped lang="sass">
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
