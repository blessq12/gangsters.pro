<script>
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/swiper-bundle.css';

export default {
  components: {
    Swiper,
    SwiperSlide
  },
  props: {
    banners: Object
  }
};
</script>

<template>
  <div class="hero-banner">
    <swiper :slides-per-view="1" :loop="true" :autoplay="true" pagination class="p-0">
      <swiper-slide v-for="banner in banners" :key="banner.id" class="hero-banner-slide">
        <img :src="'/uploads/'+banner.image" alt="" class="img-fluid">
      </swiper-slide>
      <div class="swiper-pagination"></div>
    </swiper>
  </div>
</template>

<style scoped lang="sass">
.hero-banner
  position: relative
  min-height: unset
  overflow: hidden
  padding: 0
.hero-banner-slide
  padding: 0 3px
  img
    width: 100%
    height: 100%
    object-fit: cover
    border-radius: 16px
    overflow: hidden
.overlay
  position: absolute
  top: 0
  left: 0
  width: 100%
  height: 100%
  background: rgba(0, 0, 0, 0.5)

.content
  position: absolute
  bottom: 20%
  left: 50%
  transform: translateX(-50%)
  text-align: center
  color: #fff
</style>
