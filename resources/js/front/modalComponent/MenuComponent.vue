<script>
import { userStore } from '../../stores/userStore'
import { appStore } from '../../stores/appStorage'
import { mapStores } from 'pinia'
export default {
    computed: {
        ...mapStores(userStore, appStore)
    },
    data: () => ({
        links: [],
        company: null
    }),
    mounted() {
        this.getLinks();
        this.getCompany();
    },
    methods:{
        async getLinks() {
            const response = await axios.get('/api/get-routes');
            this.links = response.data;
        },
        async getCompany() {
            const response = await axios.get('/api/get-company');
            this.company = response.data;
        }
    }
};
</script>

<template>
    <div class="row row-cols-1">
        <transition
            enter-active-class="animate__animated animate__fadeInDown"
            leave-active-class="animate__animated animate__fadeOut"
            mode="out-in"
        >
            <div class="col mb-4" v-if="links.length > 0">
                <h5 class="mb-3 border-bottom pb-2">Навигация по сайту</h5>
                <ul class="list-unstyled p-0 m-0 nav-list">
                    <a :href="link.url" v-for="link in links">
                        <li>{{ link.name }}</li>
                    </a>
                </ul>
            </div>
            <div class="col mb-4 placeholder-glow" v-else>
                <h5 class="mb-3 border-bottom pb-2 placeholder rounded">Навигация по сайту</h5>
                <ul class="list-unstyled p-0 m-0 nav-list">
                    <a :href="link.url" v-for="link in 3" class="placeholder d-block bg-dark rounded">
                        <li style="height: 50px;"></li>
                    </a>
                </ul>
            </div>
        </transition>

        <transition
            enter-active-class="animate__animated animate__fadeInDown"
            leave-active-class="animate__animated animate__fadeOut"
            mode="out-in"
        >
            <div class="col" v-if="company">
                <h5 class="mb-3 border-bottom pb-2">Контакты</h5>
                <div class="row py-2 align-items-center">
                    <div class="col-12 d-flex">
                        <img 
                            :src="'/uploads/' + company.logo"
                            :alt="company.name" 
                            class="img-fluid"
                            style="max-width: 120px;"
                        >
                        <div class="d-block ms-3">
                            <div class="company-name fs-4 fw-bold">{{ company.name }} - {{ company.city }}</div>
                            <div class="company-description">{{ company.description }}</div>
                        </div>
                        
                    </div>
                    <div class="col mt-4">
                            <div class="row">
                                <div class="col">
                                    <div class="card">
                                        <a :href="'tel:' + company.phone" class="company-phone ms-3">
                                            <div class="card-body d-flex align-items-center justify-content-center">
                                                <i class="fa fa-phone" style="font-size: 24px; margin-right: 12px;"></i>
                                                {{ company.phone }}
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="card">
                                        <a 
                                            href="https://yandex.ru/maps/67/tomsk/?ll=84.986330%2C56.513423&mode=routes&rtext=~56.513356%2C84.986301&rtt=auto&ruri=~ymapsbm1%3A%2F%2Forg%3Foid%3D82888444717&z=16.61"
                                            class="company-address ms-3"
                                            title="Построить маршрут на Яндекс.Картах"
                                        >
                                        <div class="card-body d-flex align-items-center justify-content-center">
                                            <i class="fa fa-map-marker" style="font-size: 24px; margin-right: 12px;"></i>
                                            {{ company.city }}, {{ company.street }}, {{ company.house }}
                                        </div>
                                    </a>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <div class="col placeholder-glow" v-else>
                <h5 class="mb-3 border-bottom pb-2 placeholder rounded">Контакты</h5>
                <div class="row py-2 align-items-center">
                    <div class="col-12 d-flex">
                        <img 
                            class="rounded-circle placeholder"
                            style="min-width: 120px; min-height: 120px;"
                        >
                        <div class="d-block ms-3">
                            <div class="company-name fs-4 fw-bold placeholder mb-2 rounded">
                                Lorem ipsum dolor sit
                            </div>
                            <div class="company-description placeholder rounded">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam consectetur autem modi quaerat id repellendus minus labore incidunt, 
                            </div>
                        </div>
                        
                    </div>
                    <div class="col mt-4">
                            <div class="row">
                                <div class="col">
                                    <div class="card placeholder w-100">
                                        
                                            <div class="card-body d-flex align-items-center justify-content-center">
                                                <i class="fa fa-phone" style="font-size: 24px; margin-right: 12px;"></i>
                                                
                                            </div>
                                        
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="card placeholder w-100">
                                        <div class="card-body d-flex align-items-center justify-content-center">
                                            <i class="fa fa-map-marker" style="font-size: 24px; margin-right: 12px;"></i>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    
                </div>
            </div>
            
        </transition>

    </div>
</template>

<style scoped lang="sass">
.card
    border: 1px solid $color-main !important
    border-radius: 16px
    transition: all .3s
    .card-body
        background: transparent
        color: $color-main
        
    &:hover
        background: $color-main
        color: #fff
        .card-body
            
            color: #fff
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
