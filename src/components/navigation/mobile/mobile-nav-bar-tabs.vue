<template>
  <div class="mobile-nav-bar__tabs">
    <template v-if="isAuthenticated">
      <MobileNavBarTab path="/" label="Home" />
      <MobileNavBarTab path="/profile" label="Profile" />
      <MobileNavBarTab path="/lunar-melt" label="Lunar Melt" />
      <MobileNavBarTab path="/mars-mosaic" label="Mars Mosaics" />
    </template>
  </div>
</template>

<script setup>
import MobileNavBarTab from "@/components/navigation/mobile/mobile-nav-bar-tab.vue";
import { useAuth0 } from "@auth0/auth0-vue";

const { isAuthenticated } = useAuth0();
</script>

<style scoped></style>
