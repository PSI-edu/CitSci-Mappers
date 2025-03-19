<template>
  <div class="nav-bar__tabs">
    <template v-if="isAuthenticated">
      <NavBarTab path="/" label="Home" />
      <NavBarTab path="/profile" label="Profile" />
      <NavBarTab path="/mars-mosaic" label="Mars Mosaics" />
    </template>
  </div>
</template>

<script setup>
import NavBarTab from "@/components/navigation/desktop/nav-bar-tab.vue";
import { useAuth0 } from "@auth0/auth0-vue";

const { isAuthenticated } = useAuth0();
</script>
