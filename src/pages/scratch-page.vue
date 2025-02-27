<template>
  <PageLayout>
    <div class="content-layout">
      <h1 id="page-title" class="content__title">Scratch</h1>
      <div class="content__body">
        <img-display :image-name="imageName" v-if="imageName" />
        <p v-else>Loading image...</p>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import { ref, onMounted } from 'vue';
import ImgDisplay from '@/components/ImgDisplay.vue';
import axios from 'axios';

const imageName = ref(null);

const fetchImageName = async () => {
  try {
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/image-get.php", {
      app_id: 1,
      user_id: 1
    });
    imageName.value = response.data.file_location;
    console.log(imageName.value);
  } catch (error) {
    console.error('Error fetching image name:', error);
    // Handle error (e.g., display an error message)
  }
};

onMounted(fetchImageName);

</script>
