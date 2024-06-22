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
      slidesPerView: 1,
      spaceBetween: 10,
    };
  },
  mounted() {
    this.updateSwiperSettings();
    window.addEventListener('resize', this.updateSwiperSettings);
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.updateSwiperSettings);
  },
  methods: {
    updateActiveIndex(swiper) {
      this.activeIndex = swiper.activeIndex;
    },
    updateSwiperSettings() {
      if (window.innerWidth >= 768) {
        this.slidesPerView = 3;
        this.spaceBetween = 20;
      } else {
        this.slidesPerView = 1;
        this.spaceBetween = 10;
      }
    }
  }
};
</script>

<template>
  <div class="swiper-wrapper">
    <swiper
      :loop="banners.length > 1"
      :slidesPerView="slidesPerView"
      :spaceBetween="spaceBetween"
      :centeredSlides="true"
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
    <div class="swiper-pagination"></div>
    <!-- <div class="swiper-navigation">
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div> -->
  </div>
</template>

<style scoped lang="sass">
.swiper-wrapper
  display: flex
  justify-content: center
  align-items: center
.mySwiper
  min-width: 260%
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
