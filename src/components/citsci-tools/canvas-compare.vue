<template>
  <canvas ref="canvas__compare" id="canvas__compare" width="450" height="450" @click="drawRectangle"></canvas>
  <div id="compare-buttons-panel">
    <strong>Show me:</strong>
      <input type="radio" id="control" name="compare" value="control" @click="loadControl()" >
      <label for="control">Overlaid </label>
      <input type="radio" id="diff" name="diff" value="difference" @click="loadDiff()" checked>
      <label for="diff">Difference</label>
      <input type="radio" id="blink" name="blink" value="blink" @click="loadBlink()" >
      <label for="blink">Blink images for 5sec</label>
  </div>



</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  controlName: String,
  imageName: String,
  diffName: String,
});

// To stop CORS issues with image loading from cache
const getCacheBustedUrl = (url) => {
  return `${url}?t=${new Date().getTime()}`;
};

// onload show imageName and then respond to radio button clicks
const canvas__compare = ref(null);
onMounted(() => {
  // Initially load the "control" state (controlName with imageName overlay)
  loadDiff();
});
watch(() => props.imageName, () => {
  loadDiff();
});

// Helper function to apply tint
const applyTint = (image, tintColor) => {
  // Create a temporary canvas to draw the image and then get its pixel data
  const tempCanvas = document.createElement('canvas');
  const tempCtx = tempCanvas.getContext('2d');
  tempCanvas.width = image.width;
  tempCanvas.height = image.height;
  tempCtx.drawImage(image, 0, 0);

  const imageData = tempCtx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
  const data = imageData.data;

  const rTint = tintColor[0];
  const gTint = tintColor[1];
  const bTint = tintColor[2];

  for (let i = 0; i < data.length; i += 4) {
    const r = data[i];
    const g = data[i + 1];
    const b = data[i + 2];

    // Apply tint: Simple multiplication-based tint.
    data[i] = Math.min(255, r * (rTint / 255));     // Red
    data[i + 1] = Math.min(255, g * (gTint / 255)); // Green
    data[i + 2] = Math.min(255, b * (bTint / 255)); // Blue
    // Alpha channel (data[i + 3]) remains unchanged
  }

  // Put the tinted image data back onto the temporary canvas
  tempCtx.putImageData(imageData, 0, 0);

  // Return the temporary canvas to be drawn on the main canvas
  return tempCanvas;
};

// NEW: Helper function to blend two images by summing their pixel values
const sumImages = (canvas1, canvas2) => {
  const ctx1 = canvas1.getContext('2d');
  const ctx2 = canvas2.getContext('2d');

  // Assuming both canvases have the same dimensions (they should after tinting)
  const width = canvas1.width;
  const height = canvas1.height;

  const imageData1 = ctx1.getImageData(0, 0, width, height);
  const data1 = imageData1.data;
  const imageData2 = ctx2.getImageData(0, 0, width, height);
  const data2 = imageData2.data;

  // Create a new canvas to hold the summed image
  const summedCanvas = document.createElement('canvas');
  const summedCtx = summedCanvas.getContext('2d');
  summedCanvas.width = width;
  summedCanvas.height = height;
  const summedImageData = summedCtx.createImageData(width, height);
  const summedData = summedImageData.data;

  for (let i = 0; i < data1.length; i += 4) {
    // Sum R, G, B channels, clamping at 255
    summedData[i] = Math.min(255, data1[i] + data2[i]);         // Red
    summedData[i + 1] = Math.min(255, data1[i + 1] + data2[i + 1]); // Green
    summedData[i + 2] = Math.min(255, data1[i + 2] + data2[i + 2]); // Blue
    summedData[i + 3] = Math.min(255, data1[i + 3] + data2[i + 3]); // Alpha (summing alphas might result in opaque)
  }

  summedCtx.putImageData(summedImageData, 0, 0);
  return summedCanvas;
};

// if someone clicks the difference button show the difference image and unselect the other radio buttons
const loadDiff = () => {
  const canvas = canvas__compare.value;
  const ctx = canvas.getContext('2d');
  const image = new Image();

  image.crossOrigin = 'anonymous';

  image.onload = () => {
    canvas.width = image.width;
    canvas.height = image.height;
    ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear canvas before drawing
    ctx.drawImage(image, 0, 0);
  };

  image.onerror = () => {
    console.error(`Failed to load image: ${props.diffName}`);
  };

  image.src = getCacheBustedUrl(props.diffName);

  // unselect the other radio buttons
  document.getElementById("control").checked = false;
  document.getElementById("blink").checked = false;
};

// if someone clicks the control button show controlName tinted orange with imageName tinted blue at 50% opacity on top
const loadControl = () => {
  const canvas = canvas__compare.value;
  const ctx = canvas.getContext('2d');

  if (!canvas) return;

  const controlImage = new Image();
  const testImage = new Image();
  controlImage.crossOrigin = 'anonymous';
  testImage.crossOrigin = 'anonymous';

  let controlImageLoaded = false;
  let testImageLoaded = false;

  const drawCombinedImages = () => {
    if (controlImageLoaded && testImageLoaded) {
      // Step 1: Tint both images
      const tintedControlImageCanvas = applyTint(controlImage, [255, 255, 0]); // Yellow (RGB)
      const tintedTestImageCanvas = applyTint(testImage, [0, 0, 255]);   // Blue (RGB)

      // Step 2: Sum the tinted images
      const summedImageCanvas = sumImages(tintedControlImageCanvas, tintedTestImageCanvas);

      // Set canvas dimensions to match summed image
      canvas.width = summedImageCanvas.width;
      canvas.height = summedImageCanvas.height;

      // Step 3: Draw the summed image onto the main canvas
      ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear canvas
      ctx.drawImage(summedImageCanvas, 0, 0);
    }
  };

  controlImage.onload = () => {
    controlImageLoaded = true;
    drawCombinedImages();
  };

  testImage.onload = () => {
    testImageLoaded = true;
    drawCombinedImages();
  };

  controlImage.onerror = () => {
    console.error(`Failed to load control image: ${props.controlName}`);
  };

  testImage.onerror = () => {
    console.error(`Failed to load test image: ${props.imageName}`);
  };

  controlImage.src = getCacheBustedUrl(props.controlName);
  testImage.src = getCacheBustedUrl(props.imageName);

  // unselect the other radio buttons
  document.getElementById("diff").checked = false;
  document.getElementById("blink").checked = false;
  document.getElementById("control").checked = true; // Ensure control is checked
};

// if someone clicks the blink button, blink between imageName and controlName every 500ms for 5 seconds then stop and show the control image and check the control radio button
const loadBlink = () => {
  const canvas = canvas__compare.value;
  const ctx = canvas.getContext('2d');
  const image1 = new Image(); // This will be controlName
  const image2 = new Image(); // This will be imageName

  image1.crossOrigin = 'anonymous';
  image2.crossOrigin = 'anonymous';

  // uncheck the other radio buttons
  document.getElementById("control").checked = false;
  document.getElementById("diff").checked = false;

  let blinkCount = 0;
  let blinkInterval = setInterval(() => {
    if (blinkCount < 10) { // 10 blinks = 5 seconds (500ms * 10)
      document.getElementById("control").checked = false;
      document.getElementById("diff").checked = false;
      ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear canvas for blinking
      if (blinkCount % 2 === 0) {
        ctx.drawImage(image1, 0, 0);
      } else {
        ctx.drawImage(image2, 0, 0);
      }
      blinkCount++;
    } else {
      clearInterval(blinkInterval);
      loadControl(); // Stop blinking and show the control view
    }
  }, 500);

  // Ensure both images are loaded before starting the blink
  let image1Loaded = false;
  let image2Loaded = false;

  image1.onload = () => {
    image1Loaded = true;
    if (image1Loaded && image2Loaded) {
      // Start blinking immediately after both images are loaded
      blinkCount = 0; // Reset blink count to ensure proper start
      // Initial draw for blinking
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(image1, 0, 0);
    }
  };

  image2.onload = () => {
    image2Loaded = true;
    if (image1Loaded && image2Loaded) {
      // Start blinking immediately after both images are loaded
      blinkCount = 0; // Reset blink count to ensure proper start
      // Initial draw for blinking (if image1 loaded first, this won't be drawn again)
      if (!image1Loaded) { // Only draw if image1 isn't ready
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(image2, 0, 0);
      }
    }
  };

  image1.onerror = () => {
    console.error(`Failed to load image for blink (Control): ${props.controlName}`);
  };

  image2.onerror = () => {
    console.error(`Failed to load image for blink (Test): ${props.imageName}`);
  };

  image1.src = getCacheBustedUrl(props.controlName);
  image2.src = getCacheBustedUrl(props.imageName);
};

// You can add a drawRectangle function if needed, but it's not directly related to the image loading logic
const drawRectangle = (event) => {
  // Your existing drawRectangle logic here
  console.log("Canvas clicked at:", event.offsetX, event.offsetY);
};
</script>

<style scoped>
/* Your existing styles here */
#canvas__compare {
  border: 1px solid black;
  display: block; /* Ensures no extra space below canvas */
  margin: 0 auto; /* Center the canvas */
}

#compare-buttons-panel {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  margin-top: 10px;
}

.compare-column {
  margin: 0 15px;
}
</style>