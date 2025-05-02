<script>
export default {
    props: {
        noDelForm: {
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
};
</script>

<template>
    <form ref="noDelivery" class="space-y-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label
                    for="name"
                    class="block text-sm font-medium text-gray-700"
                    >Имя</label
                >
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                    v-model="noDelForm.name"
                />
            </div>
            <div class="space-y-2">
                <label for="tel" class="block text-sm font-medium text-gray-700"
                    >Телефон</label
                >
                <input
                    type="text"
                    name="tel"
                    id="tel"
                    placeholder="+7 (___) ___-__-__"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                    v-model="noDelForm.tel"
                    v-maska
                    data-maska="+7 (###) ###-##-##"
                />
            </div>
        </div>

        <div class="space-y-2">
            <label
                for="personQty"
                class="block text-sm font-medium text-gray-700"
                >Количество персон</label
            >
            <div class="flex items-center space-x-2">
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50 disabled:bg-gray-400 transition duration-200"
                    @click="noDelForm.personQty--"
                    :disabled="noDelForm.personQty < 2"
                >
                    -
                </button>
                <input
                    type="text"
                    name="personQty"
                    id="personQty"
                    class="w-20 px-4 py-2 text-center rounded-lg border border-gray-300 font-bold"
                    v-model="noDelForm.personQty"
                    min="1"
                    max="10"
                />
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition duration-200"
                    @click="noDelForm.personQty++"
                >
                    +
                </button>
            </div>
        </div>

        <div class="space-y-2">
            <label for="comment" class="block text-sm font-medium text-gray-700"
                >Комментарий</label
            >
            <textarea
                name="comment"
                id="comment"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 min-h-[100px]"
                v-model="noDelForm.comment"
            ></textarea>
        </div>
    </form>
</template>

<style>
/* Удаляем старые стили, так как теперь используем Tailwind */
</style>
