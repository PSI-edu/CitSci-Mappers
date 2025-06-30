<template>
  <canvas ref="canvas__compare" id="canvas__compare" width="450" height="450"></canvas>
  <div id="compare-buttons-panel">
    <strong>Show me:</strong>

    <input type="radio" id="diff" name="compare_mode" value="difference" @click="loadDiff()">
    <label for="diff">Difference</label>

    <input type="radio" id="blink" name="compare_mode" value="blink" @click="loadBlink()">
    <label for="blink">Blink images</label>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  controlName: String,
  imageName: String,
});

let blinkIntervalId = null;

// To stop CORS issues with image loading
const getCacheBustedUrl = (url) => {
  return `${url}?t=${new Date().getTime()}`;
};

const canvas__compare = ref(null);

onMounted(() => {
  // Initially load the "difference" image
  loadDiff();
});

watch(() => props.imageName, () => {
  // Reload the view if the input image changes
  loadDiff();
});

// Helper function to blend two images by summing their pixel values
const sumImages = (canvas1, canvas2) => {
  const ctx1 = canvas1.getContext('2d');
  const ctx2 = canvas2.getContext('2d');

  const width = canvas1.width;
  const height = canvas1.height;

  const imageData1 = ctx1.getImageData(0, 0, width, height);
  const data1 = imageData1.data;
  const imageData2 = ctx2.getImageData(0, 0, width, height);
  const data2 = imageData2.data;

  const summedCanvas = document.createElement('canvas');
  const summedCtx = summedCanvas.getContext('2d');
  summedCanvas.width = width;
  summedCanvas.height = height;
  const summedImageData = summedCtx.createImageData(width, height);
  const summedData = summedImageData.data;

  for (let i = 0; i < data1.length; i += 4) {
    // Sum R, G, B, and Alpha channels, clamping at 255
    summedData[i] = Math.min(255, Math.abs(data1[i] - data2[i]));
    summedData[i + 1] = Math.min(255, Math.abs(data1[i] - data2[i]));
    summedData[i + 2] = Math.min(255, Math.abs(data1[i] - data2[i]));
    summedData[i + 3] = Math.min(255, data1[i + 3] + data2[i + 3]);
  }

  summedCtx.putImageData(summedImageData, 0, 0);
  return summedCanvas;
};

// if someone clicks the sum button, show the sum of controlName and imageName
const loadDiff = () => {
  if (blinkIntervalId) {
    clearInterval(blinkIntervalId);
    blinkIntervalId = null;
  }

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
      // Step 1: Create temporary canvases for the original images
      const controlCanvas = document.createElement('canvas');
      const controlCtx = controlCanvas.getContext('2d');
      controlCanvas.width = controlImage.width;
      controlCanvas.height = controlImage.height;
      controlCtx.drawImage(controlImage, 0, 0);

      const testCanvas = document.createElement('canvas');
      const testCtx = testCanvas.getContext('2d');
      testCanvas.width = testImage.width;
      testCanvas.height = testImage.height;
      testCtx.drawImage(testImage, 0, 0);

      // Step 2: Sum the images from their canvases
      const summedImageCanvas = sumImages(controlCanvas, testCanvas);

      // Set main canvas dimensions to match the resulting image
      canvas.width = summedImageCanvas.width;
      canvas.height = summedImageCanvas.height;

      // Step 3: Draw the final summed image
      ctx.clearRect(0, 0, canvas.width, canvas.height);
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
  controlImage.onerror = () => console.error(`Failed to load control image: ${props.controlName}`);
  testImage.onerror = () => console.error(`Failed to load test image: ${props.imageName}`);

  controlImage.src = getCacheBustedUrl(props.controlName);
  testImage.src = getCacheBustedUrl(props.imageName);

  // Ensure the correct radio button is checked
  document.getElementById("diff").checked = true;
};

// if someone clicks the blink button, blink between imageName and controlName for 5 seconds
const loadBlink = () => {
  // Clear any previously running interval before starting a new one.
  if (blinkIntervalId) {
    clearInterval(blinkIntervalId);
  }

  const canvas = canvas__compare.value;
  const ctx = canvas.getContext('2d');
  const image1 = new Image(); // controlName
  const image2 = new Image(); // imageName
  image1.crossOrigin = 'anonymous';
  image2.crossOrigin = 'anonymous';

  let image1Loaded = false;
  let image2Loaded = false;

  const startBlinkingWhenReady = () => {
    // Ensure both images are loaded before starting.
    if (!image1Loaded || !image2Loaded) {
      return;
    }

    canvas.width = image1.width;
    canvas.height = image1.height;
    let currentImage = 0;

    // Draw the first image immediately to avoid a blank canvas.
    ctx.drawImage(image1, 0, 0);
    currentImage = 1;

    // Start the interval and store its ID. It will run forever.
    blinkIntervalId = setInterval(() => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      if (currentImage === 0) {
        ctx.drawImage(image1, 0, 0);
        currentImage = 1;
      } else {
        ctx.drawImage(image2, 0, 0);
        currentImage = 0;
      }
    }, 500);
  };

  image1.onload = () => {
    image1Loaded = true;
    startBlinkingWhenReady();
  };
  image2.onload = () => {
    image2Loaded = true;
    startBlinkingWhenReady();
  };

  const errorHandler = (imgName) => {
    console.error(`Failed to load image for blink: ${imgName}`);
    // Ensure we stop the interval if an image fails to load.
    if (blinkIntervalId) {
      clearInterval(blinkIntervalId);
      blinkIntervalId = null;
    }
  };

  image1.onerror = () => errorHandler(props.controlName);
  image2.onerror = () => errorHandler(props.imageName);

  image1.src = getCacheBustedUrl(props.controlName);
  image2.src = getCacheBustedUrl(props.imageName);
};
</script>

<style scoped>
#canvas__compare {
  border: 1px solid black;
  display: block;
  margin: 0 auto;
}

#compare-buttons-panel {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 10px;
  gap: 5px; /* Adds space between labels and inputs */
}

#compare-buttons-panel label {
  margin-right: 15px;
}
</style>