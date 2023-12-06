<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
export default{
    computed:{
        ...mapStores(localStore)
    }
}
</script>

<template>
    {{ localStore.cart }}
</template>

<style lang="sass">
</style>