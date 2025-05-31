<script>
import { mapStores } from "pinia";

import { appStore } from "../stores/appStorage";
import { localStore } from "../stores/localStore";
import { userStore } from "../stores/userStore";

import { useToast } from "vue-toastification";
const toast = useToast();

export default {
    props: {
        //
    },
    mounted() {
        this.localStore.loadStorage();
        this.userStore.loadStore();
        this.appStore.defineDevice();
    },
    computed: { ...mapStores(localStore, userStore, appStore) },
    watch: {
        "userStore.authStatus": {
            handler(val) {
                // if (val) {
                //     toast.info('Вы успешно авторизовались')
                // } else {
                //     toast.warning('Вы вышли из аккаунта')
                // }
            },
            immediate: true,
        },
        appStore: {
            handler(val) {
                if (val.modal && val.modalName === "cart") {
                    this.checkAvaliability();
                }
            },
            deep: true,
        },
    },
    methods: {
        checkAvaliability() {
            if (this.localStore.cart.length > 0) {
                let cartItemsIds = this.localStore.cart.map((item) => item.id);
                axios
                    .post(
                        "/api/orders/check-avalibility",
                        { ids: cartItemsIds },
                        {
                            validateStatus: function (status) {
                                return status >= 200 && status <= 422;
                            },
                        }
                    )
                    .then((res) => {
                        if (res.status === 422) {
                            let unavaliableItems = res.data;
                            unavaliableItems.forEach((item) => {
                                unavaliableItems.forEach((id) => {
                                    const item = this.localStore.cart.find(
                                        (cartItem) => cartItem.id === id
                                    );
                                    if (item) {
                                        toast.warning(
                                            `"${item.name}" сейчас не недоступен для заказа`
                                        );
                                        this.localStore.manageStore(
                                            "cart",
                                            item
                                        );
                                    }
                                });
                            });
                        }
                    })
                    .catch((err) => console.log(err.response));
                clearTimeout(this.avaiability);
            } else {
                return;
            }
        },
    },
};
</script>
<template></template>
<style lang="sass" scoped></style>
