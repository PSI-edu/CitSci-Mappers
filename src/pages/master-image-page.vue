const handleCanvasClick = (event) => {
if (!imageCanvas.value) return;

const canvas = imageCanvas.value;
const rect = canvas.getBoundingClientRect();

// 1. Calculate click position on the canvas in pixels
const clickX = event.clientX - rect.left;
const clickY = event.clientY - rect.top;

// 2. Convert canvas click coordinates to original image scale
const scale = canvas.width / canvas.offsetWidth; // Handles CSS vs Canvas pixel scaling
const containerWidth = canvas.width;

// Get original image dimensions ratio from canvas width
// (We use canvas width / container scaling to reconstruct original image coords)
const imgWidth = imageCanvas.value.dataset.imgWidth;
const imageScale = containerWidth / imgWidth;

const originalX = clickX * scale / imageScale;
const originalY = clickY * scale / imageScale;

// 3. Block overlap dimensions
const blockSize = 450;
const stride = 405; // 450 * 0.9

// Check if X or Y falls within the 405px - 450px window of any tile
const isXInOverlap = (originalX % stride) >= stride && (originalX % stride) < blockSize;
const isYInOverlap = (originalY % stride) >= stride && (originalY % stride) < blockSize;

// Only open modal if click is completely OUTSIDE overlap regions
if (!isXInOverlap && !isYInOverlap) {
isModalOpen.value = true;
}
};