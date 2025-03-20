<template>
  <PageLayout>
    <div class="content-layout">
      <h1 id="page-title" class="content__title">Profile Page</h1>
      <div class="content__body">
        <p id="page-description">
           This page will allow people to submit their publishable name.
        </p>
        <br/>
      <h4>Profile</h4>

        <UpdateProfileForm
          :email="email"
          :userId="userId" v-if="userId"
        />

        <br/>
      <h4>Stats</h4>
      <p>Coming Soon</p>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import UpdateProfileForm from "@/components/update-profile-form.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref } from 'vue';
import axios from 'axios';

const { user } = useAuth0();

console.log(user.value.email);
const email = ref(null);
const userId = ref(null);

onMounted(async () => {
  try {
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-getid.php", {
      email: user.value.email
    });
    console.log(response.data);
    userId.value = response.data;
    email.value = user.value.email;
  } catch (error) {
    console.log(error);
  }
});
</script>
