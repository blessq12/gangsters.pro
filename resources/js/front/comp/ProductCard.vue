<script>
import { localStore } from '../../stores/localStore';
import { appStore } from '../../stores/appStorage';
import { mapStores } from 'pinia';

export default {
    data() {
        return {
            thumbs: this.product['thumbnails'],
            images: this.product['images']
        }
    },
    computed: {
        ...mapStores(localStore, appStore),

        getThumbs() {
            if (this.thumbs) return this.thumbs.length > 0 && this.thumbs !== undefined ? this.thumbs[0]['small'] : '/images/placeholder/product-image-empty-512x512.jpg';
        }
    },
    props: {
        product: {
            type: Object,
            required: true
        }
    },
}
</script>

<template>
    <div class="col rounded">
        <div class="hover-over"></div>
        <div class="product">
            <div 
                class="header bg-image rounded position-relative overflow-hidden" 
                v-lazy:background-image="getThumbs"
            >
                <transition
                    enter-active-class="animate__animated animate__faster animate__fadeIn"
                    leave-active-class="animate__animated animate__faster animate__fadeOut"
                    mode="out-in"
                >
                    <div class="counter" v-if="localStore.checkExist('cart', product)">
                        <transition 
                            enter-active-class="animate__animated animate__faster animate__fadeIn"
                            leave-active-class="animate__animated animate__faster animate__fadeOut"
                            mode="out-in"
                        >
                            <span v-if="localStore.getQty(product) > 0" :key="localStore.getQty(product)">
                                {{ localStore.getQty(product) }}
                            </span>
                        </transition>
                    </div>
                </transition>
                <div class="badge position-relative" style="z-index: 1;">
                    <button 
                        type="button" 
                        :class="['favorite', 'rounded', { active: localStore.checkExist('fav', product) }]" 
                        @click="localStore.manageStore('fav', product)"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        :title="localStore.checkExist('fav', product) ? 'Удалить из избранного' : 'Добавить в избранное'"
                    >
                        <i :class="localStore.checkExist('fav', product) ? 'fa fa-heart' : 'fa fa-heart-o'"></i>
                        {{ localStore.checkExist('fav', product) ? 'Убрать' : 'Добавить' }}
                    </button>
                </div>
            </div>
            <div class="content">
                <span>{{ product.name }}</span>
            </div>
            <div class="footer">
                <div class="d-flex align-items-center justify-content-start">
                    <transition 
                        enter-active-class="animate__animated animate__faster animate__fadeIn"
                        leave-active-class="animate__animated animate__faster animate__fadeOut"
                        mode="out-in"
                    >
                        <button type="button" class="btn rounded btn-main m-0" @click="localStore.manageStore('cart', product)" v-if="!localStore.checkExist('cart', product)">
                            <i class="fa fa-shopping-cart" style="margin-right: 6px;"></i> В корзину
                        </button>
                        <div class="prod-qty" v-else>
                            <button class="btn rounded" @click="localStore.manageQty(false, product)">-</button>
                            <button class="btn rounded" @click="localStore.manageQty(true, product)">+</button>
                        </div>
                    </transition>
                    <button
                        type="button" 
                        class="additional"
                        data-bs-toggle="modal" 
                        data-bs-target="#additional"
                        @click="appStore.currentAdditional = product"
                    >i</button>
                </div>
                <div class="d-block">
                    <span class="d-block price" data-bs-toggle="tooltip" data-bs-placement="top" title="Цена указана за набор">
                        {{ product.price ?? 'Не указано' }}
                    </span>
                    <span class="d-block weight" data-bs-toggle="tooltip" data-bs-placement="top" title="Вес (масса нетто)">
                        {{ product.weight ?? 'Не указано' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="sass" scoped>
.fav-wrap
    min-width: 0px
    width: 0px
    overflow: hidden
    transition: all 0.3s
    &.show
        min-width: 74px
.favorite
    padding: unset
    margin: unset
    border: unset
    font-size: .8rem
    color: #fff
    display: flex
    align-items: center
    justify-content: center
    background: transparent
    border: 1px solid red
    padding: 8px 12px
    transition: all .3s
    i
        margin-right: 6px
    &:hover
        border-color: darken(red, 40%)
        background: darken(red, 40%)
        color: #fff
    &.active
        background: red
        color: #fff
        border-color: red

.btn-main
    background: $color-main !important
    color: #fff
    &:hover
        background: darken($color-main, 60%)
.prod-qty
    display: flex
    align-items: center
    button
        border: $color-main 1px solid
        background: $color-main !important
        margin-right: 0 !important
        color: #fff
        transition: all .3s
        min-width: 60px
        max-height: 47px
        padding: unset
        display: flex
        align-items: center
        justify-content: center
        font-size: 1.3rem
        &:hover
            background: lighten($color-main, 30%) !important
        &:first-child
            border-radius: 12px 0 0 12px !important
            margin: 0
        &:last-child
            border-radius: 0 12px 12px 0 !important
        &:hover
            background: $color-main
            color: #fff
    .form-control
        border-radius: 0
        border-left: none
        border-right: none
        width: 80px
        min-height: 48px
        height: 100%
        display: flex
        align-items: center
        justify-content: center
        text-align: center

.col
    max-width: unset
    padding: 12px
    min-width: 320px
    width: fit-content
    position: relative
    overflow: hidden

    @media (min-width: 768px)
      min-width: 260px

    @media (min-width: 992px)
      min-width: 25%
      width: 25%

    .hover-over
      z-index: -1
      opacity: 0
      position: absolute
      transition: all 0.7s ease-in-out
      top: 0
      left: 0
      width: 100%
      height: 100%
      background: url('/images/placeholder/pattern-50.png') no-repeat center center / cover

    &:hover
      .hover-over
        opacity: 0.4

    .product
        display: block

    .header
      min-height: 160px
      position: relative
      padding: 6px

      .counter
        position: absolute
        top: 0
        left: 0
        display: flex
        align-items: center
        justify-content: center
        width: 100%
        height: 100%
        z-index: 1
        background: rgba(0, 0, 0, 0.4)
        padding: 4px 8px
        border-radius: 12px

        span
          font-size: 5rem
          font-weight: 700
          color: #fff

    .content
      margin: 12px 0
      min-height: 45px

      span
        font-weight: 500
        font-size: 1.1rem
        display: block
        line-height: 1

    .footer
      display: flex
      align-items: center
      justify-content: space-between

      .additional
        display: flex
        background: transparent
        align-items: center
        justify-content: center
        border-top: 1px solid $color-main
        border-bottom: 1px solid $color-main
        border-right: 1px solid $color-main
        border-radius: 0 16px 16px 0
        position: relative
        margin: unset !important
        max-height: 47px
        font-weight: 800
        height: 100%
        font-size: 1.3rem
        transition: all 0.3s

        &:hover
          background: transparent !important
          color: $color-main

        &::before
          transition: all 0.3s
          position: absolute
          transform: scale(1.05)
          z-index: -1
          left: -50%
          height: 100%
          width: 100%
          background: transparent !important
          border-top: 1px solid $color-main
          border-bottom: 1px solid $color-main
          content: ''
          background: #dedede
          padding: unset
          margin: unset
          max-height: 47px
          height: 100%

      button
        background: #dedede
        border: unset
        padding: 14px 18px
        white-space: nowrap
        margin-right: 12px

        &:hover
          background: #2e2e2e
          color: #fff

        &.active
          background: $color-main
          color: #fff

      span
        &.price
          font-size: 1.1rem
          font-weight: 700
          margin-bottom: -4px

          &::after
            content: 'руб.'
            padding-left: 4px
            font-weight: 200

        &.weight
          font-size: 0.8rem
          font-weight: 500

          &::after
            content: 'гр.'
            padding-left: 4px
            font-weight: 200
</style>