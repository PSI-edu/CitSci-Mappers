<template>
  <PageLayout>
    <div class="content-layout">
      <h1 id="page-title" class="content__title">Informed Consent & GDPR Disclosures</h1>
      <div class="content__body">
        <form id="consentForm" action="/consent" method="POST" @submit.prevent="addEmail">
          <p>This site invites you to participate in science projects by reviewing images
            and either mapping their features or indicating aspects of their content. We
            require an email address from you, and will use it to email you whenever your
            contributions are used in a research product (paper, cataglogue, machine learning, etc). We will
            not use your email address for any other reason unless you sign up specifically
            to be contacted. You can review how we use data and our privacy policy using the
            links in the footer. That information will be kept current as new projects as are added.
          </p>
          <p>
            This website uses cookies to track if you are logged in and store information about
            your login. Our logins are done using Auth0 by Okta. This is required so we can
            give you credit for the science you do.
          </p>
          <p>
            After you are logged, in you will be given the opportunity to setup a username and
            publishable name. These are optional. If you don't enter anything, your data will
            be attributed to Anonymous (but you will still be notified if we use your data.</p>
          <p>
            By checking the box below, you are consenting to the use of your email address for
            the purposes described above. You are also consenting to the use of cookies to track
            your login and store information about your login. You are also consenting that we
            may use your data in research products (papers, catalogues, machine learning, etc).
          </p>

          <input type="checkbox" name="confirm" value="yes" required> I consent.
          <br>
          <button type="submit">Submit</button>
        </form>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import PageLayout from "@/components/page-layout.vue";

const formAction = ref('');
const urlParams = new URLSearchParams(window.location.search);
const state = urlParams.get('state');

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const state = urlParams.get('state');
  const baseUrl = "https://"+import.meta.env.VITE_AUTH0_DOMAIN+"/continue?state=" + state;

  const consentForm = document.getElementById('consentForm');

  consentForm.action = baseUrl;
});

const addEmail = () => {
  const email = urlParams.get('email');

  axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + '/user-new.php',
      { email: email },
  {
        headers: {
          'Authorization': 'Bearer ' + import.meta.env.VITE_MAPPERS_API_KEY
        }
      })
      .then(response => {
        localStorage.setItem('userID', response.data);
        console.log('Success:', response.data);
      })
      .catch(error => {
        console.error('Error:', error);
      });
};
</script>