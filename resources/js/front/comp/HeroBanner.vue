<script>
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import { Pagination, Navigation, Autoplay } from 'swiper/modules';

export default {
  props: {
    banners: Object
  },
  components: {
    Swiper,
    SwiperSlide,
  },
  data() {
    return {
      modules: [Pagination, Navigation, Autoplay],
      activeIndex: 0,
    };
  },
  methods: {
    updateActiveIndex(swiper) {
      this.activeIndex = swiper.activeIndex;
    }
  }
};
</script>

<template>
  <swiper
    :loop="banners.length > 1"
    :slidesPerView="1"
    :spaceBetween="10"
    :centeredSlides="true"
    navigation
    :pagination="{
      clickable: true,
    }"
    :autoplay="{
      delay: 5000,
    }"
    :modules="modules"
    class="mySwiper"
    @slideChange="updateActiveIndex"
  >
    <swiper-slide v-for="(banner, index) in banners" :key="banner.id" :class="{ 'active-slide': index === activeIndex }">
      <div class="slide-container">
        <img :src="'/uploads/' + banner.image" alt="Gangsters sushi Томск" class="img-fluid">
      </div>
    </swiper-slide>
  </swiper>
</template>

<style scoped lang="sass">
.mySwiper
  .swiper-slide
    opacity: 0.4
    transition: opacity 0.3s ease
    img
      border-radius: 16px
  .swiper-slide-active
    opacity: 1
    .slide-container
      display: flex
      justify-content: center
      align-items: center
</style>
