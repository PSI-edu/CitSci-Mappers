<template>
  <PageLayout>
    <div class="content-layout">
      <h1 id="page-title" class="content__title">Informed Consent & GDPR Disclosures</h1>
      <div class="content__body">
        <form id="consentForm" action="/consent" method="POST" @submit="addEmail">
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
import PageLayout from '@/components/page-layout.vue';

const formAction = ref('');

const addEmailAndRedirect = async (event) => { // Make it async to await the axios call
  event.preventDefault(); // Manually prevent default form submission first

  if (import.meta.env.VITE_LOCALHOST_DEV) {
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email');

    try {
      const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + '/user-new.php', { email: email });
      localStorage.setItem('userID', response.data);
      console.log('Success:', response.data);

      // After successful API call, now redirect to Auth0
      // This simulates the form submission by directly navigating
      window.location.href = formAction.value;

    } catch (error) {
      console.error('Error:', error);
      // Handle error, maybe show an error message to the user
      // Don't redirect if there's an error in addEmail
    }
  } else {
    console.log("addEmail not executed: VITE_LOCALHOST_DEV is false. Redirecting to Auth0 directly.");
    // If VITE_LOCALHOST_DEV is false, just proceed with Auth0 redirect
    window.location.href = formAction.value;
  }
};

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const state = urlParams.get('state');
  const baseUrl = "https://"+import.meta.env.VITE_AUTH0_DOMAIN+"/continue?state=" + state;

  const consentForm = document.getElementById('consentForm');

  consentForm.action = baseUrl;
});
</script>