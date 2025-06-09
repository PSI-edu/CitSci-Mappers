import axios from 'axios';

// Create an axios instance
const apiClient = axios.create({
    baseURL: import.meta.env.VITE_MAPPERS_API_SERVER,
    headers: {
        'Content-Type': 'application/json',
    },
});

// This is a NAMED export
export const setupAxiosInterceptors = (auth0) => {
    apiClient.interceptors.request.use(
        async (config) => {
            try {
                const token = await auth0.getAccessTokenSilently();
                if (token) {
                    config.headers.Authorization = `Bearer ${token}`;
                }
            } catch (error) {
                console.error('Could not get access token:', error);
            }
            return config;
        },
        (error) => {
            return Promise.reject(error);
        }
    );
};

export default apiClient;