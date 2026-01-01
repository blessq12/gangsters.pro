<script>
export default {
    data() {
        return {
            isVisible: false,
        };
    },
    computed: {
        shouldShowModal() {
            const now = new Date();
            const month = now.getMonth(); // 0-11, где 0 = январь
            const day = now.getDate(); // 1-31

            // Показываем только 1 и 2 января
            return month === 0 && (day === 1 || day === 2);
        },
    },
    mounted() {
        if (this.shouldShowModal) {
            this.isVisible = true;
        }
    },
    methods: {
        closeModal() {
            this.isVisible = false;
        },
    },
};
</script>

<template>
    <transition name="fade">
        <div
            v-if="isVisible"
            class="holiday-modal-overlay"
            @click.self="closeModal"
        >
            <div class="holiday-modal">
                <button
                    class="close-btn"
                    @click="closeModal"
                    aria-label="Закрыть"
                >
                    <i class="mdi mdi-close"></i>
                </button>

                <div class="holiday-content">
                    <div class="decoration-top">🎄 ⭐ 🎄 ⭐ 🎄</div>

                    <h2 class="title">GANGSTER'S SUSHI</h2>

                    <p class="greeting">
                        Дорогие гости, обращаем Ваше внимание на изменение
                        графика работы в праздничные дни:
                    </p>

                    <div class="holiday-info">
                        <div class="non-working-days">
                            <span class="icon">🚫</span>
                            <span class="text-bold">1 и 2 января</span>
                            <span class="text-highlight"
                                >— МЫ НЕ РАБОТАЕМ!</span
                            >
                        </div>

                        <div class="working-days">
                            <span class="icon">✅</span>
                            <span class="text">С 3 января</span>
                            <span class="text"
                                >мы возвращаемся к стандартному графику
                                работы</span
                            >
                        </div>
                    </div>

                    <div class="decoration-bottom">🎄 ⭐ 🎄 ⭐ 🎄</div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style lang="sass" scoped>
.holiday-modal-overlay
    position: fixed
    top: 0
    left: 0
    width: 100%
    height: 100%
    background: rgba(0, 0, 0, 0.7)
    display: flex
    align-items: center
    justify-content: center
    z-index: 9999
    backdrop-filter: blur(4px)

.holiday-modal
    position: relative
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%)
    border-radius: 20px
    padding: 3rem 2.5rem
    max-width: 600px
    width: 90%
    max-height: 90vh
    overflow-y: auto
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3)
    animation: modalAppear 0.3s ease-out

.close-btn
    position: absolute
    top: 1rem
    right: 1rem
    background: transparent
    border: none
    font-size: 1.5rem
    cursor: pointer
    color: #666
    padding: 0.5rem
    border-radius: 50%
    transition: all 0.2s
    width: 40px
    height: 40px
    display: flex
    align-items: center
    justify-content: center

    &:hover
        background: #f0f0f0
        color: #dc3545
        transform: rotate(90deg)

.holiday-content
    text-align: center

.decoration-top,
.decoration-bottom
    font-size: 2rem
    color: #198754
    margin: 1rem 0
    line-height: 1

.title
    font-size: 2rem
    font-weight: bold
    color: #dc3545
    margin: 1.5rem 0
    text-transform: uppercase
    letter-spacing: 2px

.greeting
    font-size: 1.1rem
    color: #333
    margin: 1.5rem 0
    line-height: 1.6

.holiday-info
    margin: 2rem 0
    display: flex
    flex-direction: column
    gap: 1.5rem

.non-working-days,
.working-days
    display: flex
    align-items: center
    justify-content: center
    gap: 0.75rem
    padding: 1.25rem
    border-radius: 12px
    flex-wrap: wrap

.non-working-days
    background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%)
    border: 3px solid #ffc107
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3)

    .icon
        font-size: 2rem

    .text-bold
        font-weight: bold
        font-size: 1.3rem
        color: #856404

    .text-highlight
        font-weight: bold
        font-size: 1.3rem
        color: #dc3545
        text-transform: uppercase

.working-days
    background: linear-gradient(135deg, #d1e7dd 0%, #a3cfbb 100%)
    border: 3px solid #198754
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3)

    .icon
        font-size: 2rem

    .text
        font-size: 1.1rem
        color: #0a3622
        font-weight: 500

@keyframes modalAppear
    from
        opacity: 0
        transform: scale(0.9) translateY(-20px)
    to
        opacity: 1
        transform: scale(1) translateY(0)

.fade-enter-active,
.fade-leave-active
    transition: opacity 0.3s

.fade-enter-from,
.fade-leave-to
    opacity: 0

@media (max-width: 640px)
    .holiday-modal
        padding: 2rem 1.5rem
        width: 95%

    .title
        font-size: 1.5rem

    .non-working-days .text-bold,
    .non-working-days .text-highlight
        font-size: 1.1rem

    .working-days .text
        font-size: 1rem
</style>
