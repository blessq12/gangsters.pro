import axios from "axios";
import { defineStore } from "pinia";
import { useToast } from "vue-toastification";
const toast = useToast({
    position: "top-right",
    timeout: 5000,
    closeOnClick: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
});

export const userStore = defineStore("user", {
    state: () => ({
        authStatus: false,
        userData: null,
        authErrorBag: {},
        orders: [],
    }),
    actions: {
        async loadStore() {
            if (localStorage.getItem("token")) {
                axios.defaults.headers["Accept"] = "application/json";
                axios.defaults.headers[
                    "Authorization"
                ] = `Bearer ${localStorage.getItem("token")}`;
                try {
                    const response = await axios.get("/api/auth/get-user");
                    this.userData = response.data.user;
                    this.authStatus = true;
                } catch (error) {
                    this.authErrorBag = error.response.data.errors;
                    console.error("Error loading user data:", error);
                }
            } else {
                this.authStatus = false;
                this.userData = null;
            }
        },
        auth(action, cred) {
            let url =
                action == "login" ? "/api/auth/login" : "/api/auth/register";
            axios
                .post(url, cred)
                .then((res) => {
                    this.authStatus = true;
                    this.userData = res.data.user;
                    localStorage.setItem("token", res.data.token);
                    axios.defaults.headers["Accept"] = "application/json";
                    axios.defaults.headers["Authorization"] =
                        "Bearer " + res.data.token;

                    this.loadStore();
                })
                .catch((err) => {
                    toast.error(err.response.data.message);
                });
        },
        logout() {
            this.authStatus = false;
            this.userData = null;
            localStorage.removeItem("token");

            axios.defaults.headers["Authorization"] = "";
        },
        updateUser(data) {
            axios
                .patch("/api/auth/update-user", data)
                .then((res) => {
                    this.userData = res.data.user;
                    toast.success("Изменения сохранены");
                })
                .catch((err) => {
                    for (const [key, value] of Object.entries(
                        err.response.data.errors
                    )) {
                        toast.error(`${value}`);
                    }
                });
        },
        resetPassword(email) {
            axios
                .post("/api/auth/reset-password", { email: email })
                .then((res) => {
                    toast.success(res.data.message);
                })
                .catch((err) => {
                    toast.error(err.response.data.message);
                });
        },
    },
    getters: {},
});
