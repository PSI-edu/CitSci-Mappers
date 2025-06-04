<template>
  <a
      v-if="props.external"
      :href="props.path"
      target="_blank"
      rel="noopener noreferrer"
      class="nav-bar__tab"
  >
    {{ props.label }}
  </a>
  <router-link
      v-else
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
  path: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  external: {
    type: Boolean,
    default: false, // Default to internal link if not specified
  },
});

// console.log(props.path, props.external); // You can use this for debugging
</script>
