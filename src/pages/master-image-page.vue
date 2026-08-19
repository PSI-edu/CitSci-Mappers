<template>
  <PageLayout>
    <div class="content-layout">
      <h1 id="page-title" class="content__title">Data Review</h1>
      <div class="content__body">
        <div class="control-group">
          <label for="masters">Which image do you want to view?</label>
          <select
              id="masters"
              name="masters"
              v-model="selectedSetId"
              @change="handleSetChange"
          >
            <option value="" disabled>-- Select a master image --</option>
            <option v-for="set in imageSets" :key="set.id" :value="set.id">
              {{ set.name }}
            </option>
          </select>

          <!-- Status Indicators -->
          <span v-if="isLoading" class="status">Loading list...</span>
          <span v-if="isProcessingImage" class="status processing">Processing...</span>
          <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

          <span v-if="isLoading" class="status">Loading...</span>
          <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        </div>

        <!-- Canvas Container -->
        <div v-show="imageUrl" class="canvas-container">
          <canvas ref="imageCanvas" class="responsive-canvas"></canvas>
        </div>
      </div>
    </div>
  </PageLayout>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import apiClient from '@/api/axios';
import PageLayout from "@/components/page-layout.vue";

// --- State Variables ---
const errorMessage = ref('');
const isLoading = ref(false);
const imageSets = ref([]);
const selectedSetId = ref('');
const imageUrl = ref('');
const isProcessingImage = ref(false);

// --- Canvas Ref ---
const imageCanvas = ref(null);

// --- API Endpoints ---
const API_SERVER = import.meta.env.VITE_MAPPERS_API_SERVER; // Adjust to your PHP API base route

onMounted(async () => {
  try {
    const response = await apiClient.post(`${API_SERVER}/masterimages-list.php`);
    imageSets.value = response.data;
    console.log(response.data)
  } catch (error) {
    console.error('Failed to load image sets:', error);
  }
})

// --- Event Handlers ---
const handleSetChange = () => {
  // Reset previous error
  errorMessage.value = '';

  // Find the matching item from the array loaded during onMounted
  const selectedItem = imageSets.value.find(set => set.id === selectedSetId.value);

  if (selectedItem && selectedItem.details) {
    imageUrl.value = selectedItem.details;
  } else {
    imageUrl.value = '';
    errorMessage.value = 'Image URL not found for this selection.';
  }
};

// --- Draw Image to Canvas ---
const drawImageToCanvas = () => {
  if (!imageUrl.value || !imageCanvas.value) return;

  // Signal that image loading & drawing processing has started
  isProcessingImage.value = true;

  const canvas = imageCanvas.value;
  const ctx = canvas.getContext('2d');
  const img = new Image();

  img.onload = () => {
    // 1. Calculate container scaling
    const containerWidth = canvas.parentElement.clientWidth;
    const scale = containerWidth / img.width; // Scale ratio between canvas and original image

    // 2. Set Canvas dimensions based on scale
    canvas.width = containerWidth;
    canvas.height = img.height * scale;

    // 3. Clear and draw base image
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

    // 4. Block parameters (in original image pixels)
    const blockSize = 450;
    const overlapRatio = 0.10;
    const stride = blockSize * (1 - overlapRatio); // 405px step

    // Configure stroke styling
    ctx.strokeStyle = '#FFFFFF';
    ctx.lineWidth = 2; // Adjust border thickness as needed

    // Configure text styling
    const fontSize = Math.max(12, Math.round(14 * scale)); // Scales font, minimum 12px
    ctx.font = `bold ${fontSize}px sans-serif`;
    ctx.fillStyle = '#FFFFFF';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';

    // 5. Nested Loops: Y-axis outer, X-axis inner
    for (let y = 0; y < img.height; y += stride) {
      for (let x = 0; x < img.width; x += stride) {

        // Scale block coordinates to match canvas dimensions
        const canvasX = x * scale;
        const canvasY = y * scale;
        const currentWidth = Math.min(blockSize, img.width - x);
        const currentHeight = Math.min(blockSize, img.height - y);
        const canvasBlockWidth = Math.min(blockSize, img.width - x) * scale;
        const canvasBlockHeight = Math.min(blockSize, img.height - y) * scale;

        // Draw rectangle stroke (no fill)
        ctx.strokeRect(canvasX, canvasY, canvasBlockWidth, canvasBlockHeight);

        // Top center coordinate calculation for text positioning
        const textX = canvasX + (canvasBlockWidth / 2);
        const textY = canvasY + (4 * scale); // 4px padding from top border

        // Label string using original image pixel values
        const labelText = `${Math.round(x)},${Math.round(y)}`;

        // Optional text shadow for readability over light background areas
        ctx.shadowColor = 'black';
        ctx.shadowBlur = 4;

        // Draw text label at top center of box
        ctx.fillText(labelText, textX, textY);

        // Reset shadow so it doesn't leak into other operations
        ctx.shadowBlur = 0;
      }
    }

    // Finished drawing: hide the Processing indicator
    isProcessingImage.value = false;

  };

  // Handle potential broken image paths or loading failures
  img.onerror = () => {
    isProcessingImage.value = false;
    errorMessage.value = 'Failed to load selected image file.';
  };

  img.src = imageUrl.value;
};

// Watch for imageUrl changes and redraw
watch (imageUrl, async () => {
 await nextTick();
 drawImageToCanvas();
});

</script>

<style scoped>
.canvas-container {
  width: 100%;
  margin-top: 1rem;
}

.responsive-canvas {
   width: 100%;
   height: auto;
   display: block;
 }
</style>

