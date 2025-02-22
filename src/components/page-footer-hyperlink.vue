<template>
  <a
    :href="props.path"
    class="page-footer__hyperlink"
    rel="noopener noreferrer"
  >
    <slot />
  </a>
</template>

<script setup>
const props = defineProps({
  path: String,
});
</script>
