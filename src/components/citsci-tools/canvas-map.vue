<template>
  <div style="position: relative; display: inline-block;">
    <canvas ref="canvas__map" id="canvas__map"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, defineProps, defineEmits } from 'vue';

const props = defineProps({
  imageName: String,
  mode: String,
  drawings: Array,
});

const emit = defineEmits(['draw', 'clearDrawing']);

const canvas__map = ref(null);
const canvasWidth = ref(0);
const canvasHeight = ref(0);
const ctx = ref(null);
const isDrawing = ref(false);
const startPoint = ref(null);
const currentDrawing = ref(null);

const setDrawingMode = (newMode) => {
  // Reset drawing state when mode changes
  isDrawing.value = false;
  startPoint.value = null;
  currentDrawing.value = null;
};

const handleMouseDown = (event) => {
  if (!props.mode || props.mode === 'erase') return;
  isDrawing.value = true;
  startPoint.value = { x: event.offsetX, y: event.offsetY };
  currentDrawing.value = { type: props.mode, data: {} };
};

const handleMouseMove = (event) => {
  if (!isDrawing.value || !currentDrawing.value) return;

  const currentX = event.offsetX;
  const currentY = event.offsetY;

  const canvas = canvas__map.value;
  const tempCtx = canvas.getContext('2d');
  tempCtx.clearRect(0, 0, canvas.width, canvas.height); // Clear temporary drawing

  // Redraw the base image
  const image = new Image();
  image.onload = () => {
    tempCtx.drawImage(image, 0, 0, canvas.width, canvas.height);
    // Redraw existing drawings
    props.drawings.forEach(existingDrawing => {
      drawShape(tempCtx, existingDrawing);
    });
    // Draw the current in-progress drawing
    drawShape(tempCtx, { ...currentDrawing.value, data: getCurrentShapeData(currentX, currentY) });
  };
  image.src = props.imageName;
};

const handleMouseUp = (event) => {
  if (!isDrawing.value || !currentDrawing.value) return;
  isDrawing.value = false;
  const endPoint = { x: event.offsetX, y: event.offsetY };
  currentDrawing.value.data = getCurrentShapeData(endPoint.x, endPoint.y);
  emit('draw', currentDrawing.value);
  currentDrawing.value = null;

  // Redraw the main canvas to include the new drawing permanently
  redrawCanvas();
};

const getCurrentShapeData = (x2, y2) => {
  if (props.mode === 'circle' && startPoint.value) {
    const dx = x2 - startPoint.value.x;
    const dy = y2 - startPoint.value.y;
    const radius = Math.sqrt(dx * dx + dy * dy);
    return { x: startPoint.value.x, y: startPoint.value.y, radius };
  } else if (props.mode === 'line' && startPoint.value) {
    return { x1: startPoint.value.x, y1: startPoint.value.y, x2, y2 };
  } else if (props.mode === 'dot') {
    return { x: x2, y: y2 };
  }
  return {};
};

const drawShape = (context, drawing) => {
  context.fillStyle = 'rgba(197, 131, 54, 0.2)'; // Default fill

  if (drawing.type === 'circle') {
    context.strokeStyle = '#c58336';
    context.lineWidth = 2;
    context.beginPath();
    context.arc(drawing.data.x, drawing.data.y, drawing.data.radius, 0, 2 * Math.PI);
    context.stroke();
    context.fill();
  } else if (drawing.type === 'line') {
    context.lineWidth = 2 * 2; // 2 times thicker
    context.strokeStyle = '#6f6e2a'; // Green color
    // Draw the green line
    context.beginPath();
    context.moveTo(drawing.data.x1, drawing.data.y1);
    context.lineTo(drawing.data.x2, drawing.data.y2);
    context.stroke();

    // Draw the white outline
    context.lineWidth = 1;
    context.strokeStyle = 'white';
    context.beginPath();
    context.moveTo(drawing.data.x1, drawing.data.y1);
    context.lineTo(drawing.data.x2, drawing.data.y2);
    context.stroke();

    // Reset line width for subsequent drawings
    context.lineWidth = 2;
  } else if (drawing.type === 'dot') {
    const dotRadius = 3 * 1.5; // 50% bigger
    context.fillStyle = '#29336c'; // Blue color
    context.strokeStyle = 'white';
    context.lineWidth = 1;
    context.beginPath();
    context.arc(drawing.data.x, drawing.data.y, dotRadius, 0, 2 * Math.PI);
    context.fill();
    context.stroke();
  }
};

const handleCanvasClick = (event) => {
  if (props.mode === 'erase') {
    const clickX = event.offsetX;
    const clickY = event.offsetY;
    // Iterate through the drawings and check if the click is near them
    for (let i = props.drawings.length - 1; i >= 0; i--) {
      const drawing = props.drawings[i];
      let distance;
      if (drawing.type === 'circle') {
        distance = Math.sqrt(
            Math.pow(clickX - drawing.data.x, 2) + Math.pow(clickY - drawing.data.y, 2)
        );
        if (distance < drawing.data.radius + 5) {
          emit('clearDrawing', i);
          break;
        }
      } else if (drawing.type === 'line') {
        // Need to implement a more robust line hit detection
        // For a simple approach, check if the click is within a certain distance of the line
        const dx = drawing.data.x2 - drawing.data.x1;
        const dy = drawing.data.y2 - drawing.data.y1;
        const lengthSq = dx * dx + dy * dy;
        if (lengthSq !== 0) {
          const t = ((clickX - drawing.data.x1) * dx + (clickY - drawing.data.y1) * dy) / lengthSq;
          const clampedT = Math.max(0, Math.min(1, t));
          const closestX = drawing.data.x1 + clampedT * dx;
          const closestY = drawing.data.y1 + clampedT * dy;
          distance = Math.sqrt(Math.pow(clickX - closestX, 2) + Math.pow(clickY - closestY, 2));
          if (distance < 5) { // Adjust tolerance as needed
            emit('clearDrawing', i);
            break;
          }
        } else {
          // If it's a very short line (or a point), just check distance to the start point
          distance = Math.sqrt(Math.pow(clickX - drawing.data.x1, 2) + Math.pow(clickY - drawing.data.y1, 2));
          if (distance < 5) {
            emit('clearDrawing', i);
            break;
          }
        }
      } else if (drawing.type === 'dot') {
        const dotRadius = 3 * 1.5; // Match the drawing size
        distance = Math.sqrt(Math.pow(clickX - drawing.data.x, 2) + Math.pow(clickY - drawing.data.y, 2));
        if (distance < dotRadius + 2) {
          emit('clearDrawing', i);
          break;
        }
      }
    }
  }
};

const redrawCanvas = () => {
  if (!canvas__map.value || !props.imageName) return;
  const canvas = canvas__map.value;
  const context = canvas.getContext('2d');

  const image = new Image();
  image.onload = () => {
    context.clearRect(0, 0, canvas.width, canvas.height);
    context.drawImage(image, 0, 0, canvas.width, canvas.height);
    props.drawings.forEach(drawing => {
      drawShape(context, drawing);
    });
  };
  image.src = props.imageName;
};

onMounted(() => {
  const canvas = canvas__map.value;
  ctx.value = canvas.getContext('2d');

  canvas.addEventListener('mousedown', handleMouseDown);
  canvas.addEventListener('mousemove', handleMouseMove);
  canvas.addEventListener('mouseup', handleMouseUp);
  canvas.addEventListener('click', handleCanvasClick); // For eraser

  loadImage();
});

watch(() => props.imageName, () => {
  loadImage();
});

watch(props.drawings, () => {
  redrawCanvas();
}, { deep: true });

const loadImage = () => {
  if (!props.imageName || !canvas__map.value) return;

  const canvas = canvas__map.value;
  const context = canvas.getContext('2d');
  const image = new Image();

  image.onload = () => {
    canvas.width = image.width;
    canvas.height = image.height;
    canvasWidth.value = image.width;
    canvasHeight.value = image.height;
    context.drawImage(image, 0, 0);
  };

  image.onerror = () => {
    console.error(`Failed to load image: ${props.imageName}`);
  };

  image.src = props.imageName;
};

defineExpose({
  setDrawingMode,
  redrawCanvas
});
</script>

<style scoped>
canvas {
  border: 1px solid black;
  cursor: crosshair;
}
</style>