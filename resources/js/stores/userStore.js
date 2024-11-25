import axios from "axios";
import { defineStore, acceptHMRUpdate } from "pinia";
import { useToast } from 'vue-toastification'
const toast = useToast()

// Function to create a session identifier
function createSessionIdentifier() {
    axios.post('/api/session-identifiers')
        .then(response => {
            console.log('Session ID:', response.data.session_id);
            localStorage.setItem('session_id', response.data.session_id); // Store session ID in localStorage
        })
        .catch(error => {
            console.error('Error creating session identifier:', error);
        });
}

export const userStore = defineStore('user', {
    state: () => ({
        authStatus: false,
        userData: null,
        authErrorBag: {},
        orders:[]
    }),
    actions: {
        async loadStore() {
            if (localStorage.getItem('token')) {
                axios.defaults.headers['Accept'] = 'application/json';
                axios.defaults.headers['Authorization'] = `Bearer ${localStorage.getItem('token')}`;
                try {
                    const response = await axios.get('/api/auth/get-user');
                    this.userData = response.data.user;
                    this.authStatus = true
                    const notificationId = response.data.notificationId;
                    if (notificationId) {
                        localStorage.setItem('session_id', notificationId);
                        console.log('Session ID set in localStorage:', notificationId);
                    } else {
                        console.log('No existing session ID found for user');
                    }
                } catch (error) {
                    this.authErrorBag = error.response.data.errors;
                    console.error('Error loading user data:', error);
                }
            } else {
                this.authStatus = false
                this.userData = null
            }

            // Check if session ID already exists
            if (!localStorage.getItem('session_id')) {
                createSessionIdentifier(); // Create session if it doesn't exist
            }
        },
        auth(action, cred) {    
            let url = action == 'login' ? '/api/auth/login' : '/api/auth/register'
            axios.post( url, cred )
                .then(res => { 
                    this.authStatus = true
                    this.userData = res.data.user
                    localStorage.setItem('token', res.data.token)
                    axios.defaults.headers['Accept'] = 'application/json'
                    axios.defaults.headers['Authorization'] = 'Bearer ' + res.data.token
                    
                    // Проверяем и устанавливаем идентификатор сессии
                    axios.get(`/api/session-identifiers/${res.data.user.id}`)
                        .then(response => {
                            localStorage.setItem('session_id', response.data.session_id);
                        })
                        .catch(error => {
                            console.error('Error fetching session identifier:', error);
                        });

                    // Проверяем и устанавливаем идентификатор сессии
                    axios.post('/api/session-identifiers/assign-user', {
                        session_id: localStorage.getItem('session_id')
                    })
                    .then(response => {
                        console.log('Session assigned to user:', response.data);
                    })
                    .catch(error => {
                        console.error('Error assigning session to user:', error);
                    });

                    this.loadStore(); // Reload store to reflect updated session data
                 })
                .catch (err => { 
                    toast.error(err.response.data.message)
                 })
        },
        logout(){
            this.authStatus = false
            this.userData = null
            localStorage.removeItem('token')
            localStorage.removeItem('session_id'); // Удаляем идентификатор сессии
            axios.defaults.headers['Authorization'] = '';

            // Создаем новый публичный идентификатор сессии
            createSessionIdentifier();
        },
        updateUser(data){
            axios.patch( '/api/auth/update-user', data )
                .then(res => {
                    this.userData = res.data.user
                    toast.success('Изменения сохранены')
                 })
                .catch (err => { 
                    for (const [key, value] of Object.entries(err.response.data.errors)) {
                        toast.error(`${value}`)
                    }
                 })
        },
        resetPassword(email){
            axios.post('/api/auth/reset-password', {email: email})
            .then( res => { 
                toast.success(res.data.message)
             })
            .catch( err => { 
                toast.error(err.response.data.message)
             })
        }
    },
    getters:{}
} )