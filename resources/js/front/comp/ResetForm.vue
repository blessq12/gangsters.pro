<script>
import { useToast } from 'vue-toastification';
const toast = useToast();
import { object, string } from 'yup';
export default {
    props: {
        token: String
    },
    data() {
        return {
            loading: false,
            data: {
                password: '',
                password_confirmation: '',
                privacy: true,
                passwordVisible: false,
            },
            schema: object({
                //
            }),
        }
    },
    methods: {
        resetPassword() {
            this.loading = true;
            let data = {
                token: this.token,
                password: this.data.password,
            }
            console.log(data);
            axios.post('/api/auth/change-password', data).then(response => {
                toast.success(response.data.message);
                window.location.href = '/';
            }).catch(error => {
                toast.error(error.response.data.message);
            }).finally(() => {
                this.loading = false;
            });
        }
    }
}
</script>


<template>

<div class="w-100 d-flex justify-content-center align-items-center" style="height: 80vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6 col-lg-4">
                    <h4>Сброс пароля</h4>
                    <p class="text-muted">
                        <small class="d-block">Пароль должен содержать не менее 6 символов, включая заглавные и строчные буквы, цифры и специальные символы.</small>
                        <small class="d-block">После сброса пароля вы сможете войти в свою учетную запись с новым паролем.</small>
                    </p>
                    <form @submit.prevent="resetPassword">
                       <div class="input-group mb-4">
                            <input :type="data.passwordVisible ? 'text' : 'password'" class="form-control" placeholder="Пароль" aria-label="Пароль" v-model="data.password">
                            
                        </div>
                        <div class="input-group mb-4">
                            <input :type="data.passwordVisible ? 'text' : 'password'" class="form-control" placeholder="Подтвердите пароль" aria-label="Подтвердите пароль" v-model="data.password_confirmation">
                            <span class="input-group-text" id="basic-addon1" @click="data.passwordVisible = !data.passwordVisible">
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="privacy" v-model="data.privacy">
                            <label class="form-check-label" for="privacy">
                                Я прочитал и принимаю <a href="/privacy" target="_blank">условия использования</a>
                            </label>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded btn-sm" :disabled="!data.privacy || !data.password || !data.password_confirmation || data.password !== data.password_confirmation">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="loading"></span>
                                <span v-else>Сбросить пароль</span>
                            </button>
                            <a href="/" class="btn btn-link btn-sm text-decoration-none text-muted">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    

</template>

<style scoped lang="sass">
</style>
