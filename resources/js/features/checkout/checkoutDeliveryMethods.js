/** Способы доставки в UI визарда. */

export const CHECKOUT_DELIVERY_METHOD_IDS = ["courier", "pickup"];

export const CHECKOUT_DELIVERY_METHOD_META = {
    courier: {
        label: "Курьер",
        inlineLabel: "Курьером",
        hint: "Доставка по адресу",
        icon: "mdi mdi-moped",
    },
    pickup: {
        label: "Самовывоз",
        inlineLabel: "Самовывоз",
        hint: "Заберёшь заказ сам",
        icon: "mdi mdi-store-outline",
    },
};
