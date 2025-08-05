<template>
  <PageLayout>
    <div class="content-layout main-content">
      <h1 id="page-title" class="content__title">Profile Page</h1>
      <div class="content__body">
        <p id="page-description">
          This page will allows you to tell us what name to use in publications.
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
        <br/>
        <MailchimpSignupForm />
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import UpdateProfileForm from "@/components/update-profile-form.vue";
import MailchimpSignupForm from "@/components/MailchimpSignupForm.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref } from 'vue';
import apiClient from '@/api/axios'; // Use our configured client

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
  } catch (error) {
    console.log(error);
  }
});
</script>
