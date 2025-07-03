<template>
  <template v-if="isNoFingers">
    <PageLayout title=": Lunar Melt" >
      <div class="content-layout">
        <div id="citsci-main-panel">
          <div id="citsci-buttons-panel">
            <button
              @click="setMode('red-line')"
              :class="{'button-selected': mode === 'red-line', 'button-not-selected': mode !== 'red-line'}"
              style="background-color: white; color: red; border: 2px solid red; width: 48px; height: 48px; border-radius: 8px; margin: 6px; font-size: 2em; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.15);"
              title="Draw Red Line"
            >
              <svg width="32" height="32" viewBox="0 0 32 32">
                <line x1="4" y1="28" x2="28" y2="4" stroke="red" stroke-width="4" stroke-linecap="round"/>
              </svg>
            </button>
            <button
              @click="setMode('erase')"
              :class="{'button-selected': mode === 'erase', 'button-not-selected': mode !== 'erase'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-erase.png'); background-size: contain; width: 48px; height: 48px; border: none; border-radius: 8px; margin: 6px;"
              title="Erase/Delete"
            ></button>
            <button
              @click="setMode('edit')"
              :class="{'button-selected': mode === 'edit', 'button-not-selected': mode !== 'edit'}"
              style="background-image: url('https://wm-web-assets.s3.us-east-2.amazonaws.com/buttons/button-edit.png'); background-size: contain; width: 48px; height: 48px; border: none; border-radius: 8px; margin: 6px;"
              title="Move/Edit"
            ></button>
          </div>
          <div id="citsci-mapping-panel">
            <CanvasMap
              ref="canvasMapRef"
              :mode="mode"
              :drawings="drawings"
              @draw="handleDraw"
              @clearDrawing="clearDrawing"
              @updateDrawing="handleUpdateDrawing"
            />
          </div>
          <div class="citsci-info-panel melt">
            <h5>Activity 1:</h5>
            <h3>Craters, Boulders, Rocks</h3>
            <p>We are mapping geologic features related to flowing impact melt in the Moon's Little Lowell & Tycho craters. Long ago, the heat of asteroid impacts melted the regions you're mapping. Your work helps us understand how the melt flowed & when it cooled. </p>
            <br/>
            <h4>{{ infoTitle }}</h4>
            <p>{{ infoText }}</p>
            <div id="ex-canvas">
              <canvas ref="exampleMarks" id="exampleMarks" width="100" height="75"></canvas>
            </div>
          </div>
          <button @click="saveResponse()" class="submit-button" id="submit-button">Submit</button>
          <button class="busy-button" id="busy-button">Working....</button>
          <div class="LunarMelt citsci-examples">
            <h4>Examples</h4>
            <img v-for="example in exampleImages" :key="example" :src="example" style="margin-right: 5px;" alt="Example Image" />
          </div>
        </div>
      </div>
    </PageLayout>
  </template>
  <template v-else>
    <PageLayout title=": Lunar Melt BETA" >
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

const isNoFingers = useIsNoFingers();

// Info panel state (copied from lunar-melt-page.vue for erase/edit)
const eraseTitle = ref("Erasing");
const eraseInfo = ref("Click on a mark to delete it.");
const infoTitle = ref("Ready?");
const infoText = ref("Welcome to the lunar surface. Select a tool to begin marking features.");

// Drawing State
const mode = ref('');
const drawings = ref([]);
const canvasMapRef = ref(null);

function setMode(newMode) {
  mode.value = newMode;
  if (newMode === 'erase') {
    infoTitle.value = eraseTitle.value;
    infoText.value = eraseInfo.value;
  } else if (newMode === 'edit') {
    infoTitle.value = 'Editing';
    infoText.value = 'Drag or resize marks to adjust.';
  } else if (newMode === 'red-line') {
    infoTitle.value = 'Red Lines';
    infoText.value = 'Click and drag to draw red line segments.';
  } else {
    infoTitle.value = 'Ready?';
    infoText.value = 'Welcome to the lunar surface. Select a tool to begin marking features.';
  }
}

function handleDraw(drawing) {
  // If mode is 'red-line', add color info
  if (mode.value === 'red-line') {
    drawing.color = 'red';
  }
  drawings.value.push(drawing);
}

function clearDrawing(index) {
  drawings.value.splice(index, 1);
}

function handleUpdateDrawing({ index, drawing }) {
  drawings.value[index] = drawing;
}
</script>