<template>
  <PageLayout>
    <div class="content-layout">
      <div id="citsci-main-panel">
        <CanvasMap
            :imageUrl="imageUrl" v-if="imageUrl"
        />
        <img v-else src="/src/assets/images/loading.png" alt="Loading">
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import CanvasMap from "@/components/citsci-tools/canvas-map.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref } from 'vue';
import axios from 'axios';

const { user } = useAuth0();

const imageUrl = ref(null);

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
    const imageUrl = response.data.file_location;
  } catch (error) {
    console.log(error);
  }
});
</script>
