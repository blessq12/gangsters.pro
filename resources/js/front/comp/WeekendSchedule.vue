<script>
export default {
    data(){
        return {
            viewed: false,
            modal: null
        }
    },
    mounted() {
        if (!localStorage.getItem('newyear_modal_viewed')) {
            setTimeout(() => {
                this.modal = new bootstrap.Modal(document.getElementById('exampleModal'))
                this.modal.show()
            }, 3000)
        }
    },
    methods:{
        closeNotification(){
            localStorage.setItem('newyear_modal_viewed', 'true')
            this.modal.hide()
        }
    }
}
</script>

<template>
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">График работы в праздники</h1>
                <button type="button" class="btn-close" @click="closeNotification" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="holiday-message">
                    <div class="decoration-top mb-4">🎄 ⭐ 🎄 ⭐ 🎄</div>
                    <h2 class="mb-4">GANGSTER'S SUSHI на связи!</h2>
                    <p class="mb-3">Дорогие гости, в предверии НОВОГО ГОДА мы хотим заранее Вас проинформировать.</p>
                    <p class="mb-3 fw-bold">1 и 2 января - МЫ НЕ РАБОТАЕМ!</p>
                    <p class="mb-3">3 января - мы возвращаемся к стандартному графику работы!</p>
                    <div class="decoration-bottom mt-4">🎄 ⭐ 🎄 ⭐ 🎄</div>
                </div>
            </div>
            </div>
        </div>
        </div>
</template>

<style lang="scss" scoped>
.holiday-message {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);

    .decoration-top,
    .decoration-bottom {
        font-size: 1.5rem;
        color: #198754;
    }

    h2 {
        color: #dc3545;
        font-weight: bold;
    }
}
</style>