<template>
  <template v-if="isNoFingers">
    <PageLayout title=": Lunar Flows BETA" >
      <div v-if="!isAuthenticated && !isLoading" class="loginDiv">
        <img src="https://learn-wp.s3.us-east-2.amazonaws.com/learn/wp-content/uploads/2025/06/06200746/Moon-150x150.png" alt="Moon Logo"/>
        <h2>Please Log In</h2>
        <p>We want to give you credit for everything you contribute to. We can only
          do that if you log in first.</p>
        <p>If you'd like to learn more about Lunar Melt before you register, please
          <a href="/learn/">check out our learning site</a>.</p>
        <button @click="handleLogin">Log In</button>
      </div>
      <div class="content-layout" v-else-if="isAuthenticated">
        <div id="citsci-main-panel">
          <div id="moon-flow">
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
          </div>
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
                <a href="" target="_blank">Tutorial</a>
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
            <div style="float: right; padding-right: 25px;">
              Show Marks:
              <input
                  type="radio"
                  :value="true"
                  v-model="showMarks"
                  @change="setExamples()"
              > on
              <input
                  type="radio"
                  :value="false"
                  v-model="showMarks"
                  @change="setExamples()"
              > off
            </div>
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
import {computed, onMounted, ref} from 'vue';
import apiClient from '@/api/axios';
import {useRouter} from "vue-router";

const isNoFingers = useIsNoFingers();

const { user, isAuthenticated, loginWithRedirect, isLoading } = useAuth0();
const router = useRouter(); // Initialize useRouter
const currentContextUrl = ref('');

// Image and example state
const imageUrl = ref(null);
const exampleMarks = ref(null);
const showMarks = ref(true);
const currentTool = ref('default');

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

// Tutorial Logic
const currStep = ref(0); // Start at 0, meaning the tutorial is not active yet
const showPatienceMessage = ref(false); // New state variable
const validationMessage = ref(null); // NEW: Reactive variable for validation message
const showValidationMessage = ref(false); // NEW: State for showing validation message

const handleLogin = () => {
  loginWithRedirect();
};

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
    title: "Welcome to Lunar Melt Flow Activity",
    content: "Ready to get mapping? " +
        "Your work will accelerate research into how asteroid impacts changed the Lunar surface.  Our research " +
        "might even help researchers find the geologic features that unlock the history of both the Earth " +
        "and the Moon.<br><br>" +

        "This tutorial will guide you through marking features formed by melted rock that flowed, solidified, and " +
        "sometimes cracked.<br><br>" +
        "Today, computers can't do this work, and your efforts  " +
        "help us focus more of our limited time on data analysis. Thank you! We'll share all our research with you on this site " +
        "or signup on the profile page to get news in your inbox. <br><br>" +
        "Let's get started!",
    className: "step-1",
    image1: "",
    image2: "",
    imageCaption: ""
  },
  {
    id: 2,
    title: "Go with the flow",
    content: "In this project you are mapping where melted rock flowed across the Moon, and the ridges and" +
        "cracks that formed as the melt cooled and solidified. To understand these features you may need to " +
        "see them in context, so we have also given you a context image. You can click the context image to " +
        "open it larger in a new window.<br><br>" +
        "When you click on a tool, you'll see examples specific to the feature it wants you to map.<br><br>",
    className: "step-2",
    image1: "",
    image2: "https://wm-web-assets.s3.us-east-2.amazonaws.com/arrow-left.png",
    imageCaption: "Try it! Click the buttons on the left to see the examples below change. " +
        "You can also turn the marks on the examples on and off to see the features."
  },
  {
    id: 3,
    title: "Marking the flow's edge",
    content: "During an impact, rock can melt and flow like lava across the lunar surface. We want to map " +
        "the boundary between the melt flow and the surrounding terrain whenever we see it. Just click where the flow" +
        "near one edge of the image and follow it along to the other edge of the image. When you're done, you'll" +
        "need to hit [esc] or double click to end the line.<em>Not every image has " +
        "a visible melt flow.</em>",
    className: "step-3",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-rocks.png",
    image2: "",
    imageCaption: "Try it! Click the 'Flow Margin' button and then trace the flow's edge. "
  },
  {
    id: 4,
    title: "Get cracking",
    content: "Most materials expand when hot and contract when cold, and lunar melt is one of those materials!" +
        " This change in volume can cause " +
        "cracks to open in the landscape. Can you mark the three cracks in this image?  <br><br>" ,

    className: "step-4",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-boulder.png",
    image2: "",
    imageCaption: "Try it! Click the 'Cracks' button, and then click along a crack."
  },
  {
    id: 5,
    title: "Trace Ridges",
    content: "Between flowing smoothly and solidifying completely, the melt gets gooey and can form " +
        "ridges where the flow smushes up on itself like partially melted chocolate. <em>These are rare!</em><br><br> " +
        "Can you mark this image's 2 ridges? <br><br>",
    className: "step-5",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-crater.png",
    image2: "https://moon-mappers.s3.us-east-2.amazonaws.com/Tutorial/LunarMelt-Act1-minsize.png",
    imageCaption: "Try it! Click the 'Craters' button, and then click in the center of the crater and drag out to its edges."
  },
  {
    id: 6,
    title: "Check your work, then get mapping!",
    content: "We're showing you how we marked the image. How do your marks compare? You can repear this tutorial " +
        "as many times as you want until you are confident in your work! When you're ready, " +
        "there are discoveries waiting to be made as we work together!<br><br>",
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
        app_id: 4,
        task: "add"
      });
      console.log('Successfully marked tutorial as complete for user.', response.data);
      router.push('/do_science/lunar-melt-flows');
    } catch (error) {
      console.error('Failed to send tutorial completion status:', error);
    }
  }
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
function setExamples(tool = currentTool.value) {
  currentTool.value = tool;
  const prefix = "https://moon-mappers.s3.us-east-2.amazonaws.com/examples/";
  const suffix = showMarks.value ? "-marked.png" : ".png";

  exampleImages.value = [];
  if (tool === 'margin') {
    for (let i = 1; i <= 3; i++) {
      exampleImages.value.push(prefix + `example-margin-${i}${suffix}`);
    }
  } else if (tool === 'cracks') {
    for (let i = 1; i <= 3; i++) {
      exampleImages.value.push(prefix + `example-cracks-${i}${suffix}`);
    }
  } else if (tool === 'ridge') {
    for (let i = 1; i <= 3; i++) {
      exampleImages.value.push(prefix + `example-ridge-${i}${suffix}`);
    }
  }else {
    // For now, just show a default set
    exampleImages.value = [
      `${prefix}example-margin-1${suffix}`,
      `${prefix}example-cracks-2${suffix}`,
      `${prefix}example-ridge-1${suffix}`,
    ];
  }
}

const toggleMarks = (val) => {
  showMarks.value = val;
  setExamples(); // Refresh images with the current tool and new suffix
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

  // Now get tutorial images
  imageUrl.value = "https://moon-mappers.s3.us-east-2.amazonaws.com/Tutorial/LunarMelt-Act2-TutorialImage.png";
  currentContextUrl.value = "https://moon-mappers.s3.us-east-2.amazonaws.com/Tutorial/LunarMelt-Act2-TutorialImage-Context.png";

  // Set examples
  setExamples();
  startTutorial();
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