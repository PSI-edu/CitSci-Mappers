<template>
  <router-link
    :to="props.path"
    exact
    class="nav-bar__tab"
    active-class="nav-bar__tab--active"
  >
    {{ props.label }}
  </router-link>
</template>

<script setup>
const props = defineProps({
  path: String,
  label: String,
});
</script>
