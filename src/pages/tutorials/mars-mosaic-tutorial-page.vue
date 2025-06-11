<template>
  <div class="darken" v-if="currStep==1"></div>
  <PageLayout title=": Mars Mosaic">
    <div class="content-layout mars">

      <div id="citsci-main-panel">
        <div class="mars" >
          <div id="tutorial" :class="currentStepClass" v-if="currStep > 0">

            <!-- Navigation buttons with numbers -->
            <div class="tutorial-navigation">
              <button
                  v-for="step in tutorialSteps.slice(1)" :key="step.id"
                  @click="goToStep(step.id)"
                  :class="{ active: currStep === step.id }"
              >
                {{ step.id }}
              </button>
            </div>

            <!-- Title -->
            <h3>{{ currentStepTitle }}</h3>

            <!-- Content -->
            <div class="clear"><</div>
            <img v-if="currentStepImage1" :src="currentStepImage1" :alt="currentStepTitle" class="tutorial-image1">
            <img v-if="currentStepImage2" :src="currentStepImage2" :alt="currentStepTitle" class="tutorial-image2">
            <div v-if="currentStepImageCaption" class="image-caption">
              <p>
                {{ currentStepImageCaption }}
              </p>
            </div>
            <p v-html="currentStepContent"></p>
            <div class="tutorial-controls">
              <button @click="prevStep" v-if="currStep > 1" class="nav-button prev-button">Previous</button>
              <button v-if="currStep === 1" class="nav-button start-button">Let's go!</button>
              <button @click="handleSubmitClick" v-if="currStep === tutorialSteps.length - 1" class="end-button">Got It!
              </button>
              <button @click="nextStep" v-if="currStep < tutorialSteps.length - 1" class="nav-button next-button">Next
              </button>
            </div>
          </div>
        </div>
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
                *
                <a @onclick="redoTutorial" href="">Tutorial</a>
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

              <div v-if="submissionMade">
                <button class="mosaics-submit" id="good">Perfectly Aligned</button>
                <button class="mosaics-submit" id="warning">Almost Aligned</button>
                <button class="mosaics-submit" id="bad">Poorly Aligned</button>
                <button class="mosaics-submit" id="error">Something is wrong</button>
              </div>

              <div v-if="!submissionMade">
                <button class="mosaics-submit" @click="handleSubmitClick" id="good">Perfectly Aligned</button>
                <button class="mosaics-submit" @click="handleSubmitClick" id="warning">Almost Aligned</button>
                <button class="mosaics-submit" @click="handleSubmitClick" id="bad">Poorly Aligned</button>
                <button class="mosaics-submit" @click="handleSubmitClick" id="error">Something is wrong</button>
              </div>

              <div v-if="showNotYetMessage">
                <button class="not-yet-box">Not Yet</button>
              </div>

              <div v-if="submissionMade">
                <button class="thank-you">Thank you!</button>
              </div>

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

import {useAuth0} from "@auth0/auth0-vue";
import {onMounted, ref, computed} from 'vue';
import apiClient from '@/api/axios';
import {useRouter} from 'vue-router';

const {user, isAuthenticated, isLoading: auth0IsLoading} = useAuth0();
const router = useRouter();

const imageUrl = ref(null);
const controlUrl = ref(null);
const diffUrl = ref(null);

const showNotYetMessage = ref(false);
const submissionMade = ref(false);

// Tutorial Logic
const currStep = ref(0); // Start at 0, meaning the tutorial is not active yet

const tutorialSteps = [
  { // Step 0: Hidden/Inactive state for the tutorial
    id: 0,
    title: '',
    content: '',
    className: '',
    image1: '',
    image2: ''
  },
  {
    id: 1,
    title: "Welcome to Mars Mosaics!",
    content: "Thanks for joining our team. You are helping us build the next generation of Mars mosaics. Your work " +
        "will enable research into how Mars changes from season to season and year to year. <br><br>" +
        "This tutorial will guide you through the process of determining if two images are aligned - " +
        "a key step in making sure our mosaics provide the same location information as current, individual images. <br><br>" +
        "It turns out, software sometimes shifts images too much or too little when combining individual frames " +
        "into mosaics. (Photoshop, we're looking at you!) And sometimes, things just go wrong." +
        "Your information will help us tweak our software " +
        "until we get pixel-perfect results." +
        "<br><br>Click 'Next' to start the tutorial.",
    className: "step-1",
    image1: "https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/MM-Tutorial/MM-Tutorial-Step1.png",
    image2: "",
    imageCaption: "This is an example of where it all started, and how far we've come."
  },
  {
    id: 2,
    title: "You're comparing these images",
    content: "Our eyes can't see single pixel differences. " +
        "If something went really wrong (like one is black, or there are contrasty bands) you'll see it here. <br></br>" +
        "This <i>shouldn't</i> happen, but if it does, please click 'Something is wrong' and let us know.",
    className: "step-2",
    image1: "",
    image2: "https://wm-web-assets.s3.us-east-2.amazonaws.com/arrow-left.png"
  },
  {
    id: 3,
    title: "See changes with Overlaid & Difference images",
    content: "<strong>Overlaid</strong> images layer one image in Blue over the other in Yellow. Where they" +
        "are the same you see shades of grey. Color highlights offsets.<br><br>" +
        "<strong>Difference </strong> images are black where the images are the same and white where they differ.<br><br> " +
        "<strong>Blink images for 5sec</strong> to see changes in motion.<br><br>",
    className: "step-3",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/arrow-right.png",
    image2: "",
    imageCaption: "Click the radio buttons to switch between options."
  },
  {
    id: 4,
    title: "How good (or bad) is the alignment?",
    content: "Each example combines an Overlaid and Difference image to show you both views. ",
    className: "step-4",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/arrow-down.png",
    image2: "https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/MM-Tutorial/MM-Tutorial-Step4.png",
  },
  {
    id: 5,
    title: "Submit Your Answer",
    content: "Click the button that best matches your images. <br><br>" +
        "We'll give you new images as long as you want to help! ",
    className: "step-5",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/arrow-down.png",
    image2: ""
  }
];

const currentStep = computed(() => tutorialSteps[currStep.value]);
const currentStepTitle = computed(() => currentStep.value.title);
const currentStepContent = computed(() => currentStep.value.content);
const currentStepClass = computed(() => currentStep.value.className);
const currentStepImage1 = computed(() => currentStep.value.image1);
const currentStepImage2 = computed(() => currentStep.value.image2);
const currentStepImageCaption = computed(() => currentStep.value.imageCaption);

// Make sure they don't try and submit things to early
const handleSubmitClick = () => {
  if (currStep.value < 5) {
    showNotYetMessage.value = true;
    setTimeout(() => {
      showNotYetMessage.value = false;
    }, 5000); // Hide message after 5 seconds
    return;
  }
  if (currStep.value === 5) {
    submissionMade.value = true;
    // You can add your data submission logic here
  }
};

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

const redoTutorial =() => {
  // Redirect to the tutorial page
  router.push('/tutorials/mars-mosaic-tutorial');
};

</script>
