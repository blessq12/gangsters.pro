<script>
export default{
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
                <div class="col-6 text-end">
                    <ul class="list-unstyled p-0 m-0">
                        <li class="btn btn-outline-primary" @click="$emit('toggleModal', 'cart')">
                            <i class="fa fa-shopping-basket"></i>
                        </li>
                        <li class="btn btn-outline-primary mx-3" @click="$emit('toggleModal', 'fav')">
                            <i class="fa fa-heart"></i>
                        </li>
                        <li class="btn btn-outline-primary" @click="$emit('toggleModal', 'user')">
                            <i class="fa fa-user"></i>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</template>

<style lang="sass">

</style>