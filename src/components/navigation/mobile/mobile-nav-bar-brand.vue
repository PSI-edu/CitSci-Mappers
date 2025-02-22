<template>
  <div @click="mobileMenuStore.closeMobileMenu" class="mobile-nav-bar__brand">
    <router-link to="/">
      World Mappers
    </router-link>
  </div>
</template>

<script setup>
import { mobileMenuStore } from "./mobile-menu.store.js";
</script>
