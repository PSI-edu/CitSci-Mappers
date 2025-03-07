<template>
  <div class="blurb">
    <div class="blurb-header">
      <div class="blurb-icon">
        <img src="/src/assets/images/CitSci/Moon.png" alt="Moon Icon">
      </div>
      <div class="blurb-title">{{title}}</div>
    </div>
    <div class="blurb-description">
      <div><p>{{description}}</p></div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from "vue";
const props = defineProps( {
  title: String,
  description: String,
  image: String
});

const title = props.title;
const description = props.description;
const image = props.image;
console.log(description);
</script>
