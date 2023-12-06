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
    {{ localStore.fav }}
</template>

<style lang="sass"></style>