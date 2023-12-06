<script>
import { mapStores } from 'pinia';
import { localStore } from '../stores/localStore';
export default{
    computed:{
        ...mapStores(localStore)
    },
    inject:['company']
}
</script>

<template>
    <nav>
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <a href="/">
                        {{ company.name }}
                    </a>
                </div>
                <div class="col-6">
                    <ul class="list-unstyled p-0 m-0 d-flex justify-content-end">
                        <li>
                            <button class="btn btn-outline-primary" type="button" @click="$emit('toggleModal', 'cart')">
                                <i class="fa fa-shopping-basket"></i>
                            </button>
                        </li>
                        <li>
                            <button class="btn btn-outline-primary mx-3" type="button" @click="$emit('toggleModal', 'fav')">
                                <i class="fa fa-heart"></i>
                            </button>
                        </li>
                        <li>
                            <button class="btn btn-outline-primary" type="button" @click="$emit('toggleModal', 'user')">
                                <i class="fa fa-user"></i>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</template>

<style lang="sass">

</style>