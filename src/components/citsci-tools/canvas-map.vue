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
const MINSIZE = 25;

const setDrawingMode = (newMode) => {
  isDrawing.value = false;
  startPoint.value = null;
  currentDrawing.value = null;
  // If changing from a drawing mode, ensure temporary artifacts are cleared
  // by redrawing existing annotations.
  if (annCtx.value) {
    redrawAnnotations();
  }
};

const handleMouseDown = (event) => {
  if (!props.mode || props.mode === 'erase' || props.mode === 'dot') return;
  isDrawing.value = true;
  startPoint.value = { x: event.offsetX, y: event.offsetY };
  currentDrawing.value = { type: props.mode, data: {} };
};

const handleMouseMove = (event) => {
  if (!isDrawing.value || !currentDrawing.value || !annCtx.value || props.mode === 'dot' || props.mode === 'erase') return;

  const currentX = event.offsetX;
  const currentY = event.offsetY;

  // Clear the annotation canvas for the temporary drawing
  annCtx.value.clearRect(0, 0, canvasWidth.value, canvasHeight.value);

  // Redraw existing **prior** drawings on the annotation canvas
  if (props.drawings) {
    props.drawings.forEach(existingDrawing => {
      drawShape(annCtx.value, existingDrawing);
    });
  }

  // Draw the current in-progress (temporary) drawing
  const tempData = getCurrentShapeData(currentX, currentY); // Get data for the current shape
  if (currentDrawing.value && currentDrawing.value.type) { // Check if currentDrawing and its type exist
    drawShape(annCtx.value, { type: currentDrawing.value.type, data: tempData });
  }
};

const handleMouseUp = (event) => {
  // If not drawing, or if mode is 'dot' or 'erase' (handled by click), do nothing here.
  if (!isDrawing.value || !currentDrawing.value || props.mode === 'dot' || props.mode === 'erase') {
    isDrawing.value = false; // Ensure isDrawing is reset if active
    return;
  }

  const endPoint = { x: event.offsetX, y: event.offsetY };
  const drawingData = getCurrentShapeData(endPoint.x, endPoint.y);

  // let's verify the mark is large enough to be saved
  let isValidDrawing = true;
  if (props.mode === 'line' || props.mode === 'circle') {
    const dx = endPoint.x - startPoint.value.x;
    const dy = endPoint.y - startPoint.value.y;
    const length = Math.sqrt(dx * dx + dy * dy);
    if (props.mode === 'line' && length <= MINESIZE) {
      isValidDrawing = false;
      console.log('line too small');
    } // Ignore small lines
    else if (props.mode === 'circle' && length <= 0.5 * MINSIZE) {
      isValidDrawing = false;
      console.log('circle too small');
    } // Ignore small circles
  }

  if (!isValidDrawing) {
    isDrawing.value = false;
    startPoint.value = null;
    currentDrawing.value = null;
    // Clear the invalid temporary drawing by redrawing only committed annotations
    redrawAnnotations();
    return;
  }

  // Finalize the drawing object
  const finalDrawing = {
    type: props.mode, // Use props.mode which should be currentDrawing.value.type
    data: drawingData
  };

  // Emit the new drawing. The parent updates `props.drawings`.
  // The watcher on `props.drawings` will then call `redrawAnnotations()`.
  emit('draw', finalDrawing);

  // Reset drawing state
  isDrawing.value = false;
  startPoint.value = null;
  currentDrawing.value = null;
};

const handleCanvasClick = (event) => {
  const clickX = event.offsetX;
  const clickY = event.offsetY;

  if (props.mode === 'dot') {
    const dotDrawing = {
      type: 'dot',
      data: { x: clickX, y: clickY } // Use clickX, clickY directly
    };
    emit('draw', dotDrawing);
    // The watcher on props.drawings will handle the redraw.
    return; // Prevent further processing if it was a dot click
  }

  if (props.mode === 'erase') {
    if (!props.drawings || props.drawings.length === 0) return;

    for (let i = props.drawings.length - 1; i >= 0; i--) {
      const drawing = props.drawings[i];
      let distance; // Default to a value that won't match unless explicitly hit
      let hit = false;

      if (drawing.type === 'circle') {
        distance = Math.sqrt(
            Math.pow(clickX - drawing.data.x, 2) + Math.pow(clickY - drawing.data.y, 2)
        );
        if (distance < drawing.data.radius + 5) { // Add a small buffer for easier clicking
          hit = true;
        }
      } else if (drawing.type === 'line') {
        const { x1, y1, x2, y2 } = drawing.data;
        const dxL = x2 - x1;
        const dyL = y2 - y1;
        const lineLengthSq = dxL * dxL + dyL * dyL;

        if (lineLengthSq === 0) { // It's a point
          distance = Math.sqrt(Math.pow(clickX - x1, 2) + Math.pow(clickY - y1, 2));
          if (distance < 5) hit = true; // Click tolerance for a point-like line
        } else {
          const t = ((clickX - x1) * dxL + (clickY - y1) * dyL) / lineLengthSq;
          const clampedT = Math.max(0, Math.min(1, t));
          const closestX = x1 + clampedT * dxL;
          const closestY = y1 + clampedT * dyL;
          distance = Math.sqrt(Math.pow(clickX - closestX, 2) + Math.pow(clickY - closestY, 2));
          if (distance < 5) { // Click tolerance for line
            hit = true;
          }
        }
      } else if (drawing.type === 'dot') {
        const dotRadius = 5; // As defined in drawShape
        distance = Math.sqrt(Math.pow(clickX - drawing.data.x, 2) + Math.pow(clickY - drawing.data.y, 2));
        if (distance < dotRadius + 2) { // Click tolerance for dot
          hit = true;
        }
      }

      if (hit) {
        emit('clearDrawing', i); // Parent will update props.drawings
                                 // Watcher will redraw.
        break; // Found and emitted, no need to check others
      }
    }
  }
};

const getCurrentShapeData = (x2, y2) => {
  // This is primarily for line and circle during mouseMove/mouseUp
  if (props.mode === 'circle' && startPoint.value) {
    const dx = x2 - startPoint.value.x;
    const dy = y2 - startPoint.value.y;
    const radius = Math.sqrt(dx * dx + dy * dy);
    return { x: startPoint.value.x, y: startPoint.value.y, radius };
  } else if (props.mode === 'line' && startPoint.value) {
    return { x1: startPoint.value.x, y1: startPoint.value.y, x2, y2 };
  }
  // 'dot' data is created directly in handleCanvasClick
  return {};
};

const drawShape = (context, drawing) => {
  if (!drawing || !drawing.type || !drawing.data) {
    console.warn('Attempted to draw invalid shape:', drawing);
    return;
  }

  context.lineWidth = 2; // Default line width
  context.setLineDash([]); // Default to solid line
  context.fillStyle = 'rgba(130,83, 31, 0.2)'; // Default fill

  if (drawing.type === 'circle') {
    //set default styles
    context.fillStyle = 'rgba(197, 131, 54, 0.2)'; // Yellow color
    context.strokeStyle = '#c58336'; // Yellow outline
    context.lineWidth = 2;

    // if the circle is small, make it dashed & red
    if (drawing.data.radius < MINSIZE * 0.5) {
      context.setLineDash([3, 3]); // Dashed line
      context.fillStyle = 'rgba(255, 0, 0, 0.2)';
      context.strokeStyle = 'red';
    }

    context.beginPath();
    context.arc(drawing.data.x, drawing.data.y, drawing.data.radius, 0, 2 * Math.PI);
    context.stroke();
    context.fill();
    context.setLineDash([]); // Reset to solid line


  } else if (drawing.type === 'line') {
    //Draw the White Outline
    context.lineWidth = 4;
    context.strokeStyle = 'white';
    context.beginPath();
    context.moveTo(drawing.data.x1, drawing.data.y1);
    context.lineTo(drawing.data.x2, drawing.data.y2);
    context.stroke();

    // Draw the central color
    context.lineWidth = 2;

    // But first check if the line is short
    const lineLen = Math.sqrt(
        Math.pow(drawing.data.x2 - drawing.data.x1, 2) +
        Math.pow(drawing.data.y2 - drawing.data.y1, 2)
    );

    if (lineLen < MINSIZE) {
      context.setLineDash([3, 3]); // Dashed line
      context.strokeStyle = 'red'; // Red color
    } else {
      context.strokeStyle = '#6f6e2a'; // Green color
    }

    context.beginPath();
    context.moveTo(drawing.data.x1, drawing.data.y1);
    context.lineTo(drawing.data.x2, drawing.data.y2);
    context.stroke();
    context.setLineDash([]); // Reset dash
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


const redrawAnnotations = () => {
  if (!annCtx.value || !annotationCanvas.value) return;
  annCtx.value.clearRect(0, 0, canvasWidth.value, canvasHeight.value);
  if (props.drawings) {
    props.drawings.forEach(drawing => {
      drawShape(annCtx.value, drawing);
    });
  }
};

onMounted(() => {
  const bgCanvas = canvas__map.value;
  if (bgCanvas) {
    bgCtx.value = bgCanvas.getContext('2d');
  }
  const annCanvas = annotationCanvas.value;
  if (annCanvas) {
    annCtx.value = annCanvas.getContext('2d');
  }
  loadImage();
});

watch(() => props.imageName, (newVal, oldVal) => {
  if (newVal !== oldVal) { // Ensure it actually changed
    loadImage();
  }
});

watch(() => props.drawings, (newDrawings, oldDrawings) => {
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
