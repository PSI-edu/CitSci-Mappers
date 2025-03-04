<template>
  <PageLayout>
    <div class="content-layout">
      <h1 id="page-title" class="content__title">Profile Page</h1>
      <div class="content__body">
        <p id="page-description">
           This page will allow people to submit their publishable name.
        </p>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref } from 'vue';
import axios from 'axios';

const { user } = useAuth0();

const code = user ? JSON.stringify(user.value, null, 2) : "";

console.log(user.value.email);

onMounted(async () => {
  try {
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-getid.php", {
      email: user.value.email
    });
    console.log(response);
    localStorage.setItem('id',response.data);
    localStorage.setItem('email',user.value.email);
  } catch (error) {
    console.log(error);
  }
});

</script>
