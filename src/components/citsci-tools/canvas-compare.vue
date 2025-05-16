<template>

      <canvas ref="canvas__compare" id="canvas__compare" width="450" height="450" @click="drawRectangle"></canvas>
      <div id="compare-buttons-panel">
        <div class="compare-column">
          <p><small>Shown above</small></p>
          <p id="shown"><strong>Control Image</strong></p>
        </div>
        <div class="compare-column">
            <input type="radio" id="control" name="compare" value="control" @click="loadControl()" checked>
            <label for="control">Control </label><br/>
            <input type="radio" id="diff" name="diff" value="difference" @click="loadDiff()" >
            <label for="diff">Difference</label><br/>
            <input type="radio" id="blink" name="blink" value="blink" @click="loadBlink()" >
            <label for="blink">Blink Control & Test for 5sec</label><br/>
        </div>
      </div>

</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  controlName: String,
  imageName: String,
  diffName: String,
});

// onload show imageName and then respond to radio button clicks
const canvas__compare = ref(null);
onMounted(() => {
  loadImage();
});
watch(() => props.imageName, () => {
  loadImage();
});
const loadImage = () => {
  if (!props.imageName || !canvas__compare.value) return;

  const canvas = canvas__compare.value;
  const ctx = canvas.getContext('2d');
  const image = new Image();

  image.onload = () => {
    canvas.width = image.width;
    canvas.height = image.height;
    ctx.drawImage(image, 0, 0);
  };

  image.onerror = () => {
    console.error(`Failed to load image: ${props.controlName}`);
  };

  image.src = props.controlName; // Assuming imageName is the correct path/URL
};

// if someone clicks the difference button show the difference image and unselect the other radio buttons
const loadDiff = () => {
  const canvas = canvas__compare.value;
  const ctx = canvas.getContext('2d');
  const image = new Image();

  image.onload = () => {
    canvas.width = image.width;
    canvas.height = image.height;
    ctx.drawImage(image, 0, 0);
    document.getElementById("shown").innerHTML = "<strong>Difference Image</strong>";
  };

  image.onerror = () => {
    console.error(`Failed to load image: ${props.diffName}`);
  };

  image.src = props.diffName; // Assuming imageName is the correct path/URL

  // unselect the other radio buttons
  document.getElementById("control").checked = false;
  document.getElementById("blink").checked = false;
};

// if someone clicks the control button show the control image and unselect the other radio buttons
const loadControl = () => {
  const canvas = canvas__compare.value;
  const ctx = canvas.getContext('2d');
  const image = new Image();

  image.onload = () => {
    canvas.width = image.width;
    canvas.height = image.height;
    ctx.drawImage(image, 0, 0);
    document.getElementById("shown").innerHTML = "<strong>Control Image</strong>";
  };

  image.onerror = () => {
    console.error(`Failed to load image: ${props.imageName}`);
  };

  image.src = props.imageName; // Assuming imageName is the correct path/URL

  // unselect the other radio buttons
  document.getElementById("diff").checked = false;
  document.getElementById("blink").checked = false;
};

// if someone clicks the blink button, blink between imageName and controlName every 500ms for 5 seconds then stop and show the control image and check the control radio button
const loadBlink = () => {
  const canvas = canvas__compare.value;
  const ctx = canvas.getContext('2d');
  const image1 = new Image();
  const image2 = new Image();

  // uncheck the other radio buttons
  document.getElementById("control").checked = false;
  document.getElementById("diff").checked = false;

  let blinkCount = 0;
  let blinkInterval = setInterval(() => {
    if (blinkCount < 10) {
      document.getElementById("control").checked = false;
      document.getElementById("diff").checked = false;
      if (blinkCount % 2 === 0) {
        ctx.drawImage(image1, 0, 0);
        document.getElementById("shown").innerHTML = "<strong>Control Image</strong>";
      } else {
        ctx.drawImage(image2, 0, 0);
        document.getElementById("shown").innerHTML = "<strong>Test Image</strong>";
      }
      blinkCount++;
    } else {
      document.getElementById("control").checked = true;
      clearInterval(blinkInterval);
      loadControl();
    }
  }, 500);

  image1.src = props.imageName; // Assuming imageName is the correct path/URL
  image2.src = props.controlName; // Assuming imageName is the correct path/URL
};
</script>
