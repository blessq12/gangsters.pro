<script>
import axios from 'axios';
import moment from 'moment'
export default {
    mounted() {
        // get shedule from api
        this.getShedule();
    },
    updated() {
        //
    },
    data() {
        return {
            shedule: [],
            moment: moment,
            openShedule: false,
        };
    },
    computed: {
        
    },
    methods: {
        async getShedule() { 
            let response = await axios.get('/api/get-shedule');
            this.shedule = response.data;
        },
        isOpen() {
            let shedule = this.todayShedule();
            if (shedule && moment().isBefore(moment(shedule.close_time, 'HH:mm'))) {
                return true;
            }

            return false;
        },
        todayShedule() {
            if (this.shedule.length > 0) {
                return this.shedule.find(item => item.day_eng === this.moment().format('dddd').toLowerCase());
            }
            return null;
        },
        tomorrowShedule() {
            let tomorrow = this.moment().add(1, 'day').format('dddd').toLowerCase();
            
            if (this.shedule.length > 0) {
                return this.shedule.find(item => item.day_eng === tomorrow);
            }
            return null;
        }
        
    },
    watch: {
        //
    }
}
</script>

<template>
    <transition 
        enter-active-class="animate__animated animate__fadeInDown"
        leave-active-class="animate__animated animate__fadeOutUp"
        mode="out-in"
    >
        <div class="shedule position-relative" v-if="this.shedule.length > 0" @click="openShedule = !openShedule">
            <div :class="['status', isOpen() ? 'open' : 'close']">
            </div>
            <div class="time">
                <span v-if="isOpen()">
                    Открыто до {{ todayShedule().close_time }}
                </span>
                <span v-else>
                    Закрыто до {{ tomorrowShedule().open_time }}
                </span>
            </div>

            <transition 
                enter-active-class="animate__animated animate__fadeInDown"
                leave-active-class="animate__animated animate__fadeOutUp"
                mode="out-in"
            >
                <div class="wrap" v-if="openShedule">
                    <div class="popup rounded">
                        <div class="popup-content">
                            <div class="popup-title">
                                Расписание работы
                            </div>
                            <ul class="list-unstyled m-0 p-0">
                                <li 
                                    v-for="item in shedule" 
                                    class="d-flex justify-content-between align-items-center"
                                    :class="item.day_eng === todayShedule().day_eng ? 'today' : ''"
                                >
                                    <span> {{ item.day }} </span>
                                    <span> {{ item.open_time }} - {{ item.close_time }} </span>
                                </li>
                            </ul>
                    </div>
                </div>
                </div>
            </transition>
            
        </div>
    </transition>
</template>

<style scoped lang="sass">
.wrap
    position: absolute
    top: 140%
    left: calc(50% - 175px)
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
        ul
            li
                padding: 10px
                border-bottom: 1px solid #f0f0f0
                &:last-child
                    border-bottom: none
                &.today
                    background: lighten($color-main, 40%)
                    color: #fff
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
        @media(min-width: 768px)
            white-space: nowrap
</style>
