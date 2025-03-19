<template>
  <div @click="mobileMenuStore.closeMobileMenu" class="mobile-nav-bar__brand">
    <router-link to="/">
      World Mappers{{ title }}
    </router-link>
  </div>
</template>

<script setup>
import { mobileMenuStore } from "./mobile-menu.store.js";
import { defineProps } from 'vue';
const props = defineProps({
  title: {
    type: String,
    default: ''
  }
})
</script>
