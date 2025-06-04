<template>
  <div class="nav-bar__tabs">
    <NavBarTab path="/" label="Do Science" />
    <NavBarTab path="https://mappers.psi.edu/learn" label="Learn" external />
    <template v-if="isAuthenticated">
      <NavBarTab path="/profile" label="Profile" />
      <NavBarTab path="/lunar-melt" label="Lunar Melt" />
      <NavBarTab path="/mars-mosaic" label="Mars Mosaics" />
    </template>
  </div>
</template>

<script setup>
import NavBarTab from "@/components/navigation/desktop/nav-bar-tab.vue";
import { useAuth0 } from "@auth0/auth0-vue";

const { isAuthenticated } = useAuth0();
</script>
