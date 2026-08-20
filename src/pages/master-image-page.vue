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

        <!-- Master Canvas Container -->
        <div v-show="imageUrl" class="canvas-container">
          <canvas
              ref="imageCanvas"
              class="responsive-canvas"
              @click="handleCanvasClick"
          ></canvas>
        </div>
      </div>
    </div>

    <!-- Floating Blue Modal -->
    <div v-if="isModalOpen" class="floating-modal-overlay">
      <div class="floating-modal">
        <button class="close-btn" @click="closeModal" aria-label="Close modal">&times;</button>
        <div class="modal-content">
          <!-- File Name & Done Status Label -->
          <p v-if="tileFileName" class="tile-filename">
            {{ tileFileName }} | Done: {{ doneStatusText }}
          </p>

          <!-- Toggle Filters for Marks -->
          <div class="marks-filters">
            <label class="filter-item">
              <input type="checkbox" v-model="showCraters" @change="redrawTileCanvas" />
              craters
            </label>
            <label class="filter-item">
              <input type="checkbox" v-model="showRocks" @change="redrawTileCanvas" />
              rocks
            </label>
            <label class="filter-item">
              <input type="checkbox" v-model="showBoulders" @change="redrawTileCanvas" />
              boulders
            </label>
            <label class="filter-item">
              <input type="checkbox" v-model="showMargins" @change="redrawTileCanvas" />
              margins
            </label>
            <label class="filter-item">
              <input type="checkbox" v-model="showWrinkles" @change="redrawTileCanvas" />
              wrinkles
            </label>
            <label class="filter-item">
              <input type="checkbox" v-model="showCracks" @change="redrawTileCanvas" />
              cracks
            </label>
          </div>

          <span v-if="isTileLoading" class="status">Loading sub-tile image...</span>
          <!-- 450x450 Canvas for Sub-tile Image -->
          <canvas
              ref="tileCanvas"
              width="450"
              height="450"
              class="tile-canvas"
          ></canvas>
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

// Done status tiles map: "x,y" -> done status (0 or 1)
const doneTilesMap = ref(new Map());

// Modal & Sub-tile Canvas state
const isModalOpen = ref(false);
const isTileLoading = ref(false);
const tileFileName = ref('');
const doneStatusText = ref('-');
const tileMarks = ref([]);
const currentTileImage = ref(null);

// Mark Visibility Checkbox Filters
const showCraters = ref(true);
const showRocks = ref(true);
const showBoulders = ref(true);
const showMargins = ref(true);
const showWrinkles = ref(true);
const showCracks = ref(true);

// --- Canvas Refs ---
const imageCanvas = ref(null);
const tileCanvas = ref(null);

// --- API Endpoints ---
const API_SERVER = import.meta.env.VITE_MAPPERS_API_SERVER;

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

// Helper: Transforms master URL to sub-tile URL and extracts filename
const constructTileUrl = (mainUrl, x, y) => {
  const cleanUrl = mainUrl.split('?')[0];

  const lastSlashIndex = cleanUrl.lastIndexOf('/');
  const path = cleanUrl.substring(0, lastSlashIndex);
  const filenameWithExt = cleanUrl.substring(lastSlashIndex + 1);

  const lastDotIndex = filenameWithExt.lastIndexOf('.');
  const filenameBase = filenameWithExt.substring(0, lastDotIndex);
  const ext = filenameWithExt.substring(lastDotIndex);

  const roundedX = Math.round(x);
  const roundedY = Math.round(y);

  const tileName = `${filenameBase}_${roundedX}-${roundedY}${ext}`;
  const fullTileUrl = `${path}/${filenameBase}/${tileName}`;

  return { fullTileUrl, tileName };
};

// --- Draw Base Image + Marks on Sub-Tile Canvas ---
const redrawTileCanvas = () => {
  if (!tileCanvas.value || !currentTileImage.value) return;

  const canvas = tileCanvas.value;
  const ctx = canvas.getContext('2d');

  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.drawImage(currentTileImage.value, 0, 0, 450, 450);

  if (!tileMarks.value || tileMarks.value.length === 0) return;

  tileMarks.value.forEach((mark) => {
    let detailsObj = mark.details;
    if (typeof detailsObj === 'string') {
      try {
        detailsObj = JSON.parse(detailsObj);
      } catch (e) {
        // Ignored unparseable JSON quietly
      }
    }

    const isMargin = mark.type === 'margin' || detailsObj?.type === 'margin';
    const isCrack = mark.type === 'crack' || detailsObj?.type === 'crack';
    const isWrinkle = mark.type === 'wrinkle' || detailsObj?.type === 'wrinkle';

    // 1. Render Craters if checked
    if (mark.type === 'crater') {
      if (showCraters.value) {
        const centerX = Number(mark.x1);
        const centerY = Number(mark.y1);
        const radius = Number(mark.diameter) / 2;

        ctx.save();
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.fillStyle = 'rgba(255, 0, 0, 0.5)';
        ctx.fill();
        ctx.strokeStyle = 'rgba(255, 0, 0, 0.8)';
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.restore();
      }
    }
    // 2. Render Rocks if checked
    else if (mark.type === 'rock') {
      if (showRocks.value) {
        const centerX = Number(mark.x1);
        const centerY = Number(mark.y1);
        const radius = 5 / 2;

        ctx.save();
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.fillStyle = 'rgba(0, 0, 255, 0.1)';
        ctx.fill();
        ctx.strokeStyle = '#FFFFFF';
        ctx.lineWidth = 1;
        ctx.stroke();
        ctx.restore();
      }
    }
    // 3. Render Boulders if checked
    else if (mark.type === 'boulder') {
      if (showBoulders.value) {
        const startX = Number(mark.x1);
        const startY = Number(mark.y1);
        const endX = Number(mark.x2);
        const endY = Number(mark.y2);

        ctx.save();
        ctx.beginPath();
        ctx.moveTo(startX, startY);
        ctx.lineTo(endX, endY);
        ctx.strokeStyle = 'rgba(0, 255, 0, 0.1)';
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.restore();
      }
    }
    // 4. Render Margins (Segmented Red Lines) if checked
    else if (isMargin) {
      if (showMargins.value) {
        const points = detailsObj?.data?.points || detailsObj?.points;

        if (Array.isArray(points) && points.length > 1) {
          ctx.save();
          ctx.beginPath();
          ctx.moveTo(Number(points[0].x), Number(points[0].y));

          for (let i = 1; i < points.length; i++) {
            ctx.lineTo(Number(points[i].x), Number(points[i].y));
          }

          ctx.strokeStyle = '#FF0000'; // Solid Red
          ctx.lineWidth = 2;
          ctx.stroke();
          ctx.restore();
        }
      }
    }
    // 5. Render Cracks (Segmented Blue Lines) if checked
    else if (isCrack) {
      if (showCracks.value) {
        const points = detailsObj?.data?.points || detailsObj?.points;

        if (Array.isArray(points) && points.length > 1) {
          ctx.save();
          ctx.beginPath();
          ctx.moveTo(Number(points[0].x), Number(points[0].y));

          for (let i = 1; i < points.length; i++) {
            ctx.lineTo(Number(points[i].x), Number(points[i].y));
          }

          ctx.strokeStyle = '#0000FF'; // Solid Blue
          ctx.lineWidth = 2;
          ctx.stroke();
          ctx.restore();
        }
      }
    }
    // 6. Render Wrinkles (Segmented Green Lines) if checked
    else if (isWrinkle) {
      if (showWrinkles.value) {
        const points = detailsObj?.data?.points || detailsObj?.points;

        if (Array.isArray(points) && points.length > 1) {
          ctx.save();
          ctx.beginPath();
          ctx.moveTo(Number(points[0].x), Number(points[0].y));

          for (let i = 1; i < points.length; i++) {
            ctx.lineTo(Number(points[i].x), Number(points[i].y));
          }

          ctx.strokeStyle = '#00FF00'; // Solid Green
          ctx.lineWidth = 2;
          ctx.stroke();
          ctx.restore();
        }
      }
    }
  });
};

// --- Fetch Marks Data ---
const fetchMarksData = async (tileName) => {
  doneStatusText.value = '...';
  tileMarks.value = [];

  try {
    const response = await apiClient.post(`${API_SERVER}/marks-get.php`, { name: tileName });
    const data = response.data;

    const activeStatus = [];
    if (data?.features == 1 || data?.features === true) {
      activeStatus.push('features');
    }
    if (data?.flows == 1 || data?.flows === true) {
      activeStatus.push('flows');
    }

    doneStatusText.value = activeStatus.length > 0 ? activeStatus.join(', ') : '-';

    if (Array.isArray(data?.marks)) {
      tileMarks.value = data.marks;
    }

    redrawTileCanvas();
  } catch (error) {
    console.error(`Failed to fetch marks for ${tileName}:`, error);
    doneStatusText.value = '-';
  }
};

// --- Fetch Done Image Data ---
const fetchDoneImageData = async (imageName) => {
  try {
    const response = await apiClient.post(`${API_SERVER}/image-list-done.php`, {
      name: imageName,
      application_id: 3
    });

    console.log('image-list-done response:', response.data);

    const map = new Map();
    if (Array.isArray(response.data)) {
      response.data.forEach(tile => {
        const roundX = Math.round(Number(tile.x));
        const roundY = Math.round(Number(tile.y));
        map.set(`${roundX},${roundY}`, tile.done);
      });
    }

    doneTilesMap.value = map;
    drawImageToCanvas();
  } catch (error) {
    console.error(`Failed to fetch done image data for ${imageName}:`, error);
  }
};

// --- Event Handlers ---
const handleSetChange = () => {
  errorMessage.value = '';
  doneTilesMap.value = new Map();
  const selectedItem = imageSets.value.find(set => set.id === selectedSetId.value);

  if (selectedItem && selectedItem.details) {
    imageUrl.value = selectedItem.details;
    fetchDoneImageData(selectedItem.name);
  } else {
    imageUrl.value = '';
    errorMessage.value = 'Image URL not found for this selection.';
  }
};

const handleCanvasClick = (event) => {
  if (!imageCanvas.value) return;

  const canvas = imageCanvas.value;
  const rect = canvas.getBoundingClientRect();

  const imgWidth = Number(canvas.dataset.imgWidth);
  if (!imgWidth) return;

  const scale = imgWidth / rect.width;
  const originalX = (event.clientX - rect.left) * scale;
  const originalY = (event.clientY - rect.top) * scale;

  const blockSize = 450;
  const stride = 405;
  const imgHeight = (canvas.height / canvas.width) * imgWidth;

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

  if (!isXInOverlap && !isYInOverlap) {
    const blockX = Math.floor(originalX / stride) * stride;
    const blockY = Math.floor(originalY / stride) * stride;

    openModalWithTile(blockX, blockY);
  }
};

const openModalWithTile = async (x, y) => {
  isModalOpen.value = true;
  isTileLoading.value = true;

  const { fullTileUrl, tileName } = constructTileUrl(imageUrl.value, x, y);
  tileFileName.value = tileName;

  await nextTick();

  if (!tileCanvas.value) return;

  const canvas = tileCanvas.value;
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const tileImg = new Image();
  tileImg.onload = () => {
    currentTileImage.value = tileImg;
    isTileLoading.value = false;

    fetchMarksData(tileName);
  };

  tileImg.onerror = () => {
    isTileLoading.value = false;
    errorMessage.value = `Failed to load tile image: ${fullTileUrl}`;
  };

  tileImg.src = fullTileUrl;
};

const closeModal = () => {
  isModalOpen.value = false;
  tileFileName.value = '';
  doneStatusText.value = '-';
  tileMarks.value = [];
  currentTileImage.value = null;

  showCraters.value = true;
  showRocks.value = true;
  showBoulders.value = true;
  showMargins.value = true;
  showWrinkles.value = true;
  showCracks.value = true;
};

// --- Draw Image to Main Canvas ---
const drawImageToCanvas = () => {
  if (!imageUrl.value || !imageCanvas.value) return;

  isProcessingImage.value = true;

  const canvas = imageCanvas.value;
  const ctx = canvas.getContext('2d');
  const img = new Image();

  img.onload = () => {
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

        const roundX = Math.round(x);
        const roundY = Math.round(y);
        const doneStatus = doneTilesMap.value.get(`${roundX},${roundY}`);

        // Highlight completed tiles in 20% transparent yellow
        if (doneStatus == 1 || doneStatus === true) {
          ctx.fillStyle = 'rgba(255, 255, 0, 0.2)';
          ctx.fillRect(canvasX, canvasY, canvasBlockWidth, canvasBlockHeight);
        }

        ctx.strokeRect(canvasX, canvasY, canvasBlockWidth, canvasBlockHeight);

        const textX = canvasX + (canvasBlockWidth / 2);
        const textY = canvasY + (4 * scale);
        const labelText = `${roundX},${roundY}`;

        ctx.fillStyle = '#FFFFFF';
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

.floating-modal {
  position: relative;
  width: 540px;
  height: 560px;
  background-color: #1e40af;
  color: #ffffff;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  padding: 30px 20px 20px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.close-btn {
  position: absolute;
  top: 8px;
  right: 14px;
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
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.tile-filename {
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 6px;
  word-break: break-all;
  text-align: center;
}

.marks-filters {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 12px;
  margin-bottom: 10px;
  align-items: center;
}

.filter-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  user-select: none;
}

.filter-item input[type="checkbox"] {
  cursor: pointer;
  accent-color: #3b82f6;
}

.tile-canvas {
  width: 450px;
  height: 450px;
  background-color: #1d4ed8;
  border-radius: 4px;
}
</style>