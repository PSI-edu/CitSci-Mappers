<template>
  <PageLayout>
    <div class="content-layout">
      <h1 id="page-title" class="content__title">Informed Consent & GDPR Disclosures</h1>
      <div class="content__body">
        <form id="consentForm" action="/consent" method="POST" @submit.prevent="addEmail">
          <p>Do you consent?</p>
          <input type="checkbox" name="confirm" value="yes" required>
          <br>
          <button type="submit">Submit</button>
        </form>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
const urlParams = new URLSearchParams(window.location.search);
const state = urlParams.get('state');
</script>

<script>
import axios from "axios";

export default {
  data() {
    return {
      isChecked: true,
    };
  },
  mounted() {
    const urlParams = new URLSearchParams(window.location.search);
    const state = urlParams.get('state');
    const baseUrl = "https://"+import.meta.env.VITE_AUTH0_DOMAIN+"/continue?state=" + state;

    const consentForm = document.getElementById('consentForm');

    consentForm.action = baseUrl;
  },
  methods: {
    addEmail() {
      const urlParams = new URLSearchParams(window.location.search);
      const email = urlParams.get('email');

      axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + '/user-new.php', { email: email })
          .then(response => {
            console.log('Success:', response.data);
          })
          .catch(error => {
            console.error('Error:', error);
          });
    }
  }
}
</script>
