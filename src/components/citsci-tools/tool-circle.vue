<template>
  <button @click="handleClick">Draw Circle</button>
</template>

<script setup>
import { defineEmits } from 'vue';

const emit = defineEmits(['modeChange']);

const handleClick = () => {
  emit('modeChange', 'circle');
};
</script>