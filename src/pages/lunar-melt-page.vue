<template>
  <PageLayout title=": Lunar Melt">
    <div class="content-layout">
      <div id="citsci-main-panel">
        <div id="citsci-buttons-panel">
          <button
              @click="setMode('line'); setInfoText(boulderInfo); setExamples('line')"
              :class="{'button-not-selected': mode !== 'line', 'button-selected': mode === 'line'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-boulder.png'); background-size: contain;"
          ></button>
          <button
              @click="setMode('circle'); setInfoText(craterInfo); setExamples('crater')"
              :class="{'button-not-selected': mode !== 'circle', 'button-selected': mode === 'circle'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-crater.png'); background-size: contain;"
          ></button>
          <button
              @click="setMode('dot'); setInfoText(rocksInfo); setExamples('rocks')"
              :class="{'button-not-selected': mode !== 'dot', 'button-selected': mode === 'dot'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-rocks.png'); background-size: contain;"
          ></button>
          <button
              @click="setMode('erase'); setInfoText(eraseInfo); setExamples('erase')"
              :class="{'button-not-selected': mode !== 'erase', 'button-selected': mode === 'erase'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-erase.png');background-size: contain;"
          ></button>
          <button @click="submitDrawings" class="submit-button">Submit</button>
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
          />
        <div id="citsci-info-panel">{{ infoText }}</div>
        </div>
        <div id="citsci-examples">
          <img
              v-for="example in exampleImages"
              :key="example"
              :src="'@/assets/' + example"
              width="100"
              height="130"
              style="margin-right: 5px;"
          />
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";
import CanvasMap from "@/components/citsci-tools/canvas-map.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';

const { user } = useAuth0();

const imageUrl = ref(null);
const mode = ref(null);
const drawings = ref([]);
const canvasMapRef = ref(null);
const infoText = ref("Welcome to the lunar surface. Select a tool to begin marking features.");
const boulderInfo = ref("Use this tool to draw lines indicating the length and direction of boulders on the moon's surface. Click and drag to create a line.");
const craterInfo = ref("Select this tool to draw circles around craters. Click and drag to define the radius of the crater.");
const rocksInfo = ref("Use this tool to place individual markers on rocks of interest. Simply click on the rock to place a dot.");
const eraseInfo = ref("Select the eraser tool and click near a mark you wish to remove. This will delete the selected annotation.");
const exampleImages = ref([]);
const setMode = (newMode) => {
  mode.value = newMode;
  if (canvasMapRef.value) {
    canvasMapRef.value.setDrawingMode(newMode);
  }
};

const setInfoText = (text) => {
  infoText.value = text;
};

const setExamples = (tool) => {
  exampleImages.value = [];
  if (tool === 'line') {
    for (let i = 1; i <= 5; i++) {
      exampleImages.value.push(`example-line-${i}.png`);
    }
  } else if (tool === 'circle') {
    for (let i = 1; i <= 5; i++) {
      exampleImages.value.push(`example-crater-${i}.png`);
    }
  } else if (tool === 'rocks') {
    for (let i = 1; i <= 5; i++) {
      exampleImages.value.push(`example-rocks-${i}.png`);
    }
  } else if (tool === 'erase') {
    exampleImages.value = [
      'example-crater-1.png',
      'example-line-1.png',
      'example-rocks-1.png',
    ];
  } else {
    // Initial state or if no tool is selected
    exampleImages.value = [
      'example-crater-1.png',
      'example-line-1.png',
      'example-rocks-1.png',
    ];
  }
};

const handleDraw = (drawing) => {
  drawings.value.push(drawing);
  console.log("Current Drawings:", drawings.value);
};

const clearDrawing = (index) => {
  drawings.value.splice(index, 1);
  if (canvasMapRef.value) {
    canvasMapRef.value.redrawCanvas();
  }
};

const submitDrawings = async () => {
  if (!localStorage.getItem('user_id') || !localStorage.getItem('image_id')) {
    console.error("User ID or Image ID not found in local storage.");
    return;
  }

  const payload = {
    user_id: localStorage.getItem('user_id'),
    image_id: localStorage.getItem('image_id'),
    drawings: drawings.value.map(drawing => ({
      type: drawing.type,
      data: drawing.data,
    })),
  };

  try {
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/submit-annotations.php", payload);
    console.log("Submission successful:", response.data);
    // Optionally, provide user feedback here
  } catch (error) {
    console.error("Error submitting drawings:", error);
    // Optionally, provide user feedback here
  }
};

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
        app_id: 3,
        user_id: localStorage.getItem('user_id')
      });
      imageUrl.value = response.data.file_location;
      localStorage.setItem('image_id',response.data.id);
      console.log("Image URL: " + imageUrl.value);
    } catch (error) {
      console.log(error);
    }
  } catch (error) {
    console.log(error);
  }

  setExamples(null);

  watch(imageUrl, (newImageUrl) => {
    if (newImageUrl && canvasMapRef.value?.$el) {
      const canvasElement = canvasMapRef.value.$el;
      const infoPanel = document.getElementById('citsci-info-panel');
      if (infoPanel && canvasElement) {
        infoPanel.style.height = `${canvasElement.offsetHeight}px`;
        infoPanel.style.top = `${canvasElement.offsetTop}px`;
      }
    }
  });
});
</script>


<style>
.button-not-selected {
  background-color: #eaecee;
  height: 60px;
  width: 60px;
}

.button-selected {
  background-color: #2a2e35;
  height: 60px;
  width: 60px;
}

.submit-button {
  padding: 8px 15px;
  border: none; /* Remove default border */
  background-color: #29336c;
  color: white;
  font-weight: bold;
  cursor: pointer;
  border-radius: 5px; /* Optional: for rounded corners */
}

.submit-button:hover {
  /* Optional: Add hover effect */
  opacity: 0.9;
}

#citsci-examples {
  margin-top: 20px;
}

</style>
