<template>
  <div id="mc_embed_shell">
    <div id="mc_embed_signup">
      <h4>Receive Mappers News</h4>
      <p>We'll only send you updates related to science projects here at World Mappers. You'll get an email whenever
      we launch a new project, or when new results are presented or published. These emails are provided by our
      partners at CosmoQuest and sent through Mailchimp.</p>
      <form
          action="https://psi.us10.list-manage.com/subscribe/post?u=f3326717f0f4b2535dfc89d72&amp;id=11ad0c11c2&amp;f_id=000248e4f0"
          method="post"
          id="mc-embedded-subscribe-form"
          name="mc-embedded-subscribe-form"
          class="validate"
          target="_blank"
          rel="noopener noreferrer"
      >
          <div>
            <label for="mce-EMAIL">Email Address:</label>
            <input type="email" name="EMAIL" class="required email" id="mce-EMAIL" required="" value="">
          </div>

          <input type="hidden" name="b_f3326717f0f4b2535dfc89d72_11ad0c11c2">
          <button type="submit" name="subscribe" value="Subscribe">Subscribe</button>
        <div id="mce-responses" class="clear">
          <div class="response" id="mce-error-response" style="display: none;"></div>
          <div class="response" id="mce-success-response" style="display: none;"></div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

// Function to dynamically load the Mailchimp script and CSS
const loadMailchimpAssets = () => {
  // Load Mailchimp JavaScript
  if (!document.getElementById('mc-validate-script')) {
    const script = document.createElement('script');
    script.src = "//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js";
    script.type = "text/javascript";
    script.id = "mc-validate-script"; // Add an ID to prevent duplicate loading
    script.onload = () => {
      // This part is the Mailchimp inline script.
      // It's important to run it *after* mc-validate.js is loaded.
      // Ensure jQuery is available before running this.
      if (typeof jQuery !== 'undefined') {
        (function($) {
          window.fnames = new Array();
          window.ftypes = new Array();
          fnames[0]='EMAIL';ftypes[0]='email';
          fnames[1]='FNAME';ftypes[1]='text';
          fnames[2]='LNAME';ftypes[2]='text';
          fnames[3]='ADDRESS';ftypes[3]='address';
          fnames[4]='PHONE';ftypes[4]='phone';
          fnames[5]='MMERGE5';ftypes[5]='text';
          fnames[6]='MMERGE6';ftypes[6]='text';
          fnames[7]='MMERGE7';ftypes[7]='text';
          fnames[8]='MMERGE8';ftypes[8]='text';
        }(jQuery));
        var $mcj = jQuery.noConflict(true);
      } else {
        console.warn("jQuery not found after Mailchimp script loaded. Form validation might not work.");
      }
    };
    document.body.appendChild(script);
  }
};

onMounted(() => {
  loadMailchimpAssets();
});
</script>

<style scoped>
/* Basic styling for the form */
form {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 400px;
  margin: 20px 0;
}
</style>
