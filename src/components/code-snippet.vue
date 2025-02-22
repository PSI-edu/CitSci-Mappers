<template>
  <div class="code-snippet">
    <span class="code-snippet__title">{{ props.title }}</span>
    <div class="code-snippet__container">
      <div class="code-snippet__wrapper">
        <pre class="code-snippet__body">{{ props.code }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  code: String,
  title: String,
});
</script>
