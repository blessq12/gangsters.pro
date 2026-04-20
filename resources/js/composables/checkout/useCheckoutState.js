import { computed, ref } from "vue";
import { useOrderStore } from "../../stores/orderStore";
import { useUserStore } from "../../stores/userStore";
import { useClientReadModel } from "../../features/client/useClientReadModel";
import { useCartReadModel } from "../../features/shoppingSession/useCartReadModel";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";
import { formatRuPhone } from "../../utils/phone/formatRuPhone";

export function useCheckoutState() {
    const orderStore = useOrderStore();
    const userStore = useUserStore();
    const clientReadModel = useClientReadModel();
    const cartReadModel = useCartReadModel();

    orderStore.initFromStorage();

    const cartItems = computed(() => cartReadModel.items.value);
    const userCartItems = computed(() => cartReadModel.userItems.value);
    const systemCartItems = computed(() => cartReadModel.systemItems.value);
    const totalAmount = computed(() => cartReadModel.totalAmount.value);
    const userTotalAmount = computed(() => cartReadModel.userTotalAmount.value);
    const systemTotalAmount = computed(() => cartReadModel.systemTotalAmount.value);
    const isAuthenticated = computed(() => clientReadModel.isAuthenticated.value);

    const activeStep = ref("cart"); // cart | auth | delivery | payment | confirm | success
    const authTab = ref("login"); // login | register
    const isGuestCheckout = ref(false);

    const newAddressForm = ref({
        title: "",
        street: "",
        house: "",
        entrance: "",
        apartment: "",
        comment: "",
        make_default: true,
    });
    const newAddressLoading = ref(false);
    const newAddressError = ref("");
    const isNewAddressOpen = ref(false);
    const deliveryStepError = ref("");
    const paymentStepError = ref("");

    const hasCartItems = computed(() => userCartItems.value.length > 0);

    const formatPrice = (value) => formatMoneyRublesRu(value);

    function formatPhone(raw) {
        return formatRuPhone(raw);
    }

    return {
        orderStore,
        userStore,
        clientReadModel,
        cartItems,
        userCartItems,
        systemCartItems,
        totalAmount,
        userTotalAmount,
        systemTotalAmount,
        isAuthenticated,
        hasCartItems,

        activeStep,
        authTab,
        isGuestCheckout,

        newAddressForm,
        newAddressLoading,
        newAddressError,
        isNewAddressOpen,
        deliveryStepError,
        paymentStepError,

        formatPrice,
        formatPhone,
    };
}

