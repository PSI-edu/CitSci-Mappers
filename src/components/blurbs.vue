<template>
<div id="blurbs">
  <Blurb
      title="Moon Mapper"
      description="Help us map out ancient geologies on the Moon."
      image="/src/assets/images/CitSci/Moon.png"
      link="moon-mapper"
  />
  <Blurb
      title="Mars Mosaics"
      description="Let's verify new algorithms make better Mars mosaics."
      image="/src/assets/images/CitSci/Mars.png"
      link="mars-mosaic"
  />

  <Blurb
      title="Looking Ahead"
      description="New projects are under development. Stay tuned."
      image="/src/assets/images/CitSci/Rocket.png"
      link="looking-ahead"
  />
</div>
</template>

<script setup>
import Blurb from "@/components/blurb.vue";
</script>
