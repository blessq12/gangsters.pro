<script>
export default{
    props:{
        stories: Object
    },
    mounted(){},
    data:()=>({
    })
}
</script>

<template>
    <div class="container">
        <div class="row">

            <div class="col-12" v-if="stories.length">
                <ul class="list-unstyled p-0 m-0 stories">
                    <li v-for="story in stories" :key="story">
                        <div class="story">
                            
                        </div>
                    </li>
                </ul>
            </div>

            <div class="col-12" v-else>
                <ul class="list-unstyled p-0 m-0 stories placeholder-glow">
                    <li><div class="story placeholder bg-dark"></div></li>
                    <li><div class="story placeholder bg-dark"></div></li>
                    <li><div class="story placeholder bg-dark"></div></li>
                    <li><div class="story placeholder bg-dark"></div></li>
                </ul>
            </div>

        </div>
    </div>
</template>

<style lang="sass">

</style>