<template>
  <div class="nav-bar__brand">
    <router-link to="/">
      World Mappers{{ title }}
    </router-link>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
const props = defineProps({
  title: {
    type: String,
    default: ''
  }
})

</script>
