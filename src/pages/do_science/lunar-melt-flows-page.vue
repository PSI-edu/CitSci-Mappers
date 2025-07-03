<template>
  <template v-if="isNoFingers">
    <PageLayout title=": Lunar Melt" >
      <div id="citsci-main-panel">
        <div id="citsci-buttons-panel">

        </div>
      </div>

      <div id="citsci-mapping-panel">

      </div>

    </PageLayout>
  </template>
  <template v-else>
    <PageLayout title=": Lunar Melt BETA" >
      <div class="content-layout">
        <p>Sorry, this tool is only available when using a pointer such as a mouse or stylus.</p>
      </div>
    </PageLayout>
  </template>
</template>


<script setup>
import { useIsNoFingers } from "@/composables/noFingers.js";
import PageLayout from "@/components/page-layout.vue";
import CanvasMap from "@/components/citsci-tools/canvas-map.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import { onMounted, ref } from 'vue';
import apiClient from '@/api/axios';

const isNoFingers = useIsNoFingers();
</script>