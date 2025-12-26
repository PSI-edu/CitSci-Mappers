<template>
  <div>
    <p><strong>special roles:</strong> {{ form.roles }}</p>

    <form @submit.prevent="submitForm">
      <div>
        <label for="username">username:</label>
        <input
            id="username"
            type="text"
            v-model="form.username"
            @blur="validateUsername"
            :class="{ 'is-invalid': usernameError }"
        />
        <div v-if="usernameError" class="invalid-feedback">{{ usernameError }}</div>
      </div>

      <div>
        <label for="publishable_name">Publishable Name:</label>
        <input
            id="publishable_name"
            type="text"
            v-model="form.publishable_name"
        />
      </div>

      <button type="submit" :disabled="isSubmitting">
        {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
      </button>

      <div v-if="apiError" class="error-message">{{ apiError }}</div>
      <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, defineProps } from 'vue';
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

const form = reactive({
  username: '',
  publishable_name: '',
  roles: '',
});

const usernameError = ref('');
const apiError = ref('');
const successMessage = ref('');
const isSubmitting = ref(false);

const hasAdminRole = ref(false);
const hasSciRole = ref(false);

// Get user ID and email from local storage
const id = props.userId;
const email = props.email;

onMounted(async () => {
  if (!id || !email) {
    console.error('Not set.', id, email);
    apiError.value = 'Error passing user info.';
    return;
  }

  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-profile.php", {
      id: id,
      email: email
    });

    if (response.data) {
      form.username = response.data.username;
      form.publishable_name = response.data.publishable_name || '';
      form.roles = response.data.roles || 'none';

      // Stuff Related to Form
      usernameError.value = ''; // Clear any previous username error
      apiError.value = '';
      successMessage.value = '';
      if (response.data.username == 'not set' && response.data.publishable_name == 'not set') {
        successMessage.value = 'Welcome human - please set some values so we know who you are.';
      }
    } else {
      apiError.value = 'Your account wasn\'t added to the DB correctly. Please alert Pamela on the Discord or email her at plg@psi.edu';
      console.error('Failed to fetch user data:', response);
    }

  } catch (error) {
    console.error('Error fetching user data:', error);
    apiError.value = 'Could not load user information.';
  }

});

const validateUsername = async () => {
  if (!form.username) {
    usernameError.value = 'Username is required.';
    return;
  }

  if (form.username.length < 2) {
    usernameError.value = 'Username must be at least 2 characters long.';
    return;
  }

  try {
    // Check if the username already exists (excluding the current user)
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-validateusername.php", {
        username: form.username,
        id: id, // Pass the current user's ID to exclude them from the check
    });

    if (response.data && response.data.exists) {
      usernameError.value = 'This username is already taken.';
    } else {
      usernameError.value = '';
    }
  } catch (error) {
    console.error('Error checking username availability:', error);
    usernameError.value = 'Could not verify username availability.';
  }
};

const submitForm = async () => {
  if (usernameError.value) {
    apiError.value = 'Please fix the errors before submitting.';
    return;
  }

  isSubmitting.value = true;
  apiError.value = '';
  successMessage.value = '';

  try {
    const response = await apiClient.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-update.php", {
      id: id,
      email: email,
      username: form.username,
      publishable_name: form.publishable_name,
    });

    if (response.data && response.data.success) {
      successMessage.value = 'Profile updated successfully!';
    } else {
      apiError.value = 'Failed to update profile.';
      console.log(response.data);
    }
  } catch (error) {
    console.error('Error updating profile:', error);
    apiError.value = 'Could not update profile.';
  } finally {
    isSubmitting.value = false;
  }
};

</script>

<style scoped>
/* Basic styling for the form */
form {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 400px;
  margin: 20px 0;
}
</style>