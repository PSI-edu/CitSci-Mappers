<template>
  <template v-if="isNoFingers">
    <PageLayout title=": Lunar Flows BETA" >
      <div class="content-layout">
        <div id="citsci-main-panel">
          <div id="citsci-buttons-panel">
            <button
                @click="setMode('zigzag-dotted'); setText(marginTitle, marginInfo); setExamples('margin')"
                :class="{'button-not-selected': mode !== 'zigzag-dotted', 'button-selected': mode === 'zigzag-dotted'}"
                style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-margin.png'); background-size: contain;"
            ></button>
            <button
                @click="setMode('zigzag-solid'); setText(cracksTitle, cracksInfo); setExamples('cracks')"
                :class="{'button-not-selected': mode !== 'zigzag-solid', 'button-selected': mode === 'zigzag-solid'}"
                style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-crack.png'); background-size: contain;"
            ></button>
            <button
                @click="setMode('zigzag-dash'); setText(ridgeTitle, ridgeInfo); setExamples('ridge')"
                :class="{'button-not-selected': mode !== 'zigzag-dash', 'button-selected': mode === 'zigzag-dash'}"
                style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-ridge.png'); background-size: contain;"
            ></button>
            <button
            <button
                @click="setMode('erase'); setText(eraseTitle, eraseInfo); setExamples('erase')"
                :class="{'button-not-selected': mode !== 'erase', 'button-selected': mode === 'erase'}"
                style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-erase.png');background-size: contain;"
            ></button>
            <button
                @click="setMode('edit'); setText(editTitle, editInfo); setExamples('erase')"
                :class="{'button-not-selected': mode !== 'edit', 'button-selected': mode === 'edit'}"
                style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-edit.png');background-size: contain;"
            ></button>
          </div>
          <div id="citsci-mapping-panel">
            <CanvasMap
                ref="canvasMapRef"
                :mode="mode"
                :drawings="drawings"
                :image-name="imageUrl"
                @draw="handleDraw"
                @clearDrawing="clearDrawing"
                @updateDrawing="handleUpdateDrawing"
            />
          </div>
          <div class="citsci-info-panel melt">
            <h5>Activity 2: Flows <span style="color: #c58336;">BETA</span></h5>
            <h3>Fractures, Flows & Channels, Ridges</h3>
            <div class="label">
              <p>Task:</p>
            </div>
            <div class="content">
              <p >We're mapping geologic features from/in flowing impact melts. </p>
            </div>
            <div class="label">
              <p>Links:</p>
            </div>
            <div class="content">
              <p>
                <a href="https://mappers.psi.edu/learn/lunar-melt/lm-the-team" target="_blank">The Team </a>
                *
                <a href="https://mappers.psi.edu/learn/lunar-melt/" target="_blank">Science </a>
                *
                <a href="https://mappers.psi.edu/learn/lunar-melt/lm-the-data/" target="_blank">Data</a>
                *
                <a href="/tutorials/lunar-melt-tutorial" target="_blank">Tutorial</a>
              </p>
            </div>

            <div style="float: right; width: 45%; padding-top:10px;">
              <h4>{{ infoTitle }}</h4>
              <p>{{ infoText }}</p>
              <p><em>Not all images have flow features!</em></p>
            </div>

            <div id="context-canvas">

              <canvas
                  ref="exampleMarks" id="exampleMarks"
                  width="150" height="150"
                  @click="openContextWindow"
                  style="
                    cursor: pointer;
                    margin: 5px;
                  "
                  title="Click to enlarge context image"
              >
              </canvas>
              <div
                  style="
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    z-index: 2999;
              ">
                <p style="color: white;">Context Image</p>
              </div>

            </div>

            <div id="citsci-imageid-panel-left">
              <h4>Image ID: {{imageID}}</h4>
              <p><span class="small">
              <a :href="imageUrl" target="_blank">view</a>,
              <a href="https://discord.com/channels/443490369443856384/1392324456869007460" target="_blank">discuss on Discord</a></span></p>
            </div>

          </div>
          <button @click="saveResponse()" class="submit-button" id="submit-button">Submit</button>
          <button class="busy-button" id="busy-button">Working....</button>
          <div class="LunarMelt citsci-examples-larger">
            <h4>Examples</h4>
            <img v-for="example in exampleImages" :key="example" :src="example" style="margin-right: 5px;" alt="Example Image" />
          </div>
        </div>
      </div>
    </PageLayout>
  </template>
  <template v-else>
    <PageLayout title=": Lunar Melt Flows" >
      <div class="content-layout">
        <p>Sorry, this tool is only available when using a pointer such as a mouse or stylus.</p>
      </div>
    </PageLayout>
  </template>
</template>


<script setup>
import { useIsNoFingers } from "@/composables/noFingers.js";
import PageLayout from "@/components/page-layout.vue";
import CanvasMap from "@/components/citsci-tools/canvas-map.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref } from 'vue';
import apiClient from '@/api/axios';
import {useRouter} from "vue-router";

const isNoFingers = useIsNoFingers();

const { user, isAuthenticated, loginWithRedirect, isLoading } = useAuth0();
const router = useRouter(); // Initialize useRouter
const currentContextUrl = ref('');

// Image and example state
const imageUrl = ref(null);
const exampleMarks = ref(null);

// Drawing State
const mode = ref('');
const drawings = ref([]);
const canvasMapRef = ref(null);

// Info panel state
const infoTitle = ref("Ready?");
const infoText = ref("Select a tool to begin marking features.");
const marginTitle = ref("Outlining Flow");
const marginInfo = ref("Click where a flow starts and follow around its edge. Done? Press [ESC] or double-click.");
const cracksTitle = ref("Tracing Cracks");
const cracksInfo = ref("Click where a crack starts, and where it bends. Done? Press [ESC] or double-click.")
const ridgeTitle = ref("Tracing Pressure Ridges");
const ridgeInfo = ref("Trace along the top of the ridge (best guess is ok!).  Done? Press [ESC] or double-click.");
const eraseTitle = ref("Erasing Mark");
const eraseInfo = ref("Click on a mark to delete it.");
const editTitle = ref("Editing Mark");
const editInfo = ref("Click on a mark to change it.");

const exampleImages = ref([]);
const imageID = ref(localStorage.getItem('image_id') || 'N/A');

const pageReady = ref(false);

const handleLogin = () => {
  loginWithRedirect();
};

const setMode = (newMode) => {
  mode.value = newMode;
  if (canvasMapRef.value) {
    canvasMapRef.value.setDrawingMode(newMode);
  }
};

const setText = (text1, text2) => {
  infoTitle.value = text1;
  infoText.value = text2;
};

// Put setExamples here TODO
function setExamples(tool) {
  const prefix = "https://moon-mappers.s3.us-east-2.amazonaws.com/examples/";
  exampleImages.value = [];
  if (tool === 'margin') {
    for (let i = 1; i <= 3; i++) {
      exampleImages.value.push(prefix + `example-margin-${i}-marked.png`);
    }
  } else if (tool === 'cracks') {
    for (let i = 1; i <= 3; i++) {
      exampleImages.value.push(prefix + `example-cracks-${i}-marked.png`);
    }
  } else if (tool === 'ridge') {
    for (let i = 1; i <= 3; i++) {
      exampleImages.value.push(prefix + `example-ridge-${i}-marked.png`);
    }
  }else {
    // For now, just show a default set
    exampleImages.value = [
      prefix + 'example-margin-1-marked.png',
      prefix + 'example-cracks-2-marked.png',
      prefix + 'example-ridge-1-marked.png',
    ];
  }
}

const handleDraw = (drawing) => {
  drawings.value.push(drawing);
};

const handleUpdateDrawing = (payload) => {
  const { index, newDrawing } = payload; // Destructure the event payload
  if (drawings.value && typeof index === 'number' && index >= 0 && index < drawings.value.length && newDrawing) {
    drawings.value[index] = newDrawing;
  } else {
    console.error("Invalid payload, index, or newDrawing for updating drawing. Payload:", payload);
  }
};

const clearDrawing = (index) => {
  drawings.value.splice(index, 1);
  if (canvasMapRef.value) {
    canvasMapRef.value.redrawCanvas();
  }
};

const saveResponse = async () => {

  // hide submit button and show busy button
  const submitButton = document.getElementById('submit-button');
  const busyButton = document.getElementById('busy-button');
  if (submitButton) {
    submitButton.style.display = 'none';
  }
  if (busyButton) {
    busyButton.style.display = 'inline';
  }

  if (!localStorage.getItem('user_id') || !localStorage.getItem('image_id')) {
    console.error("User ID or Image ID not found in local storage.");
    return;
  }
  const payload = {
    user_id: localStorage.getItem('user_id'),
    image_id: localStorage.getItem('image_id'),
    app_id: 4,
    drawings: drawings.value.map(drawing => ({
      type: drawing.type,
      data: drawing.data,
    })),
  };

  console.log("Submitting drawings:", payload);

  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/submit.php", payload);
    console.log("Submission Successful:", response.data);
    await getNewImage();

  } catch (error) {
    console.error("Error submitting drawings:", error);
    // Show the submit button and hide the busy button in case of error
    if (submitButton) {
      submitButton.style.display = 'inline';
    }
    if (busyButton) {
      busyButton.style.display = 'none';
    }
  } finally {
    // Clear drawings after submission
    drawings.value = [];
    if (canvasMapRef.value) {
      canvasMapRef.value.redrawCanvas();
    }
  }

  // Show the submit button and hide the busy button
  if (submitButton) {
    submitButton.style.display = 'inline';
  }
  if (busyButton) {
    busyButton.style.display = 'none';
  }
};

onMounted(async () => {
  // First get the user_id.
  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-getid.php", {
      email: user.value.email
    });
    localStorage.setItem('user_id',response.data);
    localStorage.setItem('email',user.value.email);
  } catch (error) {
    console.log(error);
  }

  // Check if this user has done the tutorial
  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-tutorial.php", {
      user_id: localStorage.getItem('user_id'),
      app_id: 4,
      task: "check"
    });
    if (response.data === "FALSE") {
      // router.push('/tutorials/lunar-melt-flow-tutorial');
      pageReady.value = true; // BETA
    } else {
      pageReady.value = true;
    }
    console.log("Tutorial status checked:", response.data);
  } catch (error) {
        console.error("Error checking tutorial status:", error);
  }

  // Now get the first image
  await getNewImage();

  // Set examples
  setExamples();
});

const drawContextImage = (url) => {
  if (!exampleMarks.value) return;

  const canvas = exampleMarks.value;
  const ctx = canvas.getContext('2d');
  const img = new Image();

  img.onload = () => {
    // Clear canvas before drawing
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    // Draw image to fill the canvas dimensions (100x75)
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
  };

  img.src = url;
};

const openContextWindow = () => {
  if (!currentContextUrl.value) return;

  const width = 900;
  const height = 900;

  // Calculate center of screen (optional, but professional)
  const left = (window.screen.width / 2) - (width / 2);
  const top = (window.screen.height / 2) - (height / 2);

  const features = `width=${width},height=${height},left=${left},top=${top},toolbar=no,menubar=no,scrollbars=yes`;

  window.open(currentContextUrl.value, 'ContextImage', features);
};

const getNewImage = async () => {
  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/image-get.php", {
      app_id: 4,
      user_id: localStorage.getItem('user_id')
    });

    imageUrl.value = response.data.file_location;
    localStorage.setItem('image_id', response.data.id);

    // Save to our ref so the click handler can see it
    currentContextUrl.value = response.data.file_location.replace('.png', '_context.png');

    console.log(currentContextUrl.value);

    imageID.value = response.data.id;

    // Draw the thumbnail
    drawContextImage(currentContextUrl.value);

  } catch (error) {
    console.log(error);
  }
};

</script>