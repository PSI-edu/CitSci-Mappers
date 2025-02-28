<template>
  <PageLayout>
    <div class="content-layout">
      <div id="citsci-main-panel">
        <CanvasMap
            :control-name="controlUrl" v-if="controlUrl"
            :diff-name="diffUrl"
            :image-name="imageUrl"
        />
        <img v-else src="/src/assets/images/loading.png" alt="Loading">
        <CanvasControl
            :image-name="imageUrl" v-if="imageUrl"
        />
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import CanvasMap from "@/components/citsci-tools/canvas-compare.vue";
import CanvasControl from "@/components/citsci-tools/canvas-control.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref } from 'vue';
import axios from 'axios';

const { user } = useAuth0();

const imageUrl = ref(null);
const controlUrl = ref(null);
const diffUrl = ref(null);

onMounted(async () => {

  // First get the user_id.
  try {
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-getid.php", {
      email: user.value.email
    });
    localStorage.setItem('id',response.data);
    localStorage.setItem('email',user.value.email);
  } catch (error) {
    console.log(error);
  }

  // Now get the first image
  try {
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/image-get.php", {
      app_id: 1,
      user_id: localStorage.getItem('id')
    });
    console.log(response.data);
    controlUrl.value = response.data.file_location;
    imageUrl.value = controlUrl.value.replace('controlled', 'uncontrolled');
    diffUrl.value = controlUrl.value.replace('controlled', 'difference');
    console.log(imageUrl.value);
  } catch (error) {
    console.log(error);
  }
});
</script>
