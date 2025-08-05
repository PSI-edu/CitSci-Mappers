<template>
  <PageLayout>
    <div class="content-layout main-content">
      <h1 id="page-title" class="content__title">Informed Consent & GDPR Disclosures</h1>
      <div class="content__body">
        <form id="consentForm" action="/consent" method="POST" @submit.prevent="handleSubmit">
          <p>This site invites you to participate in science projects by reviewing images
            and either mapping their features or indicating aspects of their content.</p>

          <p>
            We require an email address from you, and will use it to email you whenever your
            contributions are used in a research product (paper, catalogue, trained machine
            learning algorithm, etc). We will
            not use your email address for any other reason unless you sign up specifically
            to receive content (like our newsletter signup on the profile page).
            You can review how we use <a href="/data" target="_blank">data</a> and our
            <a href="/privacy" target="_blank">privacy policy</a> using the
            links in the footer. That information will be kept current as new projects are added.
          </p>
          <p>
            This website uses cookies to track if you are logged in and store information about
            your login. We manage logins and login data tracking using Auth0 by Okta.
            This is required so we can give you credit for the science you do.
          </p>
          <p>
            After you are logged in, you will be given the opportunity to set up a username and
            publishable name. These are optional. If you don't enter anything, your data will
            be attributed to Anonymous (but you will still be notified if we use your contributions.</p>
          <p>
            By checking the box below, you are consenting to the use of your email address for
            the purposes described above. You are also consenting to our use of cookies to track
            that you have logged in and to store information about your account. You are also
            consenting that we
            may use your contributions in research products (papers, catalogues, trained machine
            learning algorithms, etc).
          </p>

          <input type="checkbox" name="confirm" value="yes" required> I consent.
          <br>
          <button type="submit" :disabled="isLoading">
            {{ isLoading ? 'Processing...' : 'Submit' }}
          </button>
        </form>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import PageLayout from "@/components/page-layout.vue";

const isLoading = ref(false);
const urlParams = new URLSearchParams(window.location.search);


onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const state = urlParams.get('state');
  const baseUrl = "https://"+import.meta.env.VITE_AUTH0_DOMAIN+"/continue?state=" + state;

  const consentForm = document.getElementById('consentForm');

  if (consentForm) {
    consentForm.action = baseUrl;
  }
});

const addEmail = async () => {
  const email = urlParams.get('email');
  try {
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + '/user-new.php',
        { email: email },
        {
          headers: {
            'Authorization': 'Bearer ' + import.meta.env.VITE_MAPPERS_API_KEY
          }
        });
    localStorage.setItem('userID', response.data);
    console.log('Success:', response.data);
    return true; // Indicate success
  } catch (error) {
    console.error('Error:', error);
    return false; // Indicate failure
  }
};

const handleSubmit = async () => {
  isLoading.value = true; // Set loading to true when submission starts

  const isEmailAdded = await addEmail();

  if (isEmailAdded) {
    document.getElementById('consentForm').submit();
  } else {
    alert('Failed to add email. Please try again.');
    isLoading.value = false; // Reset loading if submission fails
  }
};
</script>