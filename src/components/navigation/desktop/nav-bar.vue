<template>
  <div class="nav-bar__container">
    <nav class="nav-bar">
      <NavBarBrand :title="title"/>
      <NavBarTabs />
      <NavBarButtons />
    </nav>
  </div>
</template>

<script setup>
import NavBarBrand from "@/components/navigation/desktop/nav-bar-brand.vue";
import NavBarButtons from "@/components/navigation/desktop/nav-bar-buttons.vue";
import NavBarTabs from "@/components/navigation/desktop/nav-bar-tabs.vue";

const props = defineProps({
  title: {
    type: String
  }
});
</script>
