<template>
    <canvas ref="canvas__map" id="canvas__map" width="450" height="450" @click="drawRectangle"></canvas>
    <div id="citsci-title"></div>
  <img id="source-image" src="/src/assets/images/icon.svg" style="display: none;">
</template>


<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
  imageUrl: String,
});

onMounted(() => {
  console.log("here");

  const canvas = document.getElementById('canvas__map');
  if (!canvas) {
    console.error('Canvas element not found.');
    return;
  }

  const ctx = canvas.getContext('2d');
  if (!ctx) {
    console.error('Canvas context not available.');
    return;
  }

  const image = new Image();
  image.onload = () => {
    ctx.drawImage(image, 0, 0, canvas.width, canvas.height); // Draw the image to fill the canvas
  };
  image.onerror = () => {
    console.error('Error loading image:', props.imageUrl);
  };
  image.src = props.imageUrl;
  console.log(props.imageUrl);
});

</script>

<script>
export default {
  methods: {
    drawRectangle(event) {
      const canvas = this.$refs.canvas__map;
      const ctx = canvas.getContext('2d');
      const boxEdge = canvas.getBoundingClientRect();
      let mouseX = event.clientX - boxEdge.left;
      let mouseY = event.clientY - boxEdge.top;
      console.log("Click: " + mouseX.toString() + " " + mouseY.toString());

      const rectWidth = 30;
      const rectHeight = 30;

      const x = mouseX - rectWidth / 2;
      const y = mouseY - rectHeight / 2;

      ctx.beginPath();
      ctx.strokeStyle="red";
      ctx.rect(x, y, rectWidth, rectHeight);
      ctx.stroke();
    }
  }
}
</script>


