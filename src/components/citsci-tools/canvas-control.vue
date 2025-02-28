<template>
    <canvas ref="canvas__control" id="canvas__control" width="450" height="450" @click="drawRectangle"></canvas>
    <div id="citsci-title"></div>
</template>


<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  imageName: String,
});

const canvas__control = ref(null);

onMounted(() => {
  loadImage();
});

watch(() => props.imageName, () => {
  loadImage();
});

const loadImage = () => {
  if (!props.imageName || !canvas__control.value) return;

  const canvas = canvas__control.value;
  const ctx = canvas.getContext('2d');
  const image = new Image();

  image.onload = () => {
    canvas.width = image.width;
    canvas.height = image.height;
    ctx.drawImage(image, 0, 0);
  };

  image.onerror = () => {
    console.error(`Failed to load image: ${props.imageName}`);
  };

  image.src = props.imageName; // Assuming imageName is the correct path/URL
};

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


