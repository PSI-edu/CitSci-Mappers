<template>
    <canvas ref="canvas__unbound" id="canvas__unbound" width="7642" height="5594"></canvas>
    <div id="citsci-title"></div>
</template>


<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  imageName: String,
  rectangles : {
    type: Array,
    default: () => [],
  }
});

const canvas__unbound = ref(null);

onMounted(() => {
  loadImage();
});

const loadImage = () => {
  if (!props.imageName || !canvas__unbound.value) return;

  const canvas = canvas__unbound.value;
  const ctx = canvas.getContext('2d');
  const image = new Image();

  image.onload = () => {
    canvas.width = image.width;
    canvas.height = image.height;
    ctx.drawImage(image, 0, 0);
    ctx.beginPath();
    ctx.lineWidth = 5;
    props.rectangles.forEach(rect => {
      if (rect.done === '0') {
        ctx.strokeStyle="green";
        ctx.strokeRect(rect.x, rect.y, 450, 450);
      } else {
        ctx.fillStyle='rgba(255, 0, 0, 0.25)';
        ctx.fillRect(rect.x, rect.y, 450, 450);
        ctx.strokeStyle="yellow";
        ctx.strokeRect(rect.x, rect.y, 450, 450);
      }
    });
  };
  image.src = props.imageName; // Assuming imageName is the correct path/URL

  image.onerror = () => {
    console.error(`Failed to load image: ${props.imageName}`);
  };
};

</script>

