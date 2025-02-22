<template>
  <div class="nav-bar__tabs">
    <template v-if="isAuthenticated">
      <NavBarTab path="/protected" label="Protected" />
      <NavBarTab path="/profile" label="Profile" />
    </template>
  </div>
</template>

<script setup>
import NavBarTab from "@/components/navigation/desktop/nav-bar-tab.vue";
import { useAuth0 } from "@auth0/auth0-vue";

const { isAuthenticated } = useAuth0();
</script>
