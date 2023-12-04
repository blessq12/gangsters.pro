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
                <div class="col-6">

                </div>
            </div>
        </div>
    </nav>
</template>

<style lang="sass">

</style>