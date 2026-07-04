<template>
<div class="main-content" id="blurbs">
  <Blurb
      title="Map Lunar Features"
      description="Help us map out ancient lunar geologies as we research what happens when asteroid impacts and melt
                    flows move boulders, scatter rocks, and get cratered over time."
      image="https://moon-mappers.s3.us-east-2.amazonaws.com/Moon.png"
      link="do_science/lunar-melt"
  />
  <Blurb
      title="Trace Lunar Flows"
      description="Help us map out features form from or in impact melts on the Moon, including melt flows and channels, surface cracks,
                    as well as pressure ridges."
      image="https://moon-mappers.s3.us-east-2.amazonaws.com/Moon.png"
      link="do_science/lunar-melt-flows"
  />
  <Blurb
      title="Study Mars Mosaics"
      description="Let's verify new algorithms make accurate Mars mosaics that provide clear views and pixel perfect coordinates. NOTE:
                  we are currently awaiting new data. Stay tuned!"
      image="https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/Mars.png"
      link="do_science/mars-mosaic"
  />
</div>
</template>

<script setup>
import Blurb from "@/components/blurb.vue";
</script>
