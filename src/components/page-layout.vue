<template>
  <div class="page-layout">
    <NavBar :title="title"/>
    <MobileNavBar />
    <div class="page-layout__content">
      <slot />
    </div>
    <PageFooter />
  </div>
</template>

<script setup>
import NavBar from "@/components/navigation/desktop/nav-bar.vue";
import MobileNavBar from "@/components/navigation/mobile/mobile-nav-bar.vue";
import PageFooter from "@/components/page-footer.vue";

const props = defineProps({
  title: {
    type: String
  }
});
</script>
