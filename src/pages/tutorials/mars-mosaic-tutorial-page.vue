<template>
  <div class="darken" v-if="currStep > 0"></div>
  <div id="tutorial" :class="currentStepClass" v-if="currStep > 0">
    <h3>{{ currentStepTitle }}</h3>
    <div class="tutorial-navigation">
      <button
          v-for="step in tutorialSteps.slice(1)" :key="step.id"
          @click="goToStep(step.id)"
          :class="{ active: currStep === step.id }"
      >
        {{ step.id }}
      </button>
    </div>
    <div class="clear"><</div>
    <img v-if="currentStepImage" :src="currentStepImage" :alt="currentStepTitle" class="tutorial-image">
    <p v-html="currentStepContent"></p>
    <div class="tutorial-controls">
      <button @click="prevStep" v-if="currStep > 1" class="nav-button prev-button">Previous</button>
      <button v-if="currStep === 1" class="nav-button start-button">Let's go!</button>
      <button @click="endTutorial" v-if="currStep === tutorialSteps.length - 1" class="end-button">Got It!</button>
      <button @click="nextStep" v-if="currStep < tutorialSteps.length - 1" class="nav-button next-button">Next</button>
    </div>
  </div>
  <PageLayout title=": Mars Mosaic">
    <div class="content-layout mars">

      <div id="citsci-main-panel">
        <div style="width:480px; float:left;">
          <div class="citsci-info-panel mosaic">
            <h5>Keeping Coordinated</h5>
            <h3>Are these images aligned?</h3>
            <div class="label">
              <p>Task:</p>
            </div>
            <div class="content">
              <p>Let's verify new algorithms make accurate Mars mosaics that provide clear views & pixel perfect
                coordinates.</p>
            </div>
            <div class="label">
              <p>Links:</p>
            </div>
            <div class="content">
              <p>
                <a href="https://mappers.psi.edu/learn/mars-mosiacs/mm-the-team" target="_blank">The Team </a>
                *
                <a href="https://mappers.psi.edu/learn/mars-mosiacs/" target="_blank">Science </a>
                *
                <a href="https://mappers.psi.edu/learn/mars-mosiacs/mm-the-data/" target="_blank">Data</a>
              </p>
            </div>

            <div class="context-image">
              <h5 style="text-align:center;">You're comparing these images</h5>
              <div id="label1">
                <p>Image 1</p>
              </div>
              <div id="label2">
                <p>Image 2</p>
              </div>
              <div id="image1">

              </div>
              <div id="image2">

              </div>
            </div>
          </div>
          <div class="citsci-info-panel mosaic-examples">
            <h4>Examples of different alignments</h4>
            <img class="align-good"
                 src="https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/examples/Example-Mosaics-PerfectlyAligned.png"
                 alt="Perfectly Aligned Example Image">
            <img class="align-warning"
                 src="https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/examples/Example-Mosaics-AlmostAligned.png"
                 alt="Almost Aligned Example Image">
            <img class="align-bad"
                 src="https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/examples/Example-Mosaics-PoorlyAligned.png"
                 alt="Poorly Aligned Example Image">
            <div class="label"><h5>Perfectly Aligned</h5></div>
            <div class="label"><h5>Almost Aligned</h5></div>
            <div class="label no-right-margin"><h5>Poorly Aligned</h5></div>
          </div>
        </div>
        <div style="width: 450px; float: left;">
          <div id="citsci-mapping-panel">
            <CanvasMap
                :control-name="controlUrl" v-if="controlUrl"
                :diff-name="diffUrl"
                :image-name="imageUrl"
            />
            <div id="mosaic-submit-panel">
              <h4>These images are...? <small>click the button that matches best.</small></h4>
              <button class="mosaics-submit" id="good" >Perfectly Aligned</button>
              <button class="mosaics-submit" id="warning" >Almost Aligned</button>
              <button class="mosaics-submit" id="bad" >Poorly Aligned</button>
              <button class="mosaics-submit" id="error" >Something is wrong</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import CanvasMap from "@/components/citsci-tools/canvas-compare.vue";

import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref, computed } from 'vue';
import apiClient from '@/api/axios';
import { useRouter } from 'vue-router';

const { user, isAuthenticated, isLoading: auth0IsLoading } = useAuth0();
const router = useRouter();

const imageUrl = ref(null);
const controlUrl = ref(null);
const diffUrl = ref(null);

// Tutorial Logic
const currStep = ref(0); // Start at 0, meaning the tutorial is not active yet

const tutorialSteps = [
  { // Step 0: Hidden/Inactive state for the tutorial
    id: 0,
    title: '',
    content: '',
    className: '',
    image: ''
  },
  {
    id: 1,
    title: "Welcome to Mars Mosaics!",
    content: "This tutorial will guide you through the process of aligning Mars images. Click a number below to navigate, or 'Next' to proceed.",
    className: "step-1",
    image: "https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/examples/Example-Mosaics-PerfectlyAligned.png" // Example image
  },
  {
    id: 2,
    title: "Understanding the Task",
    content: "Your goal is to determine if the two images in the main panel are aligned. Look for features that match up.",
    className: "step-2",
    image: "" // No image for this step
  },
  {
    id: 3,
    title: "Image Comparison",
    content: "Image 1 (left) is the image you need to align, and Image 2 (right) is the reference image. You can click and drag to move them.",
    className: "step-3",
    image: "https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/examples/Example-Mosaics-AlmostAligned.png" // Example image
  },
  {
    id: 4,
    title: "Example Alignments",
    content: "Below are examples of perfectly aligned, almost aligned, and poorly aligned images. Use these as a guide.",
    className: "step-4",
    image: "" // No image for this step
  },
  {
    id: 5,
    title: "Submitting Your Answer",
    content: "Once you've made your decision, select the appropriate button: 'Perfectly Aligned', 'Almost Aligned', 'Poorly Aligned', or 'Something is wrong'.",
    className: "step-5",
    image: "" // No image for this step
  }
];

const currentStep = computed(() => tutorialSteps[currStep.value]);
const currentStepTitle = computed(() => currentStep.value.title);
const currentStepContent = computed(() => currentStep.value.content);
const currentStepClass = computed(() => currentStep.value.className);
const currentStepImage = computed(() => currentStep.value.image);

const goToStep = (stepId) => {
  currStep.value = stepId;
};

const nextStep = () => {
  if (currStep.value < tutorialSteps.length - 1) {
    currStep.value++;
  }
};

const prevStep = () => {
  if (currStep.value > 1) { // Ensure we don't go below step 1 (where the tutorial starts)
    currStep.value--;
  }
};

const endTutorial = () => {
  currStep.value = 0; // Set to 0 to hide the tutorial div and darken overlay
};

const startTutorial = () => {
  currStep.value = 1; // Start from the first actual tutorial step
};

onMounted(async () => {
  // First get the user_id.
  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-getid.php", {
      email: user.value.email
    });
    localStorage.setItem('user_id', response.data);
    localStorage.setItem('email', user.value.email);
  } catch (error) {
    console.log(error);
  }

  // Set the image URLs.
  controlUrl.value = "https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/controlled/D02_028007_1921_XN_12N268W.l1.map/D02_028007_1921_XN_12N268W.l1.map_3240-1215.png";
  imageUrl.value = controlUrl.value.replace('controlled', 'uncontrolled');
  diffUrl.value = controlUrl.value.replace('controlled', 'difference');

  // make image one the background of the div #image1
  const image1Div = document.getElementById('image1');
  if (image1Div) {
    image1Div.style.backgroundImage = `url(${imageUrl.value})`;
    image1Div.style.backgroundSize = 'cover';
    image1Div.style.backgroundPosition = 'center';
  }
  // make image two the background of the div #image2
  const image2Div = document.getElementById('image2');
  if (image2Div) {
    image2Div.style.backgroundImage = `url(${controlUrl.value})`;
    image2Div.style.backgroundSize = 'cover';
    image2Div.style.backgroundPosition = 'center';
  }

  startTutorial();
});
</script>
