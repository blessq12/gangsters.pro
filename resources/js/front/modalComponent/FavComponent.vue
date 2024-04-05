<script>
import { mapStores } from 'pinia';
import { localStore } from '../../stores/localStore';
export default {
    computed: {
        ...mapStores( localStore )
    }
}
</script>

<template>
    <div v-if="localStore.fav.length">
        <ul class="fav-list">
            <li v-for="item in localStore.fav">
                <div class="fav-card">
                    <div class="image bg-image rounded" style="background: url('http://via.placeholder.com/256x256');">
                    </div>
                    <div class="d-block">
                        <div class="content">
                            <span>{{ item.name }}</span>
                            <p class="mb-0">{{ item.consist }}</p>
                        </div>
                        <div class="footer">
                            <button type="button" class="btn btn-secondary rounded">В корзину</button>
                            <button type="button" class="btn btn-danger rounded" @click="localStore.manageStore('fav', item)">Удалить</button>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <div v-else>
        <div class="row">
            <div class="col text-center">
                <p class="mb-0">
                    Пусто
                </p>
            </div>
        </div>
    </div>

</template>

<style lang="sass" scoped>

</style>