<template>
  <PageLayout>
    <div class="content-layout">
      <h1 id="page-title" class="content__title">Science Dashboard</h1>
      <div class="content__body">
        <p id="page-description">
          nothing to see yet.
        </p>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import {onMounted, ref} from 'vue';
import apiClient from "@/api/axios";

const { user } = useAuth0();
const email = ref(null);
const userId = ref(null);

onMounted(async () => {
  try {
    const response = await apiClient.post("/user-getid.php", {
      email: user.value.email
    });

    userId.value = response.data;
    email.value = user.value.email;
    console.log (userId.value);
  } catch (error) {
    console.log(error);
  }
});
</script>
