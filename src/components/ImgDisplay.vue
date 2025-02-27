<template>
  <div>
    <div>
      <img :src="`${props.imageName}`" alt="Image" />
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  imageName: String,
});
  console.log("here " + props.imageName);
</script>