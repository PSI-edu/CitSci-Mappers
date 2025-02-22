<template>
  <span
    id="mobile-menu-toggle-button"
    @click="mobileMenuStore.toggleMobileMenu()"
    class="mobile-nav-bar__toggle material-icons"
  >
    {{ mobileMenuStore.mobileMenuIcon }}
  </span>
</template>

<script setup>
import { mobileMenuStore } from "./mobile-menu.store.js";
</script>
