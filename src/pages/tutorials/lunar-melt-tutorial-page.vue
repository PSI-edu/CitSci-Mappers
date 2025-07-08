<template>
  <template v-if="isNoFingers">
    <div class="darken" v-if="currStep==1"></div>
    <PageLayout title=": Lunar Melt">
      <div class="content-layout">
        <div id="citsci-main-panel">
          <div id="moon">

            <div id="tutorial" :class="currentStepClass" v-if="currStep > 0">

              <div class="tutorial-navigation">
                <button
                    v-for="step in tutorialSteps.slice(1)" :key="step.id"
                    @click="goToStep(step.id)"
                    :class="{ active: currStep === step.id }"
                >
                  {{ step.id }}
                </button>
              </div>

              <h3>{{ currentStepTitle }}</h3>

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
                <button @click="endTutorial" v-if="currStep === tutorialSteps.length - 1" class="end-button">Got It!
                </button>
                <button @click="nextStep" v-if="currStep < tutorialSteps.length - 1" class="nav-button next-button">Next
                </button>
              </div>
            </div>


            <div id="citsci-buttons-panel">
              <h4>Tools</h4>
              <button
                  @click="setMode('circle'); setText(craterTitle, craterInfo); setExamples('circle')"
                  :class="{'button-not-selected': mode !== 'circle', 'button-selected': mode === 'circle'}"
                  style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-crater.png'); background-size: contain;"
                  title="Crater Tool"
              ></button>
              <button
                  @click="setMode('line'); setText(boulderTitle, boulderInfo); setExamples('line')"
                  :class="{'button-not-selected': mode !== 'line', 'button-selected': mode === 'line'}"
                  style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-boulder.png'); background-size: contain;"
                  title="Boulder Tool"
              ></button>
              <button
                  @click="setMode('dot'); setText(rocksTitle, rocksInfo); setExamples('dot')"
                  :class="{'button-not-selected': mode !== 'dot', 'button-selected': mode === 'dot'}"
                  style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-rocks.png'); background-size: contain;"
                  title="Rock Tool"
              ></button>
              <button
                  @click="setMode('erase'); setText(eraseTitle, eraseInfo); setExamples('erase')"
                  :class="{'button-not-selected': mode !== 'erase', 'button-selected': mode === 'erase'}"
                  style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-erase.png');background-size: contain;"
                  title="Erase Tool"
              ></button>
              <button
                  @click="setMode('edit'); setText(eraseTitle, eraseInfo); setExamples('erase')"
                  :class="{'button-not-selected': mode !== 'edit', 'button-selected': mode === 'edit'}"
                  style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-edit.png');background-size: contain;"
                  title="Move / Resize Tool"
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
                  :currStep="currStep"
                  @canvas-click-during-tutorial="handleCanvasClickDuringTutorial"
                  :correctRockLocations="correctRockLocations"
                  :correctBoulderLocations="correctBoulderLocations"
                  :correctCraterLocations="correctCraterLocations"
                  @validation-message="displayValidationMessage"
              />

              <div v-if="showPatienceMessage" class="not-yet-message">
                Please follow the instructions.
              </div>
              <div v-if="showValidationMessage" class="feedback">
                {{ validationMessage }}
              </div>
            </div>
            <div class="citsci-info-panel melt">
              <h5>Activity 1:</h5>
              <h3>Craters, Boulders, Rocks</h3>
              <div class="label">
                <p>Task:</p>
              </div>
              <div class="content">
                <p>We're mapping geologic features from/in flowing impact melt. Long ago, the heat
                  of asteroid impacts melted the regions you're mapping. Your
                  work helps us understand how the melt flowed & when it cooled. </p>
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
                  <a href="/tutorials/lunar-melt-tutorial" target="_blank">Tutorial</a>
                </p>
              </div>
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
                @click="endTutorial()"
                class="submit-button"
                id="submit-button"
                :disabled="currStep !== 6"
            >Submit
            </button>
            <button
                class="busy-button" id="busy-button"
            >Working....
            </button>
            <div class="LunarMelt citsci-examples">
              <h4> Examples</h4>
              <img
                  v-for="example in exampleImages"
                  :key="example"
                  :src="example"
                  style="margin-right: 5px;"
                  alt="Example Image"
              />
            </div>
          </div>
        </div>
      </div>
    </PageLayout>
  </template>
  <template v-else>
    <PageLayout title=": Lunar Melt BETA">
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
import {useIsNoFingers} from "@/composables/noFingers.js";
import PageLayout from "@/components/page-layout.vue";
import CanvasMap from "@/components/citsci-tools/tutorial-canvas-map.vue";
import {useAuth0} from "@auth0/auth0-vue";
import {computed, onMounted, ref, watch} from 'vue';
import apiClient from '@/api/axios';
import {useRouter} from 'vue-router';

const isNoFingers = useIsNoFingers();

const {user} = useAuth0();

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


const exampleMarks = ref(null);

// Tutorial Logic
const currStep = ref(0); // Start at 0, meaning the tutorial is not active yet
const showPatienceMessage = ref(false); // New state variable
const validationMessage = ref(null); // NEW: Reactive variable for validation message
const showValidationMessage = ref(false); // NEW: State for showing validation message
const router = useRouter();

const displayPatienceMessage = () => {
  showPatienceMessage.value = true;
  setTimeout(() => {
    showPatienceMessage.value = false;
  }, 3000); // Message disappears after 3 seconds
};

// NEW: Function to display validation messages
const displayValidationMessage = (message) => {
  validationMessage.value = message;
  showValidationMessage.value = true;
  setTimeout(() => {
    showValidationMessage.value = false;
    validationMessage.value = null; // Clear message after hiding
  }, 3000); // Message disappears after 6 seconds
};


// NEW: Define correct rock locations
const correctRockLocations = ref([
  { x: 201, y: 329 },
  { x: 209, y: 312 },
  { x: 209, y: 298 },
  { x: 223, y: 284 },
  { x: 269, y: 281 }
]);

const correctBoulderLocations = ref([
  { x1: 43, y1: 217, x2: 54, y2: 273 },
  { x1: 100, y1: 277, x2: 160, y2: 302 }
]);

const correctCraterLocations = ref([
  { x: 171, y: 94, radius: 24 }
]);

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
    title: "Welcome to Lunar Melt!",
    content: "Ready to get mapping? " +
        "Your work will accelerate research into how asteroid impacts changed the Moon's surface.  Our research" +
        "might even help humans find water and stay safe when humans return to the lunar surface.<br><br>" +

        "This tutorial will guide you through the process of marking craters, boulders, and rocks in the melt. Our " +
        "team of researchers will use your work analyze Little Lowell crater and potentially to train " +
        "machine learning algorithms to automate this process in the future.<br><br>" +
        "Today, computers can't do this work, and your efforts  " +
        "helo us focus more of our limited time on data analysis. Thank you! We'll share all our research with you on this site." +
        "You can also sign up for our news in your inbox on your profile page. <br></br>" +
        "Let's get started!",
    className: "step-1",
    image1: "",
    image2: "",
    imageCaption: ""
  },
  {
    id: 2,
    title: "You're mapping this image's rocks, boulders, & craters",
    content: "You'll use the Tools on the left to mark rocks with a dot, measure the longest part of a boulder with a line, " +
        "and outline craters larger than the minimum size. You can also erase bad marks, and move things if you make " +
        "a mistake. <br><br>" +
        "When you select a mapping tool, we'll show you more examples " +
        "specific to that tool.<br><br>",
    className: "step-2",
    image1: "",
    image2: "https://wm-web-assets.s3.us-east-2.amazonaws.com/arrow-left.png",
    imageCaption: "Try it! Click the buttons on the left to see the examples change."
  },
  {
    id: 3,
    title: "Got Rocks?",
    content: "Can you mark on at least 5 rocks in this image? Let the examples " +
        "below guide you. We'll give you initial feedback each time you mark a rock.",
    className: "step-3",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-rocks.png",
    image2: "",
    imageCaption: "Try it! Click the 'Rocks' button and then click on the image where you see rocks. "
  },
  {
    id: 4,
    title: "Measure Boulders",
    content: "These large chunks of rock are worth measuring! Use the drawing tool to put a line along the longest " +
        "axis of the 2 big boulders in this image. <br><br>" +
    "Remember to click the boulder button first to select your tool! <br><br>" ,
    className: "step-4",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-boulder.png",
    image2: "",
    imageCaption: "Try it! Click the 'Boulders' button, and then click & drag along the longest axis of each boulder."
  },
  {
    id: 5,
    title: "Trace Craters",
    content: "There are lots of craters on the Moon! Lucky for you (and us), we are only interested in craters " +
        "more than 24 pixels across. <br><br> Can you mark this image's largest crater? <br><br>",
    className: "step-5",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-crater.png",
    image2: "https://moon-mappers.s3.us-east-2.amazonaws.com/Tutorial/LunarMelt-Act1-minsize.png",
    imageCaption: "Try it! Click the 'Craters' button, and then click in the center of the crater and drag out to its edges."
  },
  {
    id: 6,
    title: "Let's do some science!",
    content: "Together we can map more of the Moon than any one of us could do alone. Let's get clicking " +
        "(and dragging). There are discoveries to make working together!<br><br>",
    className: "step-6",
    image1: "",
    image2: "",
    imageCaption: "Ready? Click the 'Submit' button below & let's do some science!"
  }
];

const currentStep = computed(() => tutorialSteps[currStep.value]);
const currentStepTitle = computed(() => currentStep.value.title);
const currentStepContent = computed(() => currentStep.value.content);
const currentStepClass = computed(() => currentStep.value.className);
const currentStepImage1 = computed(() => currentStep.value.image1);
const currentStepImage2 = computed(() => currentStep.value.image2);
const currentStepImageCaption = computed(() => currentStep.value.imageCaption);

// New method to handle canvas clicks during tutorial
const handleCanvasClickDuringTutorial = () => {
  if (currStep.value > 0 && currStep.value !== 3 && currStep.value !== 4 && currStep.value !== 5) { // Updated to allow specific tools in 3, 4, 5
    displayPatienceMessage();
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

const startTutorial = () => {
  currStep.value = 1; // Start from the first actual tutorial step
};

const endTutorial = async () => {
  const userId = localStorage.getItem('user_id');
  if (userId) {
    try {
      // Send user_id to the tutorial completion endpoint
      const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + '/user-tutorial.php', {
        user_id: localStorage.getItem('user_id'),
        app_id: 3,
        task: "add"
      });
      console.log('Successfully marked tutorial as complete for user.', response.data);
      router.push('/do_science/mars-mosaic');
    } catch (error) {
      console.error('Failed to send tutorial completion status:', error);
    }
  }
};

const setMode = (newMode) => {
  // If currStep is 3, only allow 'dot' mode. For other modes, show patience message.
  if (currStep.value === 3) {
    if (newMode !== 'dot'  && newMode !== 'erase' && newMode !== 'edit') {
      displayPatienceMessage();
      return; // Prevent changing the mode to anything other than 'dot'
    }
  }
  // If currStep is 4, only allow 'line' mode. For other modes, show patience message.
  if (currStep.value === 4  && newMode !== 'erase' && newMode !== 'edit') {
    if (newMode !== 'line') {
      displayPatienceMessage();
      return; // Prevent changing the mode to anything other than 'line'
    }
  }
  // If currStep is 5, only allow 'circle' mode. For other modes, show patience message.
  if (currStep.value === 5  && newMode !== 'erase' && newMode !== 'edit') {
    if (newMode !== 'circle') {
      displayPatienceMessage();
      return; // Prevent changing the mode to anything other than 'circle'
    }
  }
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
  const {index, newDrawing} = payload; // Destructure the event payload
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

  // Now get tutorial image
  imageUrl.value = "https://moon-mappers.s3.us-east-2.amazonaws.com/Tutorial/LunarMelt-Act1-TutorialImage.png";

  // Draw the example marks
  drawExampleCanvas();

  // Add the example images at the bottom
  setExamples(null);

  startTutorial();
});

const drawExampleCanvas = () => {
  // Draw the example marks on the canvas: a 6px circle and a 6 pixel line
  const canvasExample = exampleMarks.value;
  canvasExample.width = 100;
  canvasExample.height = 75;
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

watch(currStep, (newStep, oldStep) => {
  if (newStep !== oldStep) {
    mode.value = null; // Deselect any active tool button
    if (canvasMapRef.value) {
      canvasMapRef.value.setDrawingMode(null); // Clear the drawing mode on the canvas as well
    }
  }
});

</script>