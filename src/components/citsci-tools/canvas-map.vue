<template>
  <div style="position: relative; display: inline-block;">
    <canvas ref="canvas__map" id="canvas__map"></canvas>
    <canvas
        ref="annotationCanvas"
        style="position: absolute; top: 0; left: 0; cursor: crosshair;"
        :width="canvasWidth"
        :height="canvasHeight"
        @mousedown="handleMouseDown"
        @mousemove="handleMouseMove"
        @mouseup="handleMouseUp"
        @click="handleCanvasClick"
    ></canvas>
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

const canvas__map = ref(null); // For the background image
const annotationCanvas = ref(null); // For the drawings
const canvasWidth = ref(0);
const canvasHeight = ref(0);
const bgCtx = ref(null); // Context for the background canvas
const annCtx = ref(null); // Context for the annotation canvas
const isDrawing = ref(false);
const startPoint = ref(null);
const currentDrawing = ref(null);
const minsize = 25;

const setDrawingMode = (newMode) => {
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

  // Clear the annotation canvas for the temporary drawing
  annCtx.value.clearRect(0, 0, canvasWidth.value, canvasHeight.value);

  // Redraw existing drawings on the annotation canvas
  props.drawings.forEach(existingDrawing => {
    drawShape(annCtx.value, existingDrawing);
  });

  // Draw the current in-progress drawing on the annotation canvas
  drawShape(annCtx.value, { ...currentDrawing.value, data: getCurrentShapeData(currentX, currentY) });
};

const handleMouseUp = (event) => {
  if (!isDrawing.value || !currentDrawing.value) return;
  isDrawing.value = false;

  // Start by clearing the screen
  annCtx.value.clearRect(0, 0, canvasWidth.value, canvasHeight.value);

  // Draw the good stuff
  redrawAnnotations();

  const endPoint = { x: event.offsetX, y: event.offsetY };

  // let's verify the mark is large enough to be saved
  if (props.mode === 'line') {
    const dx = endPoint.x - startPoint.value.x;
    const dy = endPoint.y - startPoint.value.y;
    const length = Math.sqrt(dx * dx + dy * dy);
    if (length <= minsize) return; // Ignore small lines
  } else if (props.mode === 'circle') {
    const dx = endPoint.x - startPoint.value.x;
    const dy = endPoint.y - startPoint.value.y;
  }

  currentDrawing.value.data = getCurrentShapeData(endPoint.x, endPoint.y);
  emit('draw', currentDrawing.value);
  currentDrawing.value = null;

  // Redraw all drawings on the annotation canvas to finalize

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
  context.lineWidth = 2; // Default line width
  context.fillStyle = 'rgba(130,83, 31, 0.2)'; // Default fill

  if (drawing.type === 'circle') {
    // Set color based on size
    if (drawing.data.radius < 10) {
      context.setLineDash([3, 3]); // Dashed line
      context.fillStyle = 'rgba(255, 0, 0, 0.2)';
      context.strokeStyle = 'red';
    } else {
      context.fillStyle = 'rgba(197, 131, 54, 0.2)'; // Yellow color
      context.strokeStyle = '#c58336';
    }

    context.beginPath();
    context.arc(drawing.data.x, drawing.data.y, drawing.data.radius, 0, 2 * Math.PI);
    context.stroke();
    context.fill();
    context.setLineDash([]); // Reset to solid line


  } else if (drawing.type === 'line') {
    context.lineWidth = 4;
    context.strokeStyle = 'white';
    // Draw the white outline
    context.beginPath();
    context.moveTo(drawing.data.x1, drawing.data.y1);
    context.lineTo(drawing.data.x2, drawing.data.y2);
    context.stroke();

    // Draw the central color
    context.lineWidth = 2;

    // if the line is short, make it dashed & red
    let lineLen;
    lineLen = Math.sqrt(
        Math.pow(drawing.data.x2 - drawing.data.x1, 2) +
        Math.pow(drawing.data.y2 - drawing.data.y1, 2)
    );
    if (lineLen < 20) {
      context.setLineDash([3, 3]); // Dashed line
      context.strokeStyle = 'red'; // Red color
    } else {
      context.setLineDash([]); // Solid line
      context.strokeStyle = '#6f6e2a'; // Green color
    }

    context.beginPath();
    context.moveTo(drawing.data.x1, drawing.data.y1);
    context.lineTo(drawing.data.x2, drawing.data.y2);
    context.stroke();

    // Reset line for subsequent drawings
    context.setLineDash([]);
    context.lineWidth = 4;
  } else if (drawing.type === 'dot') {
    const dotRadius = 5;
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
        const dx = drawing.data.x2 - drawing.data.x1;
        const dy = drawing.data.y2 - drawing.data.y1;
        const lengthSq = dx * dx + dy * dy;
        if (lengthSq !== 0) {
          const t = ((clickX - drawing.data.x1) * dx + (clickY - drawing.data.y1) * dy) / lengthSq;
          const clampedT = Math.max(0, Math.min(1, t));
          const closestX = drawing.data.x1 + clampedT * dx;
          const closestY = drawing.data.y1 + clampedT * dy;
          distance = Math.sqrt(Math.pow(clickX - closestX, 2) + Math.pow(clickY - closestY, 2));
          if (distance < 5) {
            emit('clearDrawing', i);
            break;
          }
        } else {
          distance = Math.sqrt(Math.pow(clickX - drawing.data.x1, 2) + Math.pow(clickY - drawing.data.y1, 2));
          if (distance < 20) {
            emit('clearDrawing', i);
            break;
          }
        }
      } else if (drawing.type === 'dot') {
        const dotRadius = 5;
        distance = Math.sqrt(Math.pow(clickX - drawing.data.x, 2) + Math.pow(clickY - drawing.data.y, 2));
        if (distance < dotRadius + 2) {
          emit('clearDrawing', i);
          break;
        }
      }
    }
  }
};

const redrawAnnotations = () => {
  if (!annotationCanvas.value) return;
  const context = annotationCanvas.value.getContext('2d');
  context.clearRect(0, 0, canvasWidth.value, canvasHeight.value);
  props.drawings.forEach(drawing => {
    drawShape(context, drawing);
  });
};

onMounted(() => {
  const bgCanvas = canvas__map.value;
  bgCtx.value = bgCanvas.getContext('2d');
  const annCanvas = annotationCanvas.value;
  annCtx.value = annCanvas.getContext('2d');

  loadImage();
});

watch(() => props.imageName, () => {
  loadImage();
});

watch(props.drawings, () => {
  redrawAnnotations();
}, { deep: true });

const loadImage = () => {
  if (!props.imageName || !canvas__map.value) return;

  const bgCanvas = canvas__map.value;
  const image = new Image();

  image.onload = () => {
    canvasWidth.value = image.width;
    canvasHeight.value = image.height;
    bgCanvas.width = image.width;
    bgCanvas.height = image.height;
    annotationCanvas.value.width = image.width;
    annotationCanvas.value.height = image.height;
    bgCtx.value.drawImage(image, 0, 0);
    redrawAnnotations(); // Redraw any existing annotations if the image changes
  };

  image.onerror = () => {
    console.error(`Failed to load image: ${props.imageName}`);
  };

  image.src = props.imageName;
};

defineExpose({
  setDrawingMode,
  redrawCanvas: redrawAnnotations // Expose the annotation redraw function
});
</script>
