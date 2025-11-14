import { ref } from 'vue';
import axios from 'axios';

export function useIKUApi() {
    const loading = ref(false);
    const error = ref(null);

    // IKU Master Data API
    const getIKUs = async (params = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get('/api/iku', { params });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch IKUs';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getIKU = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/iku/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch IKU';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createIKU = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post('/api/iku', data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create IKU';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateIKU = async (id, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.put(`/api/iku/${id}`, data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update IKU';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteIKU = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.delete(`/api/iku/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete IKU';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getCategories = async () => {
        try {
            const response = await axios.get('/api/iku/categories');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch categories';
            throw err;
        }
    };

    // IKU Target API
    const getIKUTargets = async (params = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get('/api/iku-targets', { params });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch IKU Targets';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getIKUTarget = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/iku-targets/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch IKU Target';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createIKUTarget = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post('/api/iku-targets', data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create IKU Target';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateIKUTarget = async (id, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.put(`/api/iku-targets/${id}`, data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update IKU Target';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteIKUTarget = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.delete(`/api/iku-targets/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete IKU Target';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getTargetStatistics = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/iku-targets/${id}/statistics`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch statistics';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // IKU Progress API
    const getIKUProgress = async (params = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get('/api/iku-progress', { params });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch IKU Progress';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getIKUProgressById = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/iku-progress/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch IKU Progress';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createIKUProgress = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post('/api/iku-progress', data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create IKU Progress';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateIKUProgress = async (id, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post(`/api/iku-progress/${id}`, data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update IKU Progress';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteIKUProgress = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.delete(`/api/iku-progress/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete IKU Progress';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const downloadDocument = async (id) => {
        try {
            const response = await axios.get(`/api/iku-progress/${id}/download`, {
                responseType: 'blob',
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to download document';
            throw err;
        }
    };

    const getProgressSummary = async (targetId) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/iku-progress/target/${targetId}/summary`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch progress summary';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        loading,
        error,
        // IKU
        getIKUs,
        getIKU,
        createIKU,
        updateIKU,
        deleteIKU,
        getCategories,
        // IKU Target
        getIKUTargets,
        getIKUTarget,
        createIKUTarget,
        updateIKUTarget,
        deleteIKUTarget,
        getTargetStatistics,
        // IKU Progress
        getIKUProgress,
        getIKUProgressById,
        createIKUProgress,
        updateIKUProgress,
        deleteIKUProgress,
        downloadDocument,
        getProgressSummary,
    };
}
