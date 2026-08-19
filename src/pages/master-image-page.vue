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
          <span v-if="isProcessingImage" class="status processing"> Processing...</span>
          <p v-if="errorMessage" class="error"> {{ errorMessage }}</p>
        </div>

        <!-- Canvas Container -->
        <div v-show="imageUrl" class="canvas-container">
          <canvas
              ref="imageCanvas"
              class="responsive-canvas"
              @click="handleCanvasClick"
          ></canvas>
        </div>
      </div>
    </div>

    <!-- Floating 500x500 Blue Modal -->
    <div v-if="isModalOpen" class="floating-modal-overlay">
      <div class="floating-modal">
        <button class="close-btn" @click="closeModal" aria-label="Close modal">&times;</button>
        <div class="modal-content">
          <h2>Details</h2>
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
const isModalOpen = ref(false);

// --- Canvas Ref ---
const imageCanvas = ref(null);

// --- API Endpoints ---
const API_SERVER = import.meta.env.VITE_MAPPERS_API_SERVER; // Adjust to your PHP API base route

onMounted(async () => {
  isLoading.value = true;
  try {
    const response = await apiClient.post(`${API_SERVER}/masterimages-list.php`);
    imageSets.value = response.data;
  } catch (error) {
    console.error('Failed to load image sets:', error);
    errorMessage.value = 'Failed to load master images.';
  } finally {
    isLoading.value = false;
  }
});

// --- Event Handlers ---
const handleSetChange = () => {
  errorMessage.value = '';
  const selectedItem = imageSets.value.find(set => set.id === selectedSetId.value);

  if (selectedItem && selectedItem.details) {
    imageUrl.value = selectedItem.details;
  } else {
    imageUrl.value = '';
    errorMessage.value = 'Image URL not found for this selection.';
  }
};

const handleCanvasClick = (event) => {
  if (!imageCanvas.value) return;

  const canvas = imageCanvas.value;
  const rect = canvas.getBoundingClientRect();

  // 1. Convert click coordinates relative to original image size
  const imgWidth = Number(canvas.dataset.imgWidth);
  if (!imgWidth) return;

  const scale = imgWidth / rect.width;
  const originalX = (event.clientX - rect.left) * scale;
  const originalY = (event.clientY - rect.top) * scale;

  const blockSize = 450;
  const stride = 405; // 10% overlap step (450 * 0.9)
  const imgHeight = (canvas.height / canvas.width) * imgWidth;

  // Helper function: returns true if coordinate falls inside ANY tile's overlap strip
  const checkInOverlap = (coord, maxLimit) => {
    for (let start = 0; start < maxLimit; start += stride) {
      if (coord >= start + stride && coord < start + blockSize) {
        return true;
      }
    }
    return false;
  };

  const isXInOverlap = checkInOverlap(originalX, imgWidth);
  const isYInOverlap = checkInOverlap(originalY, imgHeight);

  // Only open modal if the click is OUTSIDE all overlap regions on both axes
  if (!isXInOverlap && !isYInOverlap) {
    isModalOpen.value = true;
  }
};

const closeModal = () => {
  isModalOpen.value = false;
};

// --- Draw Image to Canvas ---
const drawImageToCanvas = () => {
  if (!imageUrl.value || !imageCanvas.value) return;

  isProcessingImage.value = true;

  const canvas = imageCanvas.value;
  const ctx = canvas.getContext('2d');
  const img = new Image();

  img.onload = () => {
    // Store original image width for click calculation
    canvas.dataset.imgWidth = img.width;

    const containerWidth = canvas.parentElement.clientWidth;
    const scale = containerWidth / img.width;

    canvas.width = containerWidth;
    canvas.height = img.height * scale;

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

    const blockSize = 450;
    const overlapRatio = 0.10;
    const stride = blockSize * (1 - overlapRatio);

    ctx.strokeStyle = '#FFFFFF';
    ctx.lineWidth = 2;

    const fontSize = Math.max(12, Math.round(14 * scale));
    ctx.font = `bold ${fontSize}px sans-serif`;
    ctx.fillStyle = '#FFFFFF';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';

    for (let y = 0; y < img.height; y += stride) {
      for (let x = 0; x < img.width; x += stride) {
        const canvasX = x * scale;
        const canvasY = y * scale;
        const currentWidth = Math.min(blockSize, img.width - x);
        const currentHeight = Math.min(blockSize, img.height - y);
        const canvasBlockWidth = currentWidth * scale;
        const canvasBlockHeight = currentHeight * scale;

        ctx.strokeRect(canvasX, canvasY, canvasBlockWidth, canvasBlockHeight);

        const textX = canvasX + (canvasBlockWidth / 2);
        const textY = canvasY + (4 * scale);
        const labelText = `${Math.round(x)},${Math.round(y)}`;

        ctx.shadowColor = 'black';
        ctx.shadowBlur = 4;
        ctx.fillText(labelText, textX, textY);
        ctx.shadowBlur = 0;
      }
    }

    isProcessingImage.value = false;
  };

  img.onerror = () => {
    isProcessingImage.value = false;
    errorMessage.value = 'Failed to load selected image file.';
  };

  img.src = imageUrl.value;
};

// Watch for imageUrl changes and redraw
watch(imageUrl, async () => {
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
  cursor: pointer;
}

/* Floating Modal Overlay */
.floating-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* Fixed 500px by 500px Blue Box */
.floating-modal {
  position: relative;
  width: 500px;
  height: 500px;
  background-color: #1e40af;
  color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  padding: 20px;
  box-sizing: border-box;
}

.close-btn {
  position: absolute;
  top: 12px;
  right: 16px;
  background: transparent;
  border: none;
  color: #ffffff;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
  line-height: 1;
}

.close-btn:hover {
  color: #93c5fd;
}

.modal-content {
  height: 100%;
  display: flex;
  flex-direction: column;
}
</style>