<script>
export default {
    props: {
        target: String
    }
}
</script>

<template>
    <button type="button" class="btn btn-outline-primary">
        <i class="fa fa-heart-o" v-if="target == 'fav'"></i>
        <i class="fa fa-shopping-basket" v-if="target == 'cart'"></i>
        <i class="fa fa-user-o" v-if="target == 'user'"></i>
        <i class="fa fa-bars" v-if="target == 'bars'"></i>
    </button>
</template>

<style lang="sass" scoped>

</style>