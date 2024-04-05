<script>
import { localStore } from '../../stores/localStore';
import { mapStores } from 'pinia';

export default {
    computed: {
        ...mapStores( localStore )
    }
}
</script>

<template>
    Cart comp
    {{ localStore.cart }}
</template>

<style lang="sass" scoped>

</style>