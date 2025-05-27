<template>
  <div class="page-layout">
    <NavBar />
    <MobileNavBar />
    <div class="page-layout__content">
      <HeroBanner></HeroBanner>
      <Blurbs/>
      <Announcements/>
    </div>
    <PageFooter />
  </div>
</template>

<script setup>
import HeroBanner from "@/components/hero-banner.vue";
import Blurbs from "@/components/blurbs.vue";
import Announcements from "@/components/annoucements.vue";
import MobileNavBar from "@/components/navigation/mobile/mobile-nav-bar.vue";
import NavBar from "@/components/navigation/desktop/nav-bar.vue";
import PageFooter from "@/components/page-footer.vue";
</script>
