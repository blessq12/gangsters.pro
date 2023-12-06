<script>
import { mapStores } from 'pinia';
import { userStore } from '../stores/userStore';
export default{
    computed:{
        ...mapStores(userStore)
    }
}
</script>

<template>
    {{ userStore }}
</template>

<style lang="sass"></style>