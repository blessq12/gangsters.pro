<script>
export default {
    data() {
        return {
            isOpen: false,
            services: [
                { title: 'Оригинальные запчасти и заменители', icon: '🔧' },
                { title: 'Турбокомпрессоры Arashi Dynamics', icon: '🚀' },
                { title: 'Всевозможные диагностики автомобиля', icon: '🔍' },
                { title: 'Работы любой сложности', icon: '⚙️' },
                { title: 'Автоэлектрик', icon: '⚡' },
                { title: 'Настройка ЭБУ Subaru (чип-тюнинг)', icon: '💻' },
                { title: 'Диагностика и мойка форсунок', icon: '💧' },
                { title: 'Проточка тормозных дисков', icon: '🛠️' },
                { title: 'Развал/схождение 3D', icon: '📐' },
                { title: 'Заправка автокондиционеров', icon: '❄️' },
                { title: 'Сварочные работы', icon: '🔥' },
                { title: 'Опрессовка дымом', icon: '💨' },
                { title: 'Шиномонтаж', icon: '🚗' },
                { title: 'Автомойка', icon: '🚿' }
            ],
            contacts: {
                phone: ['32-86-86', '8-983-232-86-86'],
                address: 'г. Томск, ул. Шевченко 55 стр 5',
                vk: 'https://vk.com/gangsters_service'
            }
        }
    },
    methods: {
        openModal() {
            this.isOpen = true
            document.body.style.overflow = 'hidden'
        },
        closeModal() {
            this.isOpen = false
            document.body.style.overflow = 'auto'
        }
    }
}
</script>

<template>

    <div class="gs-story-wrap" @click="openModal">
        <div class="gs-story-item rounded bg-image" style="background:url('/images/service.jpg');" @click="show = !show; currentStory = story"></div>
    </div>
    <div class="gs-story-footer">
        <span>GANGSTER’S SERVIS</span>
    </div>

    <teleport to="body">
    <transition name="gs-modal">
    <div class="gs-modal-overlay" v-if="isOpen" @click.self="closeModal">
        <div class="gs-modal-content">
            <button class="gs-close-button" @click="closeModal">×</button>

            <h2 class="gs-modal-title">GANGSTER'S SERVIS</h2>

            <div class="gs-contacts-section">
                <div class="gs-phone-numbers">
                    <a v-for="phone in contacts.phone" :key="phone" :href="'tel:' + phone">📞 {{ phone }}</a>
                </div>
                <div class="gs-address">📍 {{ contacts.address }}</div>
                <a :href="contacts.vk" target="_blank" class="gs-vk-link">VK группа</a>
            </div>

            <div class="gs-services-grid">
                <transition-group name="gs-service-item">
                    <div v-for="(service, index) in services"
                         :key="index"
                         class="gs-service-item"
                         :style="{ animationDelay: index * 0.1 + 's' }">
                        <span class="gs-service-icon">{{ service.icon }}</span>
                        <span class="gs-service-title">{{ service.title }}</span>
                    </div>
                </transition-group>
            </div>
        </div>
    </div>
    </transition>
</teleport>

</template>

<style lang="sass" scoped>
.gs-story-wrap
    width: 100%
    height: 100%
    position: relative
    padding: 3px 3px
    background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888)
    border-radius: 18px
    cursor: pointer

    .gs-story-item
        height: 100%
        width: 100%
        position: relative
        transition: transform 0.3s
        border-radius: 15px
        background-size: cover !important
        background-position: center !important

        &:hover
            transform: scale(0.98)

.gs-story-footer
    position: relative
    z-index: 1
    margin-top: 6px
    text-align: start
    padding: 0 8px
    span
        font-weight: 600
        text-transform: capitalize

.gs-modal-overlay
    position: fixed
    top: 0
    left: 0
    width: 100%
    height: 100%
    background: rgba(0, 0, 0, 0.8)
    display: flex
    justify-content: center
    align-items: center
    z-index: 1000

.gs-modal-content
    background: white
    border-radius: 20px
    padding: 30px
    max-width: 800px
    width: 90%
    max-height: 90vh
    overflow-y: auto
    position: relative

    &::-webkit-scrollbar
        width: 8px
    &::-webkit-scrollbar-track
        background: #f1f1f1
        border-radius: 4px
    &::-webkit-scrollbar-thumb
        background: #888
        border-radius: 4px

.gs-close-button
    position: absolute
    top: 15px
    right: 15px
    border: none
    background: none
    font-size: 24px
    cursor: pointer
    color: #333
    padding: 5px 10px
    border-radius: 50%
    transition: background 0.3s

    &:hover
        background: #f0f0f0

.gs-modal-title
    text-align: center
    color: #333
    font-size: 28px
    margin-bottom: 20px

.gs-contacts-section
    text-align: center
    margin-bottom: 30px
    padding: 15px
    background: #f8f9fa
    border-radius: 10px

    .gs-phone-numbers
        margin-bottom: 10px
        a
            display: block
            color: #007bff
            text-decoration: none
            margin: 5px 0
            font-size: 18px
            &:hover
                text-decoration: underline

    .gs-address
        color: #666
        margin: 10px 0

    .gs-vk-link
        display: inline-block
        margin-top: 10px
        padding: 8px 15px
        background: #4a76a8
        color: white
        text-decoration: none
        border-radius: 5px
        transition: background 0.3s
        &:hover
            background: #3d6898

.gs-services-grid
    display: grid
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))
    gap: 15px
    padding: 10px

.gs-service-item
    background: #f8f9fa
    padding: 15px
    border-radius: 10px
    display: flex
    align-items: center
    transition: transform 0.3s, box-shadow 0.3s
    animation: fadeInUp 0.5s ease forwards
    opacity: 0

    &:hover
        transform: translateY(-5px)
        box-shadow: 0 5px 15px rgba(0,0,0,0.1)

    .gs-service-icon
        font-size: 24px
        margin-right: 15px

    .gs-service-title
        color: #333
        font-size: 16px

// Анимации
.gs-modal-enter-active, .gs-modal-leave-active
    transition: opacity 0.3s

.gs-modal-enter-from, .gs-modal-leave-to
    opacity: 0

@keyframes fadeInUp
    from
        opacity: 0
        transform: translateY(20px)
    to
        opacity: 1
        transform: translateY(0)

.gs-service-item-enter-active
    transition: all 0.3s ease-out

.gs-service-item-leave-active
    transition: all 0.3s ease-in

.gs-service-item-enter-from,
.gs-service-item-leave-to
    opacity: 0
    transform: translateY(30px)
</style>
