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
import { ref, onMounted, watch, defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
  imageName: String,
  mode: String,
  drawings: Array,
});

const emit = defineEmits(['draw', 'clearDrawing', 'updateDrawing']); // Added 'updateDrawing'

const canvas__map = ref(null);
const annotationCanvas = ref(null);
const canvasWidth = ref(0);
const canvasHeight = ref(0);
const bgCtx = ref(null);
const annCtx = ref(null);
const isDrawing = ref(false); // For creating new shapes
const startPoint = ref(null);
const currentDrawing = ref(null);
const MINSIZE = 25; // Corrected typo from MINESIZE

// --- New state for editing ---
const selectedShapeIndex = ref(-1);
const isEditing = ref(false); // True if dragging or resizing an existing shape
const editHandle = ref(null); // e.g., 'body', 'radius', 'p1', 'p2'
const dragStartCoords = ref({ x: 0, y: 0 }); // Mouse position at mousedown for edit
const originalShapeData = ref(null); // To store shape data at the start of an edit operation

const HANDLE_SIZE = 8; // Size of resize handles
const HANDLE_COLOR = 'rgba(0, 100, 255, 0.8)';
const SELECTION_COLOR = 'rgba(0, 100, 255, 0.5)';

const canvasCursor = computed(() => {
  if (props.mode === 'edit') {
    // Logic to change cursor over shapes/handles can be added here later
    // For now, a general 'grab' or 'pointer' might be fine.
    // This requires checking mouse position against shapes constantly,
    // which can be complex for performance. Start with a default.
    return 'default'; // Or 'pointer'
  }
  return 'crosshair';
});


const setDrawingMode = (newMode) => {
  isDrawing.value = false;
  startPoint.value = null;
  currentDrawing.value = null;

  if (newMode !== 'edit') {
    selectedShapeIndex.value = -1;
    isEditing.value = false;
    editHandle.value = null;
  }
  if (annCtx.value) {
    redrawAnnotations();
  }
};

// --- Helper functions for hit detection ---
function isPointInRect(px, py, rx, ry, rw, rh) {
  return px >= rx && px <= rx + rw && py >= ry && py <= ry + rh;
}

function distance(p1, p2) {
  return Math.sqrt((p1.x - p2.x) ** 2 + (p1.y - p2.y) ** 2);
}

function getShapeAtPoint(x, y) {
  if (!props.drawings) return { index: -1, handle: null };

  for (let i = props.drawings.length - 1; i >= 0; i--) {
    const drawing = props.drawings[i];
    const data = drawing.data;

    // Priority: Check handles first if a shape is already selected and it's this one
    if (i === selectedShapeIndex.value) {
      if (drawing.type === 'circle') {
        const handleX = data.x + data.radius;
        const handleY = data.y;
        if (isPointInRect(x, y, handleX - HANDLE_SIZE / 2, handleY - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE)) {
          return { index: i, handle: 'radius' };
        }
      } else if (drawing.type === 'line') {
        if (isPointInRect(x, y, data.x1 - HANDLE_SIZE / 2, data.y1 - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE)) {
          return { index: i, handle: 'p1' };
        }
        if (isPointInRect(x, y, data.x2 - HANDLE_SIZE / 2, data.y2 - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE)) {
          return { index: i, handle: 'p2' };
        }
      }
    }

    // Check shape body
    if (drawing.type === 'circle') {
      if (distance({ x, y }, { x: data.x, y: data.y }) < data.radius + HANDLE_SIZE / 2) { // Slightly larger hit area
        return { index: i, handle: 'body' };
      }
    } else if (drawing.type === 'line') {
      const { x1, y1, x2, y2 } = data;
      const distToLine = pointToLineSegmentDistance(x, y, x1, y1, x2, y2);
      if (distToLine < HANDLE_SIZE) { // Tolerance for line selection
        return { index: i, handle: 'body' };
      }
    } else if (drawing.type === 'dot') {
      if (distance({ x, y }, { x: data.x, y: data.y }) < 5 + HANDLE_SIZE / 2) { // 5 is dot radius
        return { index: i, handle: 'body' };
      }
    }
  }
  return { index: -1, handle: null }; // No shape found
}

function pointToLineSegmentDistance(px, py, x1, y1, x2, y2) {
  const l2 = (x2 - x1) * (x2 - x1) + (y2 - y1) * (y2 - y1);
  if (l2 === 0) return distance({ x: px, y: py }, { x: x1, y: y1 });
  let t = ((px - x1) * (x2 - x1) + (py - y1) * (y2 - y1)) / l2;
  t = Math.max(0, Math.min(1, t));
  const closestX = x1 + t * (x2 - x1);
  const closestY = y1 + t * (y2 - y1);
  return distance({ x: px, y: py }, { x: closestX, y: closestY });
}


const handleMouseDown = (event) => {
  const mouseX = event.offsetX;
  const mouseY = event.offsetY;

  if (props.mode === 'red-line') {
    // Do nothing on mousedown for two-click logic
    return;
  }

  if (props.mode === 'edit') {
    const { index, handle } = getShapeAtPoint(mouseX, mouseY);
    if (index !== -1) {
      selectedShapeIndex.value = index;
      isEditing.value = true;
      editHandle.value = handle;
      dragStartCoords.value = { x: mouseX, y: mouseY };
      originalShapeData.value = JSON.parse(JSON.stringify(props.drawings[index].data));
      // redrawAnnotations(); // Redraw will happen due to selectedShapeIndex change if watcher is set up for it or at end of function
    } else {
      // Clicked on empty space
      if (selectedShapeIndex.value !== -1) { // If something was previously selected
        // Deselect
      }
      selectedShapeIndex.value = -1;
      isEditing.value = false; // Not starting an edit
      editHandle.value = null;
      originalShapeData.value = null; // Clear original shape data on deselect
    }
    redrawAnnotations(); // Call redraw after any selection change
    return;
  }

  // Original drawing mode logic
  if (!props.mode || props.mode === 'erase' || props.mode === 'dot') return;
  // --- Begin red-line mode logic ---
  if (props.mode === 'red-line') {
    isDrawing.value = true;
    startPoint.value = { x: mouseX, y: mouseY };
    currentDrawing.value = { type: 'red-line', data: {} };
    return;
  }
  // --- End red-line mode logic ---
  isDrawing.value = true;
  startPoint.value = { x: mouseX, y: mouseY };
  currentDrawing.value = { type: props.mode, data: {} };
};

const handleMouseMove = (event) => {
  if (props.mode === 'red-line') {
    if (!isDrawing.value || !startPoint.value || !annCtx.value) return;
    const mouseX = event.offsetX;
    const mouseY = event.offsetY;
    annCtx.value.clearRect(0, 0, canvasWidth.value, canvasHeight.value);
    if (props.drawings) {
      props.drawings.forEach(existingDrawing => {
        drawShape(annCtx.value, existingDrawing, -1);
      });
    }
    // Live preview
    drawShape(annCtx.value, { type: 'red-line', data: { x1: startPoint.value.x, y1: startPoint.value.y, x2: mouseX, y2: mouseY }, color: 'red' }, -1);
    return;
  }
  const mouseX = event.offsetX;
  const mouseY = event.offsetY;

  if (props.mode === 'edit' && isEditing.value && selectedShapeIndex.value !== -1) {
    const dx = mouseX - dragStartCoords.value.x;
    const dy = mouseY - dragStartCoords.value.y;
    // Make sure originalShapeData is a fresh copy for calculations
    let currentData = JSON.parse(JSON.stringify(originalShapeData.value));

    const selectedDrawing = props.drawings[selectedShapeIndex.value];

    if (editHandle.value === 'body') {
      if (selectedDrawing.type === 'circle' || selectedDrawing.type === 'dot') {
        currentData.x += dx;
        currentData.y += dy;
      } else if (selectedDrawing.type === 'line') {
        currentData.x1 += dx;
        currentData.y1 += dy;
        currentData.x2 += dx;
        currentData.y2 += dy;
      }
    } else if (editHandle.value === 'radius' && selectedDrawing.type === 'circle') {
      const newRadius = distance({ x: currentData.x, y: currentData.y }, { x: mouseX, y: mouseY });
      currentData.radius = Math.max(1, newRadius);
    } else if (editHandle.value === 'p1' && selectedDrawing.type === 'line') {
      currentData.x1 = mouseX;
      currentData.y1 = mouseY;
    } else if (editHandle.value === 'p2' && selectedDrawing.type === 'line') {
      currentData.x2 = mouseX;
      currentData.y2 = mouseY;
    }

    const tempUpdatedDrawing = { ...selectedDrawing, data: currentData };

    if (annCtx.value) {
      annCtx.value.clearRect(0, 0, canvasWidth.value, canvasHeight.value);
      props.drawings.forEach((drawing, index) => {
        if (index === selectedShapeIndex.value) {
          drawShape(annCtx.value, tempUpdatedDrawing, index);
        } else {
          drawShape(annCtx.value, drawing, index);
        }
      });
    }
    return;
  }

  const currentX = event.offsetX;
  const currentY = event.offsetY;

  if (!isDrawing.value || !currentDrawing.value || !annCtx.value || props.mode === 'dot' || props.mode === 'erase') return;

  annCtx.value.clearRect(0, 0, canvasWidth.value, canvasHeight.value);
  if (props.drawings) {
    props.drawings.forEach(existingDrawing => {
      drawShape(annCtx.value, existingDrawing, -1);
    });
  }
  const tempData = getCurrentShapeData(currentX, currentY);
  if (currentDrawing.value && currentDrawing.value.type) {
    // --- Begin red-line live preview ---
    if (props.mode === 'red-line') {
      drawShape(annCtx.value, { type: 'red-line', data: tempData }, -1);
    } else {
      drawShape(annCtx.value, { type: currentDrawing.value.type, data: tempData }, -1);
    }
    // --- End red-line live preview ---
  }
};

const handleMouseUp = (event) => {
  if (props.mode === 'red-line') {
    // Do nothing on mouseup for two-click logic
    return;
  }
  if (props.mode === 'edit' && isEditing.value && selectedShapeIndex.value !== -1) {
    const mouseX = event.offsetX;
    const mouseY = event.offsetY;
    const dx = mouseX - dragStartCoords.value.x;
    const dy = mouseY - dragStartCoords.value.y;

    let finalData = JSON.parse(JSON.stringify(originalShapeData.value));
    const selectedType = props.drawings[selectedShapeIndex.value].type;

    if (editHandle.value === 'body') {
      if (selectedType === 'circle' || selectedType === 'dot') {
        finalData.x += dx;
        finalData.y += dy;
      } else if (selectedType === 'line') {
        finalData.x1 += dx;
        finalData.y1 += dy;
        finalData.x2 += dx;
        finalData.y2 += dy;
      }
    } else if (editHandle.value === 'radius' && selectedType === 'circle') {
      const newRadius = distance({ x: finalData.x, y: finalData.y }, { x: mouseX, y: mouseY });
      finalData.radius = Math.max(1, newRadius);
    } else if (editHandle.value === 'p1' && selectedType === 'line') {
      finalData.x1 = mouseX;
      finalData.y1 = mouseY;
    } else if (editHandle.value === 'p2' && selectedType === 'line') {
      finalData.x2 = mouseX;
      finalData.y2 = mouseY;
    }

    let isValidEdit = true;
    if (selectedType === 'line') {
      const length = distance({ x: finalData.x1, y: finalData.y1 }, { x: finalData.x2, y: finalData.y2 });
      if (length < MINSIZE) isValidEdit = false;
    } else if (selectedType === 'circle') {
      if (finalData.radius < MINSIZE * 0.5) isValidEdit = false;
    }

    if (isValidEdit) {
      emit('updateDrawing', {
        index: selectedShapeIndex.value,
        newDrawing: { type: selectedType, data: finalData }
      });
    } else {
      console.log('Edit resulted in shape too small, not emitting update.');
      redrawAnnotations(); // Revert visual preview if edit was invalid
    }

    isEditing.value = false;
    originalShapeData.value = null;
    return;
  }

  if (!isDrawing.value || !currentDrawing.value || props.mode === 'dot' || props.mode === 'erase') {
    isDrawing.value = false;
    return;
  }
  const endPoint = { x: event.offsetX, y: event.offsetY };
  const drawingData = getCurrentShapeData(endPoint.x, endPoint.y);

  let isValidDrawing = true;
  // --- Begin red-line validation ---
  if (props.mode === 'red-line') {
    const dx_draw = endPoint.x - startPoint.value.x;
    const dy_draw = endPoint.y - startPoint.value.y;
    const length = Math.sqrt(dx_draw * dx_draw + dy_draw * dy_draw);
    if (length <= MINSIZE) {
      isValidDrawing = false;
      console.log('red-line too small');
    }
  }
  // --- End red-line validation ---
  if (props.mode === 'line' || props.mode === 'circle') {
    const dx_draw = endPoint.x - startPoint.value.x;
    const dy_draw = endPoint.y - startPoint.value.y;
    const length = Math.sqrt(dx_draw * dx_draw + dy_draw * dy_draw);
    if (props.mode === 'line' && length <= MINSIZE) { // Corrected typo
      isValidDrawing = false;
      console.log('line too small');
    }
    else if (props.mode === 'circle' && length <= 0.5 * MINSIZE) { // Corrected typo
      isValidDrawing = false;
      console.log('circle too small');
    }
  }

  if (!isValidDrawing) {
    isDrawing.value = false;
    startPoint.value = null;
    currentDrawing.value = null;
    redrawAnnotations();
    return;
  }
  // --- Begin red-line finalize ---
  if (props.mode === 'red-line') {
    const finalDrawing = { type: 'red-line', data: drawingData, color: 'red' };
    emit('draw', finalDrawing);
    isDrawing.value = false;
    startPoint.value = null;
    currentDrawing.value = null;
    return;
  }
  // --- End red-line finalize ---
  const finalDrawing = { type: props.mode, data: drawingData };
  emit('draw', finalDrawing);
  isDrawing.value = false;
  startPoint.value = null;
  currentDrawing.value = null;
};




const handleCanvasClick = (event) => {
  if (props.mode === 'red-line') {
    const mouseX = event.offsetX;
    const mouseY = event.offsetY;
    if (!isDrawing.value) {
      // First click: set start point
      startPoint.value = { x: mouseX, y: mouseY };
      isDrawing.value = true;
      // Show preview on next mousemove
    } else {
      // Second click: finalize line
      const endPoint = { x: mouseX, y: mouseY };
      const dx = endPoint.x - startPoint.value.x;
      const dy = endPoint.y - startPoint.value.y;
      const length = Math.sqrt(dx * dx + dy * dy);
      if (length > MINSIZE) {
        const finalDrawing = { type: 'red-line', data: { x1: startPoint.value.x, y1: startPoint.value.y, x2: endPoint.x, y2: endPoint.y }, color: 'red' };
        emit('draw', finalDrawing);
      }
      isDrawing.value = false;
      startPoint.value = null;
      annCtx.value && annCtx.value.clearRect(0, 0, canvasWidth.value, canvasHeight.value);
      if (props.drawings) {
        props.drawings.forEach(existingDrawing => {
          drawShape(annCtx.value, existingDrawing, -1);
        });
      }
    }
    return;
  }
  const clickX = event.offsetX;
  const clickY = event.offsetY;

  if (props.mode === 'edit') {
    // Selection is handled by mousedown. Click might be used to deselect if no shape is hit.
    // If mousedown selected a shape, this click might be redundant unless it's for a different purpose.
    // If getShapeAtPoint in mousedown finds nothing, selectedShapeIndex is already -1.
    return;
  }

  if (props.mode === 'dot') {
    // ... (original dot logic)
    const dotDrawing = { type: 'dot', data: { x: clickX, y: clickY }};
    emit('draw', dotDrawing);
    return;
  }

  if (props.mode === 'erase') {
    // ... (original erase logic)
    if (!props.drawings || props.drawings.length === 0) return;
    // Consider using getShapeAtPoint for more consistent hit detection if desired
    for (let i = props.drawings.length - 1; i >= 0; i--) {
      const drawing = props.drawings[i];
      let hit = false;
      if (drawing.type === 'circle') {
        if (distance({x: clickX, y: clickY}, drawing.data) < drawing.data.radius + 5) hit = true;
      } else if (drawing.type === 'line') {
        if (pointToLineSegmentDistance(clickX, clickY, drawing.data.x1, drawing.data.y1, drawing.data.x2, drawing.data.y2) < 5) hit = true;
      } else if (drawing.type === 'dot') {
        if (distance({x: clickX, y: clickY}, drawing.data) < 5 + 2) hit = true; // 5 is dot radius
      }
      if (hit) {
        emit('clearDrawing', i);
        break;
      }
    }
  }
};

const getCurrentShapeData = (x2, y2) => {
  // ... (original logic)
  if (props.mode === 'circle' && startPoint.value) {
    const dx = x2 - startPoint.value.x;
    const dy = y2 - startPoint.value.y;
    const radius = Math.sqrt(dx * dx + dy * dy);
    return { x: startPoint.value.x, y: startPoint.value.y, radius };
  } else if (props.mode === 'line' && startPoint.value) {
    return { x1: startPoint.value.x, y1: startPoint.value.y, x2, y2 };
  }
  return {};
};

// Updated drawShape to include index for selection and handles
const drawShape = (context, drawing, index) => {
  if (!drawing || !drawing.type || !drawing.data) {
    // console.warn('Attempted to draw invalid shape:', drawing); // Can be noisy
    return;
  }

  context.save(); // Save context state before drawing individual shape

  // --- Original drawing logic ---
  context.lineWidth = 2;
  context.setLineDash([]);
  context.fillStyle = 'rgba(130,83, 31, 0.2)';

  if (drawing.type === 'circle') {
    context.fillStyle = 'rgba(197, 131, 54, 0.2)';
    context.strokeStyle = '#c58336';
    context.lineWidth = 2;
    if (drawing.data.radius < MINSIZE * 0.5 && props.mode !== 'edit') { // Don't show as invalid if being edited
      context.setLineDash([3, 3]);
      context.fillStyle = 'rgba(255, 0, 0, 0.2)';
      context.strokeStyle = 'red';
    }
    context.beginPath();
    context.arc(drawing.data.x, drawing.data.y, drawing.data.radius, 0, 2 * Math.PI);
    context.stroke();
    context.fill();
  } else if (drawing.type === 'line' || drawing.type === 'red-line') {
    // --- Begin red-line rendering ---
    const isRed = drawing.type === 'red-line' || drawing.color === 'red';
    context.strokeStyle = isRed ? 'red' : 'white'; // Outer stroke
    context.lineWidth = 4; // Outer stroke width
    context.beginPath();
    context.moveTo(drawing.data.x1, drawing.data.y1);
    context.lineTo(drawing.data.x2, drawing.data.y2);
    context.stroke();

    // Inner stroke
    const lineLen = distance({x: drawing.data.x1, y: drawing.data.y1}, {x: drawing.data.x2, y: drawing.data.y2});
    if (lineLen < MINSIZE && props.mode !== 'edit') { // Don't show as invalid if being edited
      context.strokeStyle = 'red';
      context.setLineDash([3, 3]);
    } else {
      context.strokeStyle = isRed ? 'red' : '#6f6e2a'; // Red for red-line, green for normal line
    }
    context.lineWidth = 2; // Inner stroke width
    context.beginPath();
    context.moveTo(drawing.data.x1, drawing.data.y1);
    context.lineTo(drawing.data.x2, drawing.data.y2);
    context.stroke();
    // --- End red-line rendering ---

  } else if (drawing.type === 'dot') {
    const dotRadius = 5;
    context.fillStyle = '#29336c';
    context.strokeStyle = 'white';
    context.lineWidth = 1;
    context.beginPath();
    context.arc(drawing.data.x, drawing.data.y, dotRadius, 0, 2 * Math.PI);
    context.fill();
    context.stroke();
  }
  context.restore(); // Restore general drawing settings

  // --- Draw selection and handles if in edit mode and this shape is selected ---
  if (props.mode === 'edit' && index === selectedShapeIndex.value) {
    context.save();
    // General selection highlight (e.g., a slightly different border or glow)
    // For simplicity, let's make the main stroke color more prominent or add an outer box
    context.strokeStyle = SELECTION_COLOR;
    context.lineWidth = 2; // A thicker or different color outline for selected

    if (drawing.type === 'circle') {
      context.beginPath();
      context.arc(drawing.data.x, drawing.data.y, drawing.data.radius, 0, 2 * Math.PI);
      context.stroke(); // Re-stroke with selection color or add another arc slightly larger

      // Draw radius handle
      const handleX = drawing.data.x + drawing.data.radius;
      const handleY = drawing.data.y;
      context.fillStyle = HANDLE_COLOR;
      context.fillRect(handleX - HANDLE_SIZE / 2, handleY - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE);
      context.strokeRect(handleX - HANDLE_SIZE / 2, handleY - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE);

    } else if (drawing.type === 'line') {
      context.beginPath(); // Re-stroke line with selection color
      context.moveTo(drawing.data.x1, drawing.data.y1);
      context.lineTo(drawing.data.x2, drawing.data.y2);
      context.stroke();

      // Handles for line endpoints
      context.fillStyle = HANDLE_COLOR;
      context.fillRect(drawing.data.x1 - HANDLE_SIZE / 2, drawing.data.y1 - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE);
      context.strokeRect(drawing.data.x1 - HANDLE_SIZE / 2, drawing.data.y1 - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE);
      context.fillRect(drawing.data.x2 - HANDLE_SIZE / 2, drawing.data.y2 - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE);
      context.strokeRect(drawing.data.x2 - HANDLE_SIZE / 2, drawing.data.y2 - HANDLE_SIZE / 2, HANDLE_SIZE, HANDLE_SIZE);

    } else if (drawing.type === 'dot') {
      // For dots, selection might just be a slightly larger circle or different color
      context.beginPath();
      context.arc(drawing.data.x, drawing.data.y, 5 + HANDLE_SIZE / 3, 0, 2 * Math.PI); // 5 is dot radius
      context.stroke();
    }
    context.restore();
  }
};

const redrawAnnotations = () => {
  if (!annCtx.value || !annotationCanvas.value) return;
  annCtx.value.clearRect(0, 0, canvasWidth.value, canvasHeight.value);
  if (props.drawings) {
    props.drawings.forEach((drawing, index) => { // Pass index here
      drawShape(annCtx.value, drawing, index);
    });
  }
};

onMounted(() => {
  // ... (original onMounted)
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
  if (newVal !== oldVal) {
    loadImage();
  }
});

// Watch for changes in drawings to redraw (e.g., after an emit or external change)
watch(() => props.drawings, () => {
  redrawAnnotations();
}, { deep: true });

// Watch for mode changes to deselect if necessary
watch(() => props.mode, (newMode) => {
  if (newMode !== 'edit') {
    selectedShapeIndex.value = -1; // Deselect when changing mode
    isEditing.value = false;
    editHandle.value = null;
  }
  redrawAnnotations(); // Redraw to remove selection visuals
});


const loadImage = () => {
  // ... (original loadImage)
  if (!props.imageName || !canvas__map.value) return;

  const bgCanvas = canvas__map.value;
  const image = new Image();

  image.onload = () => {
    canvasWidth.value = image.width;
    canvasHeight.value = image.height;
    bgCanvas.width = image.width;
    bgCanvas.height = image.height;
    if (annotationCanvas.value) { // Check if annotationCanvas is mounted
      annotationCanvas.value.width = image.width;
      annotationCanvas.value.height = image.height;
    }
    bgCtx.value.drawImage(image, 0, 0);
    redrawAnnotations();
  };

  image.onerror = () => {
    console.error(`Failed to load image: ${props.imageName}`);
  };

  image.src = props.imageName;
};

defineExpose({
  setDrawingMode,
  redrawCanvas: redrawAnnotations
});
</script>