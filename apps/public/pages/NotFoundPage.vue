<script setup>
import { useAppDesign } from "../design/useAppDesign";

const nf = useAppDesign().components.pages.notFound;
</script>

<template>
    <div :class="nf.pageWrap">
        <p :class="nf.code">
            404
        </p>
        <h1 :class="nf.title">
            Страница не найдена
        </h1>
        <p :class="nf.lead">
            Похоже, ты свернул не туда. Такой страницы у нас нет — зато меню точно на месте.
        </p>
        <div :class="nf.actions">
            <RouterLink
                :to="{ name: 'home' }"
                :class="nf.primaryBtn"
            >
                На главную
            </RouterLink>
            <RouterLink
                :to="{ name: 'contacts' }"
                :class="nf.secondaryBtn"
            >
                Контакты
            </RouterLink>
        </div>
    </div>
</template>
