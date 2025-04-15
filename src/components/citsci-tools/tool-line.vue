<template>
  <button @click="handleClick">Draw Line</button>
</template>

<script setup>
import { defineEmits } from 'vue';

const emit = defineEmits(['modeChange']);

const handleClick = () => {
  emit('modeChange', 'line');
};
</script>
