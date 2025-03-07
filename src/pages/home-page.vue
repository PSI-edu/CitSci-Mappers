<template>
  <div class="page-layout">
    <NavBar />
    <MobileNavBar />
    <HeroBanner></HeroBanner>
    <div class="page-layout__content">
      <Blurbs/>
    </div>
    <PageFooter />
  </div>
</template>

<script setup>
import HeroBanner from "@/components/hero-banner.vue";
import Blurbs from "@/components/blurbs.vue";
import MobileNavBar from "@/components/navigation/mobile/mobile-nav-bar.vue";
import NavBar from "@/components/navigation/desktop/nav-bar.vue";
import PageFooter from "@/components/page-footer.vue";
</script>
