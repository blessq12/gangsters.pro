<script>
import { userStore } from '../../stores/userStore'
import { appStore } from '../../stores/appStorage'
import { mapStores } from 'pinia'
export default {
    computed: {
        ...mapStores(userStore, appStore)
    },
    data: () => ({
        
    })
};
</script>

<template>
    <div class="row mb-4">
        <div class="col">
            <h4>Навигация по сайту</h4>
            <ul class="list-unstyled p-0 m-0 nav-list">
                <a href="" v-for="link in appStore.links">
                    <li>{{ link.name }}</li>
                </a>
            </ul>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h4>Контакты</h4>
        </div>
        <div class="col">
            <div class="row row-cols-1 row-cols-lg-2 contact-list">
                <div class="col">
                    <a href="tel:{{ appStore.company.phone }}">
                        <div class="contact-item">
                            <i class="fa fa-phone"></i>
                            <span>{{ appStore.company.phone }}</span>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="tel:{{ appStore.company.phone_additional }}">
                        <div class="contact-item">
                        <i class="fa fa-phone"></i>
                            <span>{{ appStore.company.phone_additional }}</span>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="mailto:{{ appStore.company.email_address }}">
                        <div class="contact-item">
                            <i class="fa fa-envelope"></i>
                            <span>{{ appStore.company.email_address }}</span>
                        </div>
                    </a>
                </div>
                <div class="col">
                    <a href="https://maps.app.goo.gl/vQJ1J111111111111" target="_blank">
                        <div class="contact-item">
                            <i class="fa fa-map-marker"></i>
                            <span>{{ appStore.company.street + ' ' + appStore.company.house }}</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="sass">
.nav-list
    li
        text-decoration: none
        color: $color-main
        background: transparent
        border: 1px solid $color-main
        margin-bottom: 12px
        min-height: 28px !important
        display: block
        padding: 12px 18px
        border-radius: 16px
        transition: all .3s
        &:hover
            background: $color-main
            color: #fff
.contact-list
    .col
        .contact-item
            display: flex
            border-radius: 16px
            flex-direction: column
            align-items: center
            background: $color-main
            white-space: wrap
            margin-bottom: 18px
            min-height: 70px
            color: #fff
            justify-content: center
            transition: all .3s
            &:hover
                background: lighten($color-main, 30% )
            i
                font-size: 24px
                margin-bottom: 8px
            span
                font-size: 12px
</style>
