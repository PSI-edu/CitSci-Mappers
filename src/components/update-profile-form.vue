<template>
  <div>
    <label>email: </label> {{ email }}<br />
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
import axios from 'axios';

const props = defineProps({
  email: {
    type: String,
    required: true,
  },
  userId: {
    type: String,
    required: true,
  },
});

const form = reactive({
  username: '',
  publishable_name: '',
});

const usernameError = ref('');
const apiError = ref('');
const successMessage = ref('');
const isSubmitting = ref(false);

// Get user ID and email from local storage
const id = props.userId;
const email = props.email;

console.log(id, email)

onMounted(async () => {
  if (!id || !email) {
    console.error('Not set.', id, email);
    apiError.value = 'Error passing user info.';
    return;
  }

  try {
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-profile.php", {
      id: id,
      email: email,
    });

    if (response.data) {
      form.username = response.data.username;
      form.publishable_name = response.data.publishable_name || '';
      usernameError.value = ''; // Clear any previous username error
      apiError.value = '';
      successMessage.value = '';
    } else {
      successMessage.value = 'Welcome unnamed human - please set some values.';
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
    console.log("here", form.username, id);
    // Check if the username already exists (excluding the current user)
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-validateusername.php", {
        username: form.username,
        id: id, // Pass the current user's ID to exclude them from the check
    });

    console.log(response.data);

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
    const response = await axios.post(import.meta.env.VITE_MAPPERS_API_SERVER + "/user-update.php", {
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

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

input[type="text"] {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.invalid-feedback {
  color: red;
  font-size: 0.8em;
}

button {
  padding: 10px 15px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1em;
}

button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.error-message {
  color: red;
  margin-top: 10px;
}

.success-message {
  color: green;
  margin-top: 10px;
}
</style>