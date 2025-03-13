<template>
  <div class="blurb">
    <a :href="link" class="blurb-link">
    <div class="blurb-header">
      <div class="blurb-icon">
        <img :src="imageUrl" alt="icon">
      </div>
      <div class="blurb-title">{{title}}</div>
    </div>
    </a>
    <div class="blurb-description">
      <div><p>{{description}}</p></div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from "vue";
import { computed } from "vue";

const props = defineProps( {
  title: String,
  description: String,
  image: String,
  link: String
});

const title = props.title;
const description = props.description;
console.log(props.image);


const imageUrl = computed(() => {
  return props.image;
});

const link = computed(() => {
  return props.link;
});

</script>