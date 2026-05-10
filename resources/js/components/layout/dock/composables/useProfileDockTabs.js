import { computed, ref, watch } from "vue";

/** Гость */
export const PROFILE_TAB_LOGIN = "login";
export const PROFILE_TAB_REGISTER = "register";

/** Авторизованный пользователь — один уровень вкладок в доке */
export const PROFILE_TAB_OVERVIEW = "overview";
export const PROFILE_TAB_ADDRESSES = "addresses";
export const PROFILE_TAB_ORDERS = "orders";
export const PROFILE_TAB_EDIT = "edit";

/**
 * Состояние вкладок дока профиля: гость (вход/регистрация) или ЛК (обзор/адреса/заказы/редактирование).
 */
export function useProfileDockTabs(userStore) {
    const activeTab = ref(PROFILE_TAB_LOGIN);
    const isAuthenticated = computed(
        () => !!userStore.token && !!userStore.profile.id,
    );

    watch(
        isAuthenticated,
        (auth, wasAuth) => {
            if (auth) {
                activeTab.value = PROFILE_TAB_OVERVIEW;
            } else if (wasAuth !== undefined) {
                activeTab.value = PROFILE_TAB_LOGIN;
            }
        },
        { immediate: true },
    );

    function goLogin() {
        activeTab.value = PROFILE_TAB_LOGIN;
    }

    function goOverview() {
        activeTab.value = PROFILE_TAB_OVERVIEW;
    }

    function goEdit() {
        activeTab.value = PROFILE_TAB_EDIT;
    }

    return {
        activeTab,
        isAuthenticated,
        handleLoggedIn: goOverview,
        handleRegistered: goOverview,
        handleUpdated: goOverview,
        handleLoggedOut: goLogin,
        switchToOverview: goOverview,
        switchToEdit: goEdit,
    };
}
