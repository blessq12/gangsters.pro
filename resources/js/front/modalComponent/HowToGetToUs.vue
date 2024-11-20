<script>
export default {
    data: () => ({
        show: false
    })
}
</script>

<template>
    <div class="row mb-4">
        <div class="col">
            <button type="button" class="btn btn-primary w-100 rounded" @click="show = !show">
                <span>Как до нас добраться</span>
                <i class="fa fa-map-marker px-2"></i>
            </button>
            <div class="overflow-hidden mt-3" style="transition: all 0.3s ease-in-out;">
                <transition
                    enter-active-class="animate__animated animate__slideInDown"
                leave-active-class="animate__animated animate__slideOutUp"
            >
                <div v-if="show">
                    <div class="card card-body">

                        <!-- ya maps -->
                         <div style="position:relative;overflow:hidden;" class="rounded">
                            <a href="https://yandex.ru/maps/67/tomsk/?utm_medium=mapframe&utm_source=maps" style="color:#eee;font-size:12px;position:absolute;top:0px;">Томск</a><a href="https://yandex.ru/maps/67/tomsk/?ll=84.986330%2C56.513423&mode=routes&rtext=~56.513356%2C84.986301&rtt=auto&ruri=~ymapsbm1%3A%2F%2Forg%3Foid%3D82888444717&utm_medium=mapframe&utm_source=maps&z=16.61" style="color:#eee;font-size:12px;position:absolute;top:14px;">Яндекс Карты</a><iframe src="https://yandex.ru/map-widget/v1/?ll=84.986330%2C56.513423&mode=routes&rtext=~56.513356%2C84.986301&rtt=auto&ruri=~ymapsbm1%3A%2F%2Forg%3Foid%3D82888444717&z=16.61" width="100%" height="400" frameborder="0" allowfullscreen="true" style="position:relative;"></iframe>
                        </div>
                        <!-- end ya maps -->

                        <a href="https://yandex.ru/maps/67/tomsk/?ll=84.986330%2C56.513423&mode=routes&rtext=~56.513356%2C84.986301&rtt=auto&ruri=~ymapsbm1%3A%2F%2Forg%3Foid%3D82888444717&z=16.61" target="_blank" class="btn btn-primary w-75 rounded">
                            <span>Маршрут на Яндекс.Картах</span>
                            <i class="fa fa-map-marker px-2"></i>
                        </a>

                    </div>
                </div>
            </transition>
            </div>
        </div>
    </div>
</template>

<style scoped lang="sass">
.btn-primary 
    background: lighten($color-main, 20%)
    border: 1px solid lighten($color-main, 20%)
    &:hover
        background: lighten($color-main, 10%)
        border-color: lighten($color-main, 10%)
        color: #fff

</style>