<template>
  <div class="nav-bar__brand">
    <div class="noFlex">
      <router-link to="/">
      World Mappers</router-link>
      {{ title }}
    </div>
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
