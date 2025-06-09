<template>

<template v-if="isNoFingers">
  <PageLayout title=": Lunar Melt" >
    <div class="content-layout">
      <div id="citsci-main-panel">
        <div id="citsci-buttons-panel">
          <button
              @click="setMode('circle'); setText(craterTitle, craterInfo); setExamples('circle')"
              :class="{'button-not-selected': mode !== 'circle', 'button-selected': mode === 'circle'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-crater.png'); background-size: contain;"
          ></button>
          <button
              @click="setMode('line'); setText(boulderTitle, boulderInfo); setExamples('line')"
              :class="{'button-not-selected': mode !== 'line', 'button-selected': mode === 'line'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-boulder.png'); background-size: contain;"
          ></button>
          <button
              @click="setMode('dot'); setText(rocksTitle, rocksInfo); setExamples('dot')"
              :class="{'button-not-selected': mode !== 'dot', 'button-selected': mode === 'dot'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-rocks.png'); background-size: contain;"
          ></button>
          <button
              @click="setMode('erase'); setText(eraseTitle, eraseInfo); setExamples('erase')"
              :class="{'button-not-selected': mode !== 'erase', 'button-selected': mode === 'erase'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-erase.png');background-size: contain;"
          ></button>
          <button
              @click="setMode('edit'); setText(eraseTitle, eraseInfo); setExamples('erase')"
              :class="{'button-not-selected': mode !== 'edit', 'button-selected': mode === 'edit'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-edit.png');background-size: contain;"
          ></button>
        </div>
        <div id="citsci-mapping-panel">
          <CanvasMap
              :image-name="imageUrl"
              v-if="imageUrl"
              ref="canvasMapRef"
              @draw="handleDraw"
              :mode="mode"
              :drawings="drawings"
              @clearDrawing="clearDrawing"
              @updateDrawing="handleUpdateDrawing"
          />
        </div>
        <div class="citsci-info-panel melt">
          <h5>Activity 1:</h5>
          <h3>Craters, Boulders, Rocks</h3>
          <p >We are mapping geologic features related to flowing impact melt
            in the Moon's Little Lowell & Tycho craters. Long ago, the heat
            of asteroid impacts melted the regions you're mapping. Your
            work helps us understand how the melt flowed & when it cooled. </p>

          <br/>
          <h4>{{ infoTitle }}</h4>
          <p>{{ infoText }}</p>

          <div id="ex-canvas">
            <canvas
                ref="exampleMarks" id="exampleMarks"
                width="100" height="75">
            </canvas>
          </div>
        </div>
        <button
            @click="saveResponse()" class="submit-button" id = "submit-button"
        >Submit</button>
        <button
          class="busy-button" id="busy-button"
        >Working....</button>
        <div class="LunarMelt citsci-examples">
          <h4> Examples</h4>
          <img
              v-for="example in exampleImages"
              :key="example"
              :src="example"
              style="margin-right: 5px;"
              alt ="Example Image"
          />
        </div>
      </div>
    </div>
  </PageLayout>
</template>
<template v-else>
  <PageLayout title=": Lunar Melt BETA" >
    <div class="content-layout">
      <div id="citsci-main-panel">
        <div id="citsci-buttons-panel">
          <p>Sorry, this tool is only available when using a pointer such as a mouse or stylus.</p>
        </div>
      </div>
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

const isNoFingers = useIsNoFingers();

const { user } = useAuth0();

const imageUrl = ref(null);
const mode = ref(null);
const drawings = ref([]);
const canvasMapRef = ref(null);
const infoTitle = ref("Ready?");
const infoText = ref("Welcome to the lunar surface. Select a tool to begin marking features.");
const boulderTitle = ref("Now Mapping: Boulders");
const boulderInfo = ref("Click and drag to draw a line along the longest axis of the boulder. ");
const craterTitle = ref("Now Mapping: Craters");
const craterInfo = ref("Click in the center of a crater and drag the cursor out to mark its edges.");
const rocksTitle = ref("Now Mapping: Rocks");
const rocksInfo = ref("Click in the centers of rocks to mark their locations.");
const eraseTitle = ref("Erasing");
const eraseInfo = ref("Click on a mark to delete it.");
const exampleImages = ref([]);
const editTitle = ref("Editing Marks");
const editInfo = ref("Click on a mark to move or (if appropriate) resize.");

const exampleMarks = ref(null);

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

const setExamples = (tool) => {
  const prefix = "https://moon-mappers.s3.us-east-2.amazonaws.com/examples/";
  exampleImages.value = [];
  if (tool === 'line') {
    for (let i = 1; i <= 6; i++) {
      exampleImages.value.push(prefix + `example-boulder-${i}.png`);
    }
  } else if (tool === 'circle') {
    for (let i = 1; i <= 6; i++) {
      exampleImages.value.push(prefix + `example-crater-${i}.png`);
    }
  } else if (tool === 'dot') {
    for (let i = 1; i <= 6; i++) {
      exampleImages.value.push(prefix + `example-rock-${i}.png`);
    }
  } else if (tool === 'erase') {
    exampleImages.value = [
      prefix + 'example-crater-1.png',
      prefix + 'example-crater-2.png',
      prefix + 'example-boulder-1.png',
      prefix + 'example-boulder-2.png',
      prefix + 'example-rock-1.png',
      prefix + 'example-rock-2.png',
    ];
  } else {
    // Initial state or if no tool is selected
    exampleImages.value = [
      prefix + 'example-crater-1.png',
      prefix + 'example-crater-2.png',
      prefix + 'example-boulder-1.png',
      prefix + 'example-boulder-2.png',
      prefix + 'example-rock-1.png',
      prefix + 'example-rock-2.png',
    ];
  }
};

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
    app_id: 3,
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
    // Now get the first image
    await getNewImage();
  } catch (error) {
    console.log(error);
  }

  // Now get the first image
  await getNewImage();

  // Draw the example marks
  drawExampleCanvas();

  // Add the example images at the bottom
  setExamples(null);

});

const getNewImage = async () => {
  // Now get the first image
  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/image-get.php", {
      app_id: 3,
      user_id: localStorage.getItem('user_id')
    });
    imageUrl.value = response.data.file_location;
    localStorage.setItem('image_id',response.data.id);
    console.log("Image URL: " + imageUrl.value);
  } catch (error) {
    console.log(error);
  }
};

const drawExampleCanvas = () => {
  // Draw the example marks on the canvas: a 6px circle and a 6 pixel line
  const canvasExample = exampleMarks.value;
  canvasExample.width=100;
  canvasExample.height=75;
  const ctxExample = canvasExample.getContext('2d');
  //
  // // Draw example circle
  ctxExample.strokeStyle = '#b4531f'; // Orange color
  ctxExample.fillStyle = 'rgba(130,83, 31, 0.2)'; // Orange color
  ctxExample.beginPath();
  ctxExample.lineWidth = 2;
  ctxExample.arc(30, 25, 15, 0, Math.PI * 2);
  ctxExample.stroke();
  ctxExample.fill();

  // Draw example line
  ctxExample.lineWidth = 4;
  ctxExample.strokeStyle = '#6f6e2a'; // Green color
  ctxExample.beginPath();
  ctxExample.moveTo(59, 14);
  ctxExample.lineTo(81, 36);
  ctxExample.stroke();

  // Label it
  ctxExample.font = "12px sans-serif";
  ctxExample.fillStyle = "black";
  ctxExample.fillText("minimum sizes", 10, 60)
};


</script>
