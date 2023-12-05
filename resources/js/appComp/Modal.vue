<script>
export default{
    props:{
        comp: String
    }
}
</script>

<template>
<div class="m-wrap">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <div class="mw">

                    <component :is="comp"></component>

                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style lang="sass">

</style>