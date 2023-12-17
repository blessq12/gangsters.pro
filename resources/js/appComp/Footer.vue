<script>
export default{
    inject:['company']
}
</script>

<template>
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    {{ company.name }}
                </div>
            </div>
        </div>
        <div class="copyright">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        © 2020 - {{ new Date().getFullYear() }}, All Rights Reserved by
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<style lang="sass"></style>