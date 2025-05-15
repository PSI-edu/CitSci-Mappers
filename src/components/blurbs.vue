<template>
<div id="blurbs">
  <Blurb
      title="Lunar Melt"
      description="Help us map out ancient geologies on the Moon."
      image="https://moon-mappers.s3.us-east-2.amazonaws.com/Moon.png"
      link="lunar-melt"
  />
  <Blurb
      title="Mars Mosaics"
      description="Let's verify new algorithms make better Mars mosaics."
      image="https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/Mars.png"
      link="mars-mosaic"
  />

  <Blurb
      title="Looking Ahead"
      description="New projects are under development. Stay tuned."
      image="https://wm-web-assets.s3.us-east-2.amazonaws.com/Rocket.png"
      link="looking-ahead"
  />
</div>
</template>

<script setup>
import Blurb from "@/components/blurb.vue";
</script>
