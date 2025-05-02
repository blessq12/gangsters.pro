<script>
export default {
    props: {
        categories: Object,
    },
    mounted() {
        this.current = this.categories[0].uri;
    },
    data: () => ({
        current: null,
    }),
    watch: {
        current(val) {
            console.log(val);
        },
    },
};
</script>

<template>
    <div class="flex items-center">
        <ul class="flex space-x-2">
            <li v-for="el in categories" :key="el.uri">
                <button
                    type="button"
                    class="px-4 py-2 rounded-md transition-colors bg-black/20 duration-200 ease-in-out hover:bg-gray-100"
                    :class="{
                        'bg-blue-600 text-white hover:bg-blue-700':
                            current == el.uri,
                        'bg-white text-gray-700': current != el.uri,
                    }"
                    @click="current = el.uri"
                >
                    {{ el.name }}
                </button>
            </li>
        </ul>
    </div>
</template>
