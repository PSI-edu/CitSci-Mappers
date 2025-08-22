<template>
  <PageLayout>
    <div class="content-layout main-content">
      <h1 id="page-title" class="content__title">Privacy Policy</h1>

        <p id="page-description">
          Your data is important to us... for doing science and giving you credit for science done!
        </p>
        <p>
        <span class="italic">For information on how we use your science data and website activity,
          please see our <a href="/data" class="underline">Data Page.</a></span>
        </p>
        <h3>How we use your personally identifying information</h3>

      <div class="content__body">
        <div class="icon-div">
          <img class="icon" src="https://wm-web-assets.s3.us-east-2.amazonaws.com/Helmet.png" alt="Astronaut Helmet" />
        </div>
        <div class="text-blurb">
        <p>
          <strong>Username & Avatar:</strong> Currently, we don't use your username or Avatar for anything. We plan to add
          gallaries that will list the usernames of the people who have mapped each image, and use your
          usernames and avatars for things like favorites, badges, and leaderboards. We have no timescales for doing that
          but it is a goal. Claim your username now to be ready!
        </p>
        </div>
      </div>
      <div class="content__body">
        <div class="icon-div">
          <img class="icon" src="https://wm-web-assets.s3.us-east-2.amazonaws.com/Email.png" alt="Email Icon" />
        </div>
        <div class="text-blurb">
        <p>
          <strong>Email:</strong> We will never share your email with anyone else. We may use it to contact
          you about your work being used in a research paper. If you want to be contacted about your work, please
          make sure you have a valid email address in your profile. Again, we will never share your email.
        </p>
        </div>
      </div>
      <div class="content__body">
        <div class="icon-div">
          <img class="icon" src="https://wm-web-assets.s3.us-east-2.amazonaws.com/Signature.png" alt="Signature Icon" />
        </div>
        <div class="text-blurb">
        <p>
          <strong>Publishable Name:</strong> We want to give people credit for their work. The publishable name
          doesn't have to be your real name, but it can't contain swear words or anything a typical person wouldn't
          want a 6 year old saying loudly in public. We will always email you if your publishable name is going to
          appear in a research product.
        </p>
        </div>
      </div>
      <div class="content__body">
        <div class="icon-div">
          <img class="icon" src="https://wm-web-assets.s3.us-east-2.amazonaws.com/Wind.png" alt="Wind Icon" />
        </div>
        <div class="text-blurb">
        <p>
          <strong>Right to be forgotten:</strong> Anyone can request to have their personally identifying information
          deleted from our systems at anytime. Any data you have contributed will be be credited to "redacted". We can not
          delete submitted science actions (mapping, annotations, button presses, etc.) as they are part of the
          scientific record. We will not delete your data unless you ask us to do so.
        </p>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
</script>
