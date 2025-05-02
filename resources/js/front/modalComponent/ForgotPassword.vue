<script>
import { object, string } from "yup";
export default {
    props: {
        //
    },
    data() {
        return {
            email: "",
            schema: object().shape({
                email: string().email().required(),
            }),
            errors: [],
            isLoading: false,
            issetEmail: false,
            finish: false,
            message: "",
        };
    },
    methods: {
        send() {
            this.schema
                .validate({ email: this.email }, { abortEarly: false })
                .then(() => {
                    this.errors = [];
                    this.isLoading = true;
                    axios
                        .post("/api/auth/forgot-password", {
                            email: this.email,
                        })
                        .then((res) => {
                            this.issetEmail = true;
                            this.finish = true;
                            this.message = res.data.message;
                        })
                        .catch((err) => {
                            this.issetEmail = false;
                            this.finish = true;
                            this.message = err.response.data.message;
                            console.log(err.response.data);
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                })
                .catch((err) => {
                    this.errors = [];
                    err.inner.forEach((error) => {
                        this.errors[error.path] = error.message;
                    });
                });
        },
        updateInputs() {
            let inputs =
                this.$refs.forgotPasswordForm.querySelectorAll("input");
            inputs.forEach((input) => {
                if (this.errors[input.name]) {
                    input.classList.add("is-invalid");
                } else {
                    input.classList.remove("is-invalid");
                }
            });
        },
    },
    mounted() {},
    watch: {
        errors(val) {
            this.updateInputs();
        },
    },
};
</script>

<template>
    <div class="row">
        <div class="col-12 mb-4">
            <h3>Восстановление пароля</h3>
            <p>
                Введите ваш email, чтобы получить ссылку для восстановления
                пароля
            </p>
        </div>
        <div class="col">
            <form @submit.prevent="send" ref="forgotPasswordForm">
                <div class="input-group mb-3">
                    <input
                        type="text"
                        class="form-control"
                        name="email"
                        placeholder="Электронная почта"
                        aria-label="Электронная почта"
                        v-model="email"
                    />
                    <span class="input-group-text fw-bold">@</span>
                </div>
                <div v-if="isLoading" class="d-flex mb-3 align-items-center">
                    <div
                        class="spinner-border spinner-border-sm text-primary"
                        role="status"
                    >
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2" v-if="!finish"
                        >Поиск учетной записи ...</span
                    >
                </div>
                <span class="d-block mb-2" v-if="finish">
                    <span class="text-success" v-if="issetEmail">{{
                        message
                    }}</span>
                    <span class="text-danger" v-else>{{ message }}</span>
                </span>
                <button class="btn w-50 rounded" :disabled="isLoading">
                    Отправить
                </button>
            </form>
        </div>
    </div>
</template>

<style scoped lang="sass">

.input-group-text
    background-color: #dedede
    color: #fff
.btn
    background-color: #dedede
    color: #fff
</style>
