<template>
        <CanvasMap
            image-name="https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/controlled/D02_028007_1921_XN_12N268W.l1.map/D02_028007_1921_XN_12N268W.l1.map.png"
            :rectangles ="json" v-if="json"
        />
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import CanvasMap from "@/components/citsci-tools/canvas-unbounded.vue";
import axios from 'axios';
import { onMounted, ref } from 'vue';

const json = ref(null);

onMounted(async () => {

  // First get the user_id.
  try {
    const response = await axios.get(import.meta.env.VITE_MAPPERS_API_SERVER + '/masterimage.php');
    json.value = response.data;
    //console.log( JSON.stringify(json, null, 2));
  } catch (error) {
    console.log(error);
  }
});
</script>
