<template>
  <div class="hero-banner hero-banner--yellow-mandarine">
    <div class="hero-banner__logo">
      <img class="hero-banner__image" :src="logo" alt="Vue 3 logo" />
    </div>
    <h1 id="page-title" class="hero-banner__headline">Welcome, Mappers!</h1>
    <p id="page-description" class="hero-banner__description">
      <span>
        We've got worlds to explore.
      </span>
    </p>
  </div>
</template>

<script setup>
const logo = "/src/assets/images/icon.svg";
</script>
