<script>
import axios from "axios";
import gsap from "gsap";
import moment from "moment";

export default {
    mounted() {
        this.getShedule();
    },
    data() {
        return {
            shedule: [],
            moment: moment,
            openShedule: false,
        };
    },
    computed: {},
    methods: {
        async getShedule() {
            let response = await axios.get("/api/get-shedule");
            this.shedule = response.data;
        },
        isOpen() {
            let schedule = this.todayShedule();
            if (!schedule) return false;

            const now = moment();
            const openTime = moment(schedule.open_time, "HH:mm");
            const closeTime = moment(schedule.close_time, "HH:mm");
            if (closeTime.isBefore(openTime)) {
                closeTime.add(1, "day");
                if (now.isBefore(moment("24:00", "HH:mm"))) {
                    return now.isSameOrAfter(openTime);
                }
                return now.isBefore(closeTime);
            }
            return now.isBetween(openTime, closeTime, undefined, "[]");
        },
        todayShedule() {
            if (this.shedule.length > 0) {
                return this.shedule.find(
                    (item) =>
                        item.day_eng ===
                        this.moment().format("dddd").toLowerCase()
                );
            }
            return null;
        },
        tomorrowShedule() {
            let tomorrow = this.moment()
                .add(1, "day")
                .format("dddd")
                .toLowerCase();
            if (this.shedule.length > 0) {
                return this.shedule.find((item) => item.day_eng === tomorrow);
            }
            return null;
        },
        toggleSchedule() {
            const popup = this.$refs.popup;

            if (this.openShedule) {
                gsap.to(popup, {
                    duration: 0.3,
                    opacity: 0,
                    y: -10,
                    onComplete: () => {
                        this.openShedule = false;
                        gsap.set(popup, { clearProps: "all" });
                    },
                });
            } else {
                this.openShedule = true;
                gsap.set(popup, { opacity: 0, y: -10 });
                gsap.to(popup, {
                    duration: 0.3,
                    opacity: 1,
                    y: 0,
                });
            }
        },
    },
};
</script>

<template>
    <div v-if="shedule.length > 0" class="relative">
        <div
            @click="toggleSchedule"
            class="flex items-center space-x-2 cursor-pointer min-h-[42px] hover:bg-gray-50 rounded-lg px-3 transition-colors duration-200"
        >
            <div
                :class="[
                    'w-2.5 h-2.5 rounded-full',
                    isOpen() ? 'bg-green-500' : 'bg-red-500',
                ]"
            />
            <div
                class="font-light text-sm sm:text-base md:whitespace-nowrap flex items-center space-x-2"
            >
                <span class="hidden md:block">{{
                    isOpen() ? "Открыто" : "Закрыто"
                }}</span>
                <i class="block md:hidden mdi mdi-clock-outline"></i>
                <i
                    class="mdi mdi-chevron-down transition-transform duration-200"
                    :class="[openShedule ? 'transform rotate-180' : '']"
                ></i>
            </div>
        </div>

        <div
            v-show="openShedule"
            ref="popup"
            class="absolute top-full mt-2 left-1/2 -translate-x-1/2 min-w-[320px] max-w-[calc(100vw-2rem)] z-50 md:w-full md:left-0 md:transform-none"
        >
            <div class="bg-white rounded-lg shadow-lg p-4 mx-auto">
                <div class="text-lg font-medium mb-3">Расписание работы</div>
                <ul class="divide-y divide-gray-100">
                    <li
                        v-for="item in shedule"
                        :key="item.day_eng"
                        :class="[
                            'flex justify-between items-center p-3 text-sm ',
                            item.day_eng === todayShedule()?.day_eng
                                ? 'bg-blue-500 text-white rounded-lg'
                                : '',
                        ]"
                    >
                        <span>{{ item.day }}</span>
                        <span
                            >{{ item.open_time }} - {{ item.close_time }}</span
                        >
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped lang="sass">
.shedule
    display: flex
    align-items: center
    min-height: 42px
    cursor: pointer
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
