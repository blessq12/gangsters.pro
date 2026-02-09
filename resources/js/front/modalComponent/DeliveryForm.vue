<script>
import { mapStores } from "pinia";
import { userStore } from "../../stores/userStore";
export default {
    computed: {
        ...mapStores(userStore),
    },
    props: {
        formData: {
            type: Object,
            required: true,
        },
        validatorBag: {
            type: Object,
            required: true,
        },
        validate: {
            type: Function,
            required: true,
        },
        schema: {
            type: Object,
            required: true,
        },
    },
    watch: {
        "formData.tel"(newVal) {
            if (newVal && typeof newVal === "string") {
                // Проверяем, если первая цифра после +7 ( это 7 или 8, удаляем её
                if (newVal.startsWith("+7 (7") || newVal.startsWith("+7 (8")) {
                    // Удаляем первую цифру после +7 (
                    this.formData.tel = newVal.replace(/^\+7 \(([78])/, "+7 (");
                }
            }
        },
    },
    expose: [],
};
</script>

<template>
    <form ref="delivery" class="bg-white rounded-lg shadow-md p-4 space-y-6">
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                Личные данные
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label
                        for="name"
                        class="block text-sm font-medium text-gray-700"
                        >Ваше Имя</label
                    >
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                        v-model="formData.name"
                        placeholder="Введите ваше имя"
                    />
                </div>
                <div class="space-y-2">
                    <label
                        for="tel"
                        class="block text-sm font-medium text-gray-700"
                        >Номер телефона</label
                    >
                    <input
                        type="text"
                        name="tel"
                        id="tel"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                        v-maska
                        data-maska="+7 (###) ###-##-##"
                        v-model="formData.tel"
                        placeholder="+7 (___) ___-__-__"
                    />
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                Адрес доставки
            </h3>
            <div class="space-y-4">
                <div>
                    <label
                        for="street"
                        class="block text-sm font-medium text-gray-700"
                        >Улица</label
                    >
                    <input
                        type="text"
                        name="street"
                        id="street"
                        class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                        v-model="formData.street"
                        placeholder="Введите название улицы"
                    />
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label
                            for="house"
                            class="block text-sm font-medium text-gray-700"
                            >Дом</label
                        >
                        <input
                            type="text"
                            name="house"
                            id="house"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                            v-model="formData.house"
                            placeholder="№"
                        />
                    </div>
                    <div>
                        <label
                            for="building"
                            class="block text-sm font-medium text-gray-700"
                            >Строение</label
                        >
                        <input
                            type="text"
                            name="building"
                            id="building"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                            v-model="formData.building"
                            placeholder="Корпус"
                        />
                    </div>
                    <div>
                        <label
                            for="apartment"
                            class="block text-sm font-medium text-gray-700"
                            >Квартира</label
                        >
                        <input
                            type="text"
                            name="apartment"
                            id="apartment"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                            v-model="formData.apartment"
                            placeholder="№"
                        />
                    </div>
                    <div>
                        <label
                            for="staircase"
                            class="block text-sm font-medium text-gray-700"
                            >Подъезд</label
                        >
                        <input
                            type="text"
                            name="staircase"
                            id="staircase"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                            v-model="formData.staircase"
                            v-maska
                            data-maska="#####"
                            placeholder="№"
                        />
                    </div>
                    <div>
                        <label
                            for="floor"
                            class="block text-sm font-medium text-gray-700"
                            >Этаж</label
                        >
                        <input
                            type="text"
                            name="floor"
                            id="floor"
                            class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                            v-model="formData.floor"
                            v-maska
                            data-maska="#####"
                            placeholder="№"
                        />
                    </div>
                </div>
                <div v-if="userStore.authStatus">
                    <div class="bg-green-500/10 px-3 py-2 rounded-lg">
                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                name="saveAddress"
                                id="saveAddress"
                                class="mt-1 px-4 py-2 w-5 h-5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                                v-model="formData.saveAddress"
                                :checked="formData.saveAddress"
                            />
                            <label
                                for="saveAddress"
                                class="block text-sm font-medium text-gray-700"
                                >Сохранить адрес</label
                            >
                        </div>

                        <p class="text-xs mt-1">
                            Сохранить адрес в профиле для следующих заказов
                        </p>
                    </div>
                </div>
                <div>
                    <label
                        for="personQty"
                        class="block text-sm font-medium text-gray-700"
                        >Количество персон</label
                    >
                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50 disabled:bg-gray-400 transition duration-200"
                            @click="formData.personQty--"
                            :disabled="formData.personQty < 2"
                        >
                            -
                        </button>
                        <input
                            type="text"
                            name="personQty"
                            id="personQty"
                            class="w-20 px-4 py-2 text-center rounded-lg border border-gray-300 font-bold"
                            v-model="formData.personQty"
                            min="1"
                            max="10"
                        />
                        <button
                            type="button"
                            class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition duration-200"
                            @click="formData.personQty++"
                        >
                            +
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <label for="comment" class="block text-sm font-medium text-gray-700"
                >Комментарий к заказу</label
            >
            <textarea
                name="comment"
                id="comment"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors form-control"
                v-model="formData.comment"
                placeholder="Дополнительные пожелания к заказу"
            ></textarea>
        </div>
    </form>
</template>

<style scoped lang="sass"></style>
