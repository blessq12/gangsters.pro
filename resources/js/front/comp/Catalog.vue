<script>
export default {
    props: {
        goods: Object
    }
}
</script>

<template>
    <div class="category" v-for="category in goods" :key="category.uri">
        <div class="row mb-4">
            <div class="col">
                <div class="section-title">
                    <h2>
                        {{ category.name }}
                    </h2>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3 mb-4 category-list">
            <div class="col" v-for="product in category.products" :key="product.id">
                <div class="product">
                    <div class="header bg-image rounded" style="background: url('http://via.placeholder.com/512x512');"></div>
                    <div class="content">
                        <span>{{ product.name }}</span>
                    </div>
                    <div class="footer">
                        <button type="button" class="rounded btn" @click="">
                            + В корзину
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="sass" scoped>

</style>