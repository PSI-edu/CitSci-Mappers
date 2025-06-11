<template>
  <PageLayout title=": Mars Mosaic">
    <div class="content-layout mars" v-if="pageReady">

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
              <button class="mosaics-submit" id="good" @click="submitGood">Perfectly Aligned</button>
              <button class="mosaics-submit" id="warning" @click="submitWarning">Almost Aligned</button>
              <button class="mosaics-submit" id="bad" @click="submitBad">Poorly Aligned</button>
              <button class="mosaics-submit" id="error" @click="submitBlack">Something is wrong</button>
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
import {onMounted, ref} from 'vue';
import apiClient from '@/api/axios';
import {useRouter} from 'vue-router';

const {user, isAuthenticated, isLoading: auth0IsLoading} = useAuth0(); // Destructure isAuthenticated and auth0IsLoading
const router = useRouter(); // Initialize useRouter

const imageUrl = ref(null);
const controlUrl = ref(null);
const diffUrl = ref(null);

const pageReady = ref(false);

function setLoadingImages() {
  const controlButtons = document.getElementById('control-buttons');
  if (controlButtons) {
    controlButtons.style.display = 'none';
  }
  controlUrl.value = "/src/assets/images/loading.png";
  imageUrl.value = "/src/assets/images/loading.png";
  diffUrl.value = "/src/assets/images/loading.png";
}

function showEverything() {
  const controlButtons = document.getElementById('control-buttons');
  if (controlButtons) {
    controlButtons.style.display = 'block';
  }
}

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

  // Check if this user has done the tutorial
  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-tutorial.php", {
      user_id: localStorage.getItem('user_id'),
      app_id: 2,
      task: "check"
    });
    if (response.data === "FALSE") {
      router.push('/tutorials/mars-mosaic-tutorial');
    } else {
      pageReady.value = true;
    }
    console.log("Tutorial status checked:", response.data);
  } catch (error) {
    console.error("Error checking tutorial status:", error);
  }
  await getNewImage();

});

const redoTutorial =() => {
  // Redirect to the tutorial page
  router.push('/tutorials/mars-mosaic-tutorial');
};

// If someone clicks the submitGood button, send the response to the server and get a new image
const submitGood = () => {
  setLoadingImages();
  // Send the response to the server
  saveResponse('aligned');
  // show the control panel
  showEverything();
};

// If someone clicks the submitWarning button, send the response to the server and get a new image
const submitWarning = () => {
  setLoadingImages();
  // Send the response to the server
  saveResponse('warning');
  // show the control panel
  showEverything();
};

// If someone clicks the submitSame button, send the response to the server and get a new image
const submitBad = () => {
  setLoadingImages();
  // Send the response to the server
  saveResponse('bad');
  // show the control panel
  showEverything();
};

// If someone clicks the submitBlack button, send the response to the server and get a new image
const submitBlack = () => {
  setLoadingImages();
  // Send the response to the server
  saveResponse('black');
  showEverything();
};

// Create a function to save the response to the server
const saveResponse = async (response) => {
  try {
    const res = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/submit.php", {
      app_id: 2,
      user_id: localStorage.getItem('user_id'),
      image_id: localStorage.getItem('image_id'),
      response: response
    });
    getNewImage();
    console.log("Response saved successfully:", res.data);
  } catch (error) {
    console.error("Error saving response:", error);
  }
};

// Create a function to get a new image
const getNewImage = async () => {
  setLoadingImages();
  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/image-get.php", {
      app_id: 2,
      user_id: localStorage.getItem('user_id')
    });
    console.log("New image response:", response.data);
    controlUrl.value = response.data.file_location;
    imageUrl.value = controlUrl.value.replace('controlled', 'uncontrolled');
    diffUrl.value = controlUrl.value.replace('controlled', 'difference');
    localStorage.setItem('image_id', response.data.id);
  } catch (error) {
    console.log(error);
  }
  // make image one the background of the div #image1
  const image1Div = document.getElementById('image1');
  if (image1Div) {
    image1Div.style.backgroundImage = `url(${imageUrl.value})`;
    image1Div.style.backgroundSize = 'cover';
    image1Div.style.backgroundPosition = 'center';
    console.log(imageUrl.value)
  }
  // make image two the background of the div #image2
  const image2Div = document.getElementById('image2');
  if (image2Div) {
    image2Div.style.backgroundImage = `url(${controlUrl.value})`;
    image2Div.style.backgroundSize = 'cover';
    image2Div.style.backgroundPosition = 'center';
  }
};


</script>