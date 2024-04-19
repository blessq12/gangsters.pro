
import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { MaskInput, vMaska } from 'maska';
import Toast from 'vue-toastification';
import { useToast } from 'vue-toastification';
import "vue-toastification/dist/index.css";
import moment from 'moment';
/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});

const options = {
}
app.use( Toast , options)
app.use( moment );
app.use(createPinia());
app.directive('maska', vMaska);



/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 */

Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
    app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
});

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount('#app');
new MaskInput("[data-maska]")
