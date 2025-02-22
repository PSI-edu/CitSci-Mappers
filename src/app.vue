<template>
  <div v-if="isLoading" class="page-layout">
    <PageLoader />
  </div>
  <router-view v-else />
</template>

<script setup>
import PageLoader from "@/components/page-loader.vue";
import { useAuth0 } from "@auth0/auth0-vue";

const { isLoading } = useAuth0();
</script>
