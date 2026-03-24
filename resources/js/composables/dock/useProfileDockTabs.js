import { computed, ref, watch } from "vue";

/**
 * Табы дока профиля: guest (login/register) vs authenticated (view/edit).
 */
export function useProfileDockTabs(userStore) {
    const activeTab = ref("login");
    const isAuthenticated = computed(
        () => !!userStore.token && !!userStore.profile.id,
    );

    watch(
        isAuthenticated,
        (auth, wasAuth) => {
            if (auth) {
                activeTab.value = "view";
            } else if (wasAuth !== undefined) {
                activeTab.value = "login";
            }
        },
        { immediate: true },
    );

    function goLogin() {
        activeTab.value = "login";
    }

    function goView() {
        activeTab.value = "view";
    }

    function goEdit() {
        activeTab.value = "edit";
    }

    return {
        activeTab,
        isAuthenticated,
        handleLoggedIn: goView,
        handleRegistered: goView,
        handleUpdated: goView,
        handleLoggedOut: goLogin,
        switchToEdit: goEdit,
        switchToView: goView,
    };
}
