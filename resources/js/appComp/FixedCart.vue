<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
export default{
    computed:{
        ...mapStores( localStore )
    }
}
</script>

<template>
<div class="f-cart invisible">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="visible d-flex justify-content-end">
                    <i class="fa fa-shopping-basket fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style lang="sass" scoped>
.f-cart
    position: fixed
    bottom: 60px
    width: 100%
.cart
    height: 100px
    width: 100px
    background: $color-dark
    visibility: visible !important
    margin: 0 !important
</style>