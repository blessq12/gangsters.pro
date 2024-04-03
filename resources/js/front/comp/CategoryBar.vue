<script>
export default {
    props: {
        categories: Object
    },
    mounted() {
        this.current = this.categories[0].uri
    },
    data: () => ({
        current: null
    }),
    watch: {
        current(val) {
            console.log(val)
        }
    }
}
</script>

<template>
    <div class="category-bar position-sticky" style="top: 0;">
        <ul>
            <li v-for="el in categories" :key="el.uri">
                <button type="button" class="btn rounded" @click="current = el.uri" :class="{active: current == el.uri}">
                    {{ el.name }}
                </button>
            </li>
        </ul>
    </div>
</template>

<style lang="sass" scoped>
.active
    background: $color-dark
    color: #fff
</style>