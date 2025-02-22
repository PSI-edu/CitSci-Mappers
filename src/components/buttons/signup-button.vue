<template>
  <button class="button__sign-up" @click="handleSignUp">Sign Up</button>
</template>

<script setup>
import { useAuth0 } from "@auth0/auth0-vue";

const { loginWithRedirect } = useAuth0();

const handleSignUp = () => {
  loginWithRedirect({
    appState: {
      target: "/profile",
    },
    authorizationParams: {
      prompt: "login",
      screen_hint: "signup",
    }
  });
};
</script>
