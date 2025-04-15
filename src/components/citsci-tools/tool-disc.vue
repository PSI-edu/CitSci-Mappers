<template>
  <button @click="handleClick">Draw Disc</button>
</template>

<script setup>
import { defineEmits } from 'vue';

const emit = defineEmits(['modeChange']);

const handleClick = () => {
  emit('modeChange', 'disc');
};
</script>