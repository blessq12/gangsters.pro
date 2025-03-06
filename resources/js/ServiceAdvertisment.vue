<script>
import gsap from 'gsap'

export default {
    data() {
        return {
            services: [
                {
                    title: 'Диагностика и ремонт',
                    icon: 'bi-tools',
                    items: [
                        'Всевозможные диагностики автомобиля',
                        'Работы любой сложности',
                        'Автоэлектрик',
                        'Опрессовка дымом'
                    ]
                },
                {
                    title: 'Специализированные услуги',
                    icon: 'bi-gear-wide-connected',
                    items: [
                        'Настройка ЭБУ Subaru (чип-тюнинг)',
                        'Диагностика и мойка форсунок',
                        'Новейший стенд развал/схождение 3D',
                        'Заправка автокондиционеров'
                    ]
                },
                {
                    title: 'Запчасти и комплектующие',
                    icon: 'bi-car-front',
                    items: [
                        'Оригинальные запчасти и заменители',
                        'Турбокомпрессоры Arashi Dynamics',
                        'Сварочные работы в аргонной среде',
                        'Проточка тормозных дисков'
                    ]
                }
            ],
            currentServiceIndex: 0,
            contactInfo: {
                phones: ['32-86-86', '8-983-232-86-86'],
                address: 'г. Томск, ул. Шевченко 55 стр 5'
            }
        }
    },
    mounted() {
        this.initAnimations()
        this.startRotation()
    },
    methods: {
        initAnimations() {
            gsap.from('.brand-section', {
                x: -100,
                opacity: 0,
                duration: 1,
                ease: 'power3.out'
            })

            this.animateServiceItem(this.currentServiceIndex)
        },
        startRotation() {
            this.rotationInterval = setInterval(() => {
                this.switchService((this.currentServiceIndex + 1) % this.services.length)
            }, 5000)
        },
        stopRotation() {
            clearInterval(this.rotationInterval)
        },
        switchService(index) {
            const timeline = gsap.timeline()

            timeline.to('.service-content', {
                y: 30,
                opacity: 0,
                duration: 0.3,
                onComplete: () => {
                    this.currentServiceIndex = index
                }
            })

            timeline.call(() => this.animateServiceItem(index))
        },
        animateServiceItem(index) {
            gsap.fromTo('.service-content',
                { y: -30, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.5,
                    ease: 'power2.out'
                }
            )

            gsap.fromTo('.service-item',
                { y: 20, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.3,
                    stagger: 0.1,
                    ease: 'power2.out'
                }
            )
        }
    },
    beforeUnmount() {
        this.stopRotation()
    }
}
</script>

<template>
    <div class="service-banner overflow-hidden mb-4 container">
        <div class="row">
            <!-- Секция бренда -->
            <div class="col-lg-4 brand-section p-4">
                <div class="brand-content">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="logo-circle">
                            <span class="text-uppercase">G</span>
                        </div>
                        <h2 class="h3 fw-bold mb-0">GANGSTER'S SERVIS</h2>
                    </div>

                    <div class="contact-info mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-telephone-fill"></i>
                            <a href="tel:32-86-86" class="text-decoration-none">32-86-86</a>,
                            <a href="tel:8-983-232-86-86" class="text-decoration-none">8-983-232-86-86</a>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>{{ contactInfo.address }}</span>
                        </div>
                    </div>

                    <div class="features">
                        <div class="feature-item d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Сертифицированные специалисты</span>
                        </div>
                        <div class="feature-item d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-shield-check"></i>
                            <span>Гарантия на все работы</span>
                        </div>
                        <div class="feature-item d-flex align-items-center gap-2">
                            <i class="bi bi-stars"></i>
                            <span>Современное оборудование</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Секция услуг -->
            <div class="col-lg-8 services-section p-4">
                <div class="services-slider h-100">
                    <div class="service-content text-center">
                        <div class="service-icon mb-4">
                            <i :class="services[currentServiceIndex].icon" class="display-1"></i>
                        </div>
                        <h3 class="service-title h3 mb-4">
                            {{ services[currentServiceIndex].title }}
                        </h3>
                        <div class="service-items row g-3 justify-content-center">
                            <div v-for="(item, idx) in services[currentServiceIndex].items"
                                 :key="idx"
                                 class="col-md-6 service-item">
                                <div class="service-card p-3 rounded">
                                    <i class="bi bi-arrow-right-circle me-2"></i>
                                    {{ item }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="service-navigation mt-4">
                        <div class="d-flex justify-content-center gap-2">
                            <button v-for="(_, index) in services"
                                    :key="index"
                                    class="nav-btn"
                                    :class="{ active: index === currentServiceIndex }"
                                    @click="switchService(index)">
                                {{ index + 1 }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.service-banner {
    background: #1C1C1C;
    border: 1px solid #2C2C2C;
    border-radius: 0.5rem;
    color: #fff;
}

.brand-section {
    background: #232323;
    border-right: 1px solid #2C2C2C;

    @media (max-width: 991.98px) {
        border-right: none;
        border-bottom: 1px solid #2C2C2C;
    }
}

.logo-circle {
    width: 40px;
    height: 40px;
    background: #198754;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    color: #fff;
}

.contact-info {
    i {
        color: #198754;
    }

    a {
        color: #fff;

        &:hover {
            color: #198754;
        }
    }
}

.features {
    i {
        color: #198754;
    }
}

.service-icon {
    i {
        color: #198754;
        filter: drop-shadow(0 0 15px rgba(25, 135, 84, 0.2));
    }
}

.service-card {
    background: #232323;
    border: 1px solid #2C2C2C;
    transition: all 0.3s ease;

    i {
        color: #198754;
    }

    &:hover {
        transform: translateY(-5px);
        border-color: #198754;
        box-shadow: 0 5px 15px rgba(25, 135, 84, 0.1);
    }
}

.nav-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #2C2C2C;
    background: transparent;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    padding: 0;
    font-size: 14px;

    &:hover {
        border-color: #198754;
        color: #198754;
    }

    &.active {
        background: #198754;
        border-color: #198754;
        color: #fff;
    }
}
</style>
