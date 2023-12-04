<script>
export default{
    props:{
        banners: Object
    },
    data:()=>({
    })
}
</script>

<template>
    <div class="container">
        <div class="row">

            <div class="col-12" v-if="banners.length">
                <div class="hero-wrap position-relative">
                    <img src="//via.placeholder.com/1200x600" class="img-fluid">
                </div>
            </div>

            <div class="col-12 placeholder-glow" v-else>
                <div class="hero-wrap position-relative placeholder p-4 d-flex align-items-end">
                    <div class="placegolder-glow w-100">
                        <h2 class="placeholder col-4 bg-light"></h2>
                        <br>
                        <p class="placeholder col-7 bg-light"></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>