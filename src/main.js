import { createApp } from "vue";
import { createAuth0 } from "@auth0/auth0-vue";
import App from "./app.vue";
import router from "./router";
import "./assets/css/styles.css";

// --- Step 1: Import your Axios setup ---
import { setupAxiosInterceptors } from "./api/axios"; // Assuming it's at '/src/api/axios.js'

const app = createApp(App);

// --- Step 2: Create the Auth0 instance and store it in a variable ---
const auth0 = createAuth0({
    domain: import.meta.env.VITE_AUTH0_DOMAIN,
    clientId: import.meta.env.VITE_AUTH0_CLIENT_ID,
    authorizationParams: {
        redirect_uri: import.meta.env.VITE_AUTH0_CALLBACK_URL,
        audience: import.meta.env.VITE_AUTH0_AUDIENCE,
    },
});

// --- Step 3: Use the plugins and set up the interceptor ---
app.use(router);
app.use(auth0); // Install the Auth0 plugin

// Pass the auth0 instance to our interceptor setup function.
// This must be done AFTER app.use(auth0).
setupAxiosInterceptors(auth0);

app.mount("#root");
