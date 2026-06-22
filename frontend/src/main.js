import { createApp } from "vue";
import { createPinia } from "pinia";

import App from "./App.vue";
import router from "./router";
import api from "./utils/api"

const app = createApp(App);

app.use(createPinia());
app.use(router);
window.api = api

app.mount("#app");
