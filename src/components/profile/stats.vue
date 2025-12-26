<template>
  <div id="stats-container">
    <div v-if="loading" class="loading">Loading applications...</div>

    <div v-else-if="error" class="error">{{ error }}</div>

    <div
        v-else
        v-for="app in applications"
        :key="app.id"
        class="stats-box"
    >
      <div class="icon-wrapper">
        <img :src="app.icon_url" :alt="app.title" class="app-icon" />
      </div>
      <div class="content text-center">
        <h3>{{ app.title }}</h3>
        <p><strong>Your Progress:</strong><br>
          complete: {{ app.user_count }} images <br>
          remaining: {{ app.available_to_user }} images<br>
        </p>
        <p><strong>Community Progress:</strong> <br>
          {{ app.images_remaining }} / {{ app.total_images }} needing completed</p>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/api/axios'; // Use our configured client

const props = defineProps({
  email: {
    type: String,
    required: true,
  },
  userId: {
    type: Number,
    required: true,
  },
});

// Reactive state
const applications = ref([]);
const loading = ref(true);
const error = ref(null);

const id = props.userId;
const email = props.email;

// Function to fetch data from your PHP API
const fetchApplications = async () => {
  try {
    // Replace with your actual PHP endpoint URL
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-stats.php", {
      id: id,
      email: email
    });

    const data = response.data;
    console.log(">", response.data);

    // Filter for active projects if desired
    applications.value = data.filter(app => app.active == 1);
  } catch (err) {
    error.value = "Error loading applications: " + err.message;
  } finally {
    loading.value = false;
  }
};

// Lifecycle hook
onMounted(() => {
  fetchApplications();
});
</script>