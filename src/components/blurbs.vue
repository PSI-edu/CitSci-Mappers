<template>
<div class="main-content" id="blurbs">
  <Blurb
      title="Lunar Melt"
      description="Help us map out ancient geologies on the Moon as we research what happens when asteroid impacts melt rock."
      image="https://moon-mappers.s3.us-east-2.amazonaws.com/Moon.png"
      link="do_science/lunar-melt"
  />
  <Blurb
      title="Mars Mosaics"
      description="Let's verify new algorithms make accurate Mars mosaics that provide clear views and pixel perfect coordinates."
      image="https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/Mars.png"
      link="do_science/mars-mosaic"
  />

  <Blurb
      title="Looking Ahead"
      description="New projects are under development. Stay tuned."
      image="https://wm-web-assets.s3.us-east-2.amazonaws.com/Rocket.png"
      link=""
  />
</div>
</template>

<script setup>
import Blurb from "@/components/blurb.vue";
</script>
