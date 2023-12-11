<script>
import { mapStores } from 'pinia';
import { userStore } from '../stores/userStore';
export default{
    inject:['company'],
    computed:{
        ...mapStores(userStore)
    }
}
</script>

<template>
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 mb-4 mb-md-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h2>{{ company.name }}</h2>
                    <button type="button" class="btn btn-close" @click="$emit('close')"></button>
                </div>
                <p>
                    Самый лучший ресторан доставки в городе. У нас всегда свежие продукты и отличные повара, что превосходно готовят настоящие роллы и суши.
                    При онлайн заказах действует система кешбэка в виде виртуальной валюты "Gangsta" койны. С кадого онлайн заказа начисляются монеты на ваш личный счет с которого 
                    можно оплачивать частично или полностью заказы на сайте. <div class="text-danger">Данная система кешбека работает только при онлайн заказах</div>
                </p>
                <h4>Навигация по сайту</h4>
                <ul class="list-unstyled p-0 m-0 nav-links">
                    <a href="/">
                        <li>Главная</li>
                    </a>
                    <a href="/about">
                        <li>О нас</li>
                    </a>
                    <a href="/vacancy">
                        <li>Вакансии</li>
                    </a>
                    <a href="/contact">
                        <li>Контакты</li>
                    </a>
                </ul>
            </div>
            <div class="col-12 col-md-6">
                <auth-form v-if="!userStore.auth"></auth-form>
            </div>
        </div>
    </div>
</template>

<style lang="sass">

</style>