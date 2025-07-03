<template>
  <div class="nav-bar__dropdown-container" @mouseenter="openDropdown" @mouseleave="closeDropdown">
    <div class="nav-bar__tab nav-bar__dropdown-toggle">
      {{ label }}
      <span class="dropdown-arrow">▼</span> </div>
    <div v-if="isOpen" class="nav-bar__dropdown-menu">
      <router-link
          v-for="item in items"
          :key="item.path"
          :to="item.path"
          class="nav-bar__dropdown-item"
          active-class="nav-bar__dropdown-item--active"
          @click="closeDropdown"
      >
        {{ item.label }}
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  items: {
    type: Array,
    required: true,
    validator: (value) => value.every(item => item.path && item.label),
  },
});

const isOpen = ref(false);

const openDropdown = () => {
  isOpen.value = true;
};

const closeDropdown = () => {
  isOpen.value = false;
};
</script>
