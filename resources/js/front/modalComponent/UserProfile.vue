<script>
import { userStore } from '@/stores/userStore';
import { mapStores } from 'pinia';

export default {
    setup() {
        //
    },
    computed: {
        ...mapStores(userStore)
    },
    data() {
        return {
            activeTab: 'personal_data',
            editMode: false
        }
    }
}
</script>

<template>
    <div class="header p-2">
        <div class="row align-items-center">
            <div class="col">
                <h4 class="mb-0">Привет, {{ userStore.userData.name }}</h4>
            </div>
            <div class="col text-end">
                <button class="btn-exit" @click="userStore.logout">
                    Выйти
                    <i class="fa fa-sign-out ms-1"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="content p-2">
        <div class="row">
            <div class="col-12 mb-4">
                <ul class="btn-group p-0">
                    <button class="btn list-group-item" :class="{ active: activeTab == 'personal_data' }" @click="activeTab = 'personal_data'">Личные данные</button>
                    <button class="btn list-group-item" :class="{ active: activeTab == 'orders' }" @click="activeTab = 'orders'">Заказы</button>
                    <button class="btn list-group-item" :class="{ active: activeTab == 'gcoins' }" @click="activeTab = 'gcoins'">G-Coins</button>
                    <button class="btn list-group-item" disabled>Мои адреса</button>
                </ul>
            </div>
            <div class="col-12">
                <div class="card" v-if="activeTab == 'personal_data'">
                    <div class="card-body" v-if="!editMode">
                        <h5 class="card-title mb-3">Мои данные</h5>
                        <p class="card-text"><b>Имя:</b> {{ userStore.userData.name }}</p>
                        <p class="card-text"><b>Дата рождения:</b> {{ userStore.userData.dob }}</p>
                        <p class="card-text"><b>Телефон:</b> {{ userStore.userData.tel }}</p>
                        <p class="card-text"><b>Email:</b> {{ userStore.userData.email }}</p>
                        <button class="btn-exit fw-light" @click="editMode = true">Изменить данные</button>
                    </div>
                    <div class="card-body" v-else>
                        <h5 class="card-title mb-3">Изменение данных</h5>
                        <form>
                            <div class="mb-3">
                                <label for="name" class="form-label">Имя</label>
                                <input type="text" class="form-control" id="name" v-model="userStore.userData.name">
                            </div>
                            <div class="mb-3">
                                <label for="dob" class="form-label">Дата рождения</label>
                                <input type="date" class="form-control" id="dob" v-model="userStore.userData.dob">
                            </div>
                            <div class="mb-3">
                                <label for="tel" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="tel" v-model="userStore.userData.tel">
                            </div>
                            <div class="mb-3">
                                
                            </div>
                        </form>
                        <button class="btn-exit fw-light" @click="editMode = false">Отменить</button>
                    </div>
                </div>
                <div class="card" v-if="activeTab == 'orders'">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Мои заказы</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Дата</th>
                                        <th>Статус</th>
                                        <th>Сумма</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>2023-01-01</td>
                                        <td>Выполнен</td>
                                        <td>1000 руб.</td>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>2023-01-01</td>
                                        <td>Выполнен</td>
                                        <td>1000 руб.</td>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>2023-01-01</td>
                                        <td>Выполнен</td>
                                        <td>1000 руб.</td>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>2023-01-01</td>
                                        <td>Выполнен</td>
                                        <td>1000 руб.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card" v-if="activeTab == 'gcoins'">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Мои G-Coins</h5>
                        <p class="card-text">У вас <b>{{ userStore.userData.coins }}</b> G-Coins</p>
                        <p class="card-text text-muted mb-4">
                            У нас есть система кешбека, которая позволяет вам зарабатывать деньги с каждой покупки. Каждый раз, когда вы совершаете покупку, определенный процент от суммы будет начислен на ваш кешбек-аккаунт. Вы можете использовать накопленный кешбек для частичной оплаты вашего следующего заказа, что делает ваши покупки еще более выгодными. Не упустите возможность сэкономить!
                        </p>
                        <a href="/loyalty" class="btn-exit fw-light">Полная информация о кешбеке</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer"></div>
</template>

<style scoped lang="sass">
.btn-exit
    background: $color-main
    color: #fff
    border-radius: 10px
    padding: 10px 16px
    font-size: 16px
    font-weight: 500
    border: none
    outline: none
    cursor: pointer
.list-group-item
    cursor: pointer
    &.active
        background: $color-main
        color: #fff
        border-color: $color-main
    
</style>