<template>
  <template v-if="isNoFingers">
    <div class="darken" v-if="currStep==1"></div>
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
                @click="setMode('zigzag-dotted');"
                :class="{'button-not-selected': mode !== 'zigzag-dotted', 'button-selected': mode === 'zigzag-dotted'}"
                style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-margin.png'); background-size: contain;"
            ></button>
            <button
                @click="setMode('zigzag-solid');"
                :class="{'button-not-selected': mode !== 'zigzag-solid', 'button-selected': mode === 'zigzag-solid'}"
                style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-crack.png'); background-size: contain;"
            ></button>
            <button
                @click="setMode('zigzag-dash'); "
                :class="{'button-not-selected': mode !== 'zigzag-dash', 'button-selected': mode === 'zigzag-dash'}"
                style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-ridge.png'); background-size: contain;"
            ></button>
            <button
                @click="setMode('erase');"
                :class="{'button-not-selected': mode !== 'erase', 'button-selected': mode === 'erase'}"
                style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-erase.png');background-size: contain;"
            ></button>
            <button
                @click="setMode('edit');"
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
                :currStep="currStep"
                :tutorialNoMarkSteps="nonMarkingStepsArray"
                @canvas-click-during-tutorial="handleCanvasClickDuringTutorial"
            />

            <div v-if="showPatienceMessage" class="not-yet-message">
              Please follow the instructions.
            </div>

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
                    font-weight: bold;
                    text-shadow: 2px 2px #000000;
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    z-index: 30;
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
          <button
              @click="endTutorial()"
              class="submit-button"
              id="submit-button"
              :disabled="currStep !== 7"
          >
            Submit
          </button>
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
import CanvasMap from "@/components/citsci-tools/tutorial-canvas-map.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import {computed, onMounted, ref, watch} from 'vue';
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
const nonMarkingStepsArray = ref([1,2,3,7]);

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
        "You're accelerating research into how impacts change the Lunar surface, and you may " +
        "even help researchers find the geologic features that unlock the history of the Earth " +
        "and the Moon.<br><br>" +

        "This tutorial will guide you through marking features formed by melted rock that flowed, solidified, and " +
        "sometimes cracked. Specifically, you'll map flows, rare ridges, and cracks.<br><br>" +
        "Today, AI can't do this work. Your efforts  " +
        "help us focus more of our limited time on data analysis. Thank you! We'll share our results with you here " +
        "or signup on the profile page to get our newsletter. <br><br>" +
        "Let's get started!",
    className: "step-1",
    image1: "https://moon-mappers.s3.us-east-2.amazonaws.com/Tutorial/LunarMelt-Act2-Step1-Example.png",
    image2: "",
    imageCaption: ""
  },
  {
    id: 2,
    title: "Go with the flow",
    content: "In this project, you're mapping where melted rock flowed across the Moon, and the ridges and " +
        "cracks that formed as the melt cooled and solidified. We have examples to help you every step " +
        "of the way!<br><br>" +
        "When you click on a tool, you'll see specific examples, and can turn the marks on & off.<br><br>",
    className: "step-2",
    image1: "",
    image2: "https://wm-web-assets.s3.us-east-2.amazonaws.com/arrow-left.png",
    imageCaption: "Try it! Click the buttons on the left to see the examples change, and try " +
        "toggling the marks on and off."
  },
  {
    id: 3,
    title: "Seeing things in Context",
    content: "To help you understand the features we've provided a context image. <br><br>",
    className: "step-3",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/arrow-right.png",
    image2: "",
    imageCaption: "Click the context image to see it open in a new window. "
  },
  {
    id: 4,
    title: "Marking the flow's edge",
    content: "During an impact, rock can melt and flow like lava across the lunar surface. We want to map " +
        "the boundary between the melt flow and the surrounding terrain whenever we see it. Just click where the flow " +
        "near one edge of the image and follow it along to the other edge of the image. When you're done,  " +
        "hit [esc] or double click to end the line.",
    className: "step-4",
    image1: "",
    image2: "https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-margin.png",
    imageCaption: "Try it! Click the 'Flow Margin' button, click along the flow edge, then click [esc] or double click. "
  },
  {
    id: 5,
    title: "Get cracking",
    content: "Most materials expand when hot and contract when cold, and lunar melt is one of those materials!" +
        " This change in volume can cause " +
        "cracks to open in the landscape. Can you mark the large vertical crack on the left?  <br><br>" ,

    className: "step-5",
    image1: "https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-crack.png",
    image2: "",
    imageCaption: "Click the 'Cracks' button, click along the big vertical crack, then " +
        "click [esc] or double click to end."
  },
  {
    id: 6,
    title: "Trace Ridges",
    content: "Between flowing smoothly and solidifying completely, the melt gets gooey and can form " +
        "ridges where the flow smushes up on itself like partially melted chocolate. <em>These are rare!</em><br><br> " +
        "Can you mark the top of some of the diagonal ridges? <br><br>",
    className: "step-6",
    image1: "",
    image2: "https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-ridge.png",
    imageCaption: "Click the 'Ridges' button, and mark them just like you marked the flow boundary & cracks."
  },
  {
    id: 7,
    title: "Check your work, then get mapping!",
    //content: "We're showing you how we marked the image. How do your marks compare? You can repear this tutorial " +
    content: "You can repeat this tutorial " +
        "as many times as you want until you are confident in your work! When you're ready, " +
        "there are discoveries waiting to be made as we work together!<br><br>",
    className: "step-7",
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
  if (currStep.value > 0 && currStep.value !== 3 && currStep.value !== 4 && currStep.value !== 5 && currStep.value !== 6) { // Updated to allow specific tools
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

// Set the button background
const setMode = (newMode) => {
  // Set which steps allow which modes
  if (currStep.value === 1 || currStep.value === 3 || currStep.value === 7 ) {
    displayPatienceMessage();
    return;
  }
  else if (currStep.value === 4) {
    if (newMode !== 'zigzag-dotted' && newMode !== 'erase' && newMode !== 'edit') {
      console.log("in here");
      displayPatienceMessage();
      return;
    }
  }
  else if (currStep.value === 5) {
    if (newMode !== 'zigzag-solid' && newMode !== 'erase' && newMode !== 'edit') {
      displayPatienceMessage();
      return;
    }
  }
  else if (currStep.value === 6) {
    if (newMode !== 'zigzag-dash' && newMode !== 'erase' && newMode !== 'edit') {
      displayPatienceMessage();
      return;
    }
  }

  mode.value = newMode;
  if (canvasMapRef.value) {
    canvasMapRef.value.setDrawingMode(newMode);
    setExamples(newMode)
    switch (newMode) {
      case 'zigzag-dotted':
        setText(marginTitle, marginInfo);
        return
      case 'zigzag-solid':
        setText(cracksTitle, cracksInfo);
        return
      case 'zigzag-dashed':
        setText(ridgeTitle, ridgeInfo);
        return
      case 'erase':
        setText(eraseTitle, eraseInfo);
        return
      case 'edit':
        setText(eraseTitle, eraseInfo);
        return
      default:
        console.log("how? mode set to unknown value");
        return;
    }
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
  if (tool === 'zigzag-dotted') {
    for (let i = 1; i <= 3; i++) {
      exampleImages.value.push(prefix + `example-margin-${i}${suffix}`);
    }
  } else if (tool === 'zigzag-solid') {
    for (let i = 1; i <= 3; i++) {
      exampleImages.value.push(prefix + `example-cracks-${i}${suffix}`);
    }
  } else if (tool === 'zigzag-dash') {
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

  // Draw Context Image
  drawContextImage(currentContextUrl.value);

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

watch(currStep, (newStep, oldStep) => {
  if (newStep !== oldStep) {
    mode.value = null; // Deselect any active tool button
    if (canvasMapRef.value) {
      canvasMapRef.value.setDrawingMode(null); // Clear the drawing mode on the canvas as well
    }
  }

  // Change the image to the premarked one as needed
  if (newStep === 7) {
    imageUrl.value = "https://moon-mappers.s3.us-east-2.amazonaws.com/Tutorial/LunarMelt-Act2-TutorialImage-marked.png";
  } else {
    // Revert to the original clean image if they go back to earlier steps
    imageUrl.value = "https://moon-mappers.s3.us-east-2.amazonaws.com/Tutorial/LunarMelt-Act2-TutorialImage.png";
  }

});

</script>