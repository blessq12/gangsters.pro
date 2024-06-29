<script>
import moment from 'moment'
export default {
    props: {
        shedule: Array
    },
    data() {
        return {
            isHovered: false,
            hoverTimeout: null
        };
    },
    computed: {
        
    },
    methods: {
        moment(date) {
            return moment(date);
        },
        getDay() {
            let day = moment().format('dddd');
            return day.toLowerCase();
        },
        todayShedule() {
            return this.shedule.find(item => item.day_eng === this.getDay());
        },
        tomorrowShedule() {
            let tomorrow = moment().add(1, 'days').format('dddd');
            return this.shedule.find(item => item.day_eng === tomorrow.toLowerCase());
        },
        isOpen() {
            let now = moment();
            let open = this.moment(this.todayShedule().open_time);
            let close = this.moment(this.todayShedule().close_time);
            return now.isBetween(open, close);
        },
        unHover() {
            this.hoverTimeout = setTimeout(() => {
                this.isHovered = false;
            }, 1000); // Delay of 1 second
        },
        hover() {
            clearTimeout(this.hoverTimeout);
            this.isHovered = true;
        }
    },
    mounted() {
        this.day = this.shedule.find(item => item.day_eng === this.now);
    },
    watch: {
        isHovered(value) {
            if (!value) {
                this.hoverTimeout = setTimeout(() => {
                    this.isHovered = false;
                }, 1000); // Delay of 1 second
            } else {
                clearTimeout(this.hoverTimeout);
            }
            console.log(value);
        }
    }
}
</script>

<template>
    <div class="shedule position-relative" @mouseover="hover" @mouseleave="unHover()" @touchstart="hover" @touchend="unHover()">
        <div :class="['status', isOpen() ? 'open' : 'close']">
        </div>
        <div class="time">
            <span v-if="isOpen()">
                Открыто до {{ moment(todayShedule().close_time).format('HH:mm') }}
            </span>
            <span v-else>
                Закрыто до {{ moment(tomorrowShedule().open_time).format('HH:mm') }}
            </span>
        </div>

        <transition 
            enter-active-class="animate__animated animate__fadeInDown"
            leave-active-class="animate__animated animate__fadeOutUp"
            mode="out-in"
        >
            <div class="wrap" v-show="isHovered">
                <div class="popup rounded" @mouseover="hover" @mouseleave="unHover()" @touchstart="hover" @touchend="unHover()">
                    <div class="popup-content">
                        <div class="popup-title">
                            Расписание работы
                        </div>
                        <ul class="list-unstyled m-0 p-0">
                            <li v-for="item in shedule" class="d-flex justify-content-between align-items-center">
                                <span>{{ item.day }}</span>
                                <span>{{ moment(item.open_time, 'HH:mm:ss').format('HH:mm') }} - {{ moment(item.close_time, 'HH:mm:ss').format('HH:mm') }}</span>
                            </li>
                        </ul>
                </div>
            </div>
            </div>
        </transition>
        
    </div>
</template>

<style scoped lang="sass">
.wrap
    position: absolute
    top: 140%
    left: -50%
    width: 100%
    z-index: 100
    min-width: 350px
    height: 100%
    .popup
        background: #fff
        padding: 14px
        border: 1px solid $color-main
        .popup-title
            font-size: 16px
            font-weight: 500
            margin-bottom: 10px
.shedule
    display: flex
    align-items: center
    min-height: 42px
    cursor: pointer
    // &:hover
    .status
        width: 10px
        height: 10px
        border-radius: 50%
        background: #dedede
        &.open
            background: green
        &.close
            background: red
    .time
        font-weight: 500
        white-space: nowrap
</style>
