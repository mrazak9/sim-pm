import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import { clickOutside } from './directives/clickOutside';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Register directives
app.directive('click-outside', clickOutside);

app.mount('#app');
