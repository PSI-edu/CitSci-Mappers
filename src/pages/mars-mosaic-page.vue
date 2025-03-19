<template>
  <PageLayout title=": Mars Mosaic">
    <div class="content-layout">
      <div id="citsci-main-panel">
        <div id="citsci-mapping-panel">
            <CanvasMap
                :control-name="controlUrl" v-if="controlUrl"
                :diff-name="diffUrl"
                :image-name="imageUrl"
            />
        </div>
        <div id="control_panel">
              <CanvasControl
                  :image-name="imageUrl" v-if="imageUrl"
              />
          <div id="control-instructions">
            <p>Which describes the Test Image above?</p>
          </div>
          <div id="control-buttons">
            <button class="control-button" @click="submitSharper">Sharper</button>
            <button class="control-button" @click="submitFuzzier">Fuzzier</button>
            <button class="control-button" @click="submitSame">The Same <small>(may be offset)</small></button>
            <button class="control-button-oops" @click="submitBlack">No idea - it's mostly black</button>
          </div>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import CanvasMap from "@/components/citsci-tools/canvas-compare.vue";
import CanvasControl from "@/components/citsci-tools/canvas-control.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref } from 'vue';
import axios from 'axios';

const { user } = useAuth0();

const imageUrl = ref(null);
const controlUrl = ref(null);
const diffUrl = ref(null);

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
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-getid.php", {
      email: user.value.email
    });
    localStorage.setItem('user_id',response.data);
    localStorage.setItem('email',user.value.email);
    // Now get the first image
    try {
      const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/image-get.php", {
        app_id: 2,
        user_id: localStorage.getItem('user_id')
      });
      controlUrl.value = response.data.file_location;
      imageUrl.value = controlUrl.value.replace('controlled', 'uncontrolled');
      diffUrl.value = controlUrl.value.replace('controlled', 'difference');
      localStorage.setItem('image_id',response.data.id);
    } catch (error) {
      console.log(error);
    }
  } catch (error) {
    console.log(error);
  }

});

// If someone clicks the submitSharper button, send the response to the server and get a new image
const submitSharper = () => {
  setLoadingImages();
  // Send the response to the server
  saveResponse('sharper');
  // show the control panel
  showEverything();
};

// If someone clicks the submitFuzzier button, send the response to the server and get a new image
const submitFuzzier = () => {
  setLoadingImages();
  // Send the response to the server
  saveResponse('fuzzier');
  // show the control panel
  showEverything();
};

// If someone clicks the submitSame button, send the response to the server and get a new image
const submitSame = () => {
  setLoadingImages();
  // Send the response to the server
  saveResponse('same');
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
    const res = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/submit.php", {
      app_id: 2,
      user_id: localStorage.getItem('user_id'),
      image_id: localStorage.getItem('image_id'),
      response: response
    });
    console.log(res.data);
    getNewImage();
  } catch (error) {
    console.log(error);
  }
};

// Create a function to get a new image
const getNewImage = async () => {
  try {
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/image-get.php", {
      app_id: 2,
      user_id: localStorage.getItem('user_id')
    });
    controlUrl.value = response.data.file_location;
    imageUrl.value = controlUrl.value.replace('controlled', 'uncontrolled');
    diffUrl.value = controlUrl.value.replace('controlled', 'difference');
    localStorage.setItem('image_id',response.data.id);
  } catch (error) {
    console.log(error);
  }
};


</script>
