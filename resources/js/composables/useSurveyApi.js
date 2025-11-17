import axios from 'axios';
import { ref } from 'vue';

export function useSurveyApi() {
  const loading = ref(false);
  const error = ref(null);

  // ====================
  // SURVEY CRUD API
  // ====================

  /**
   * Get all surveys with optional filters
   */
  const getSurveys = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/surveys', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get survey by ID
   */
  const getSurvey = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/surveys/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new survey
   */
  const createSurvey = async (surveyData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/surveys', surveyData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update survey
   */
  const updateSurvey = async (id, surveyData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/surveys/${id}`, surveyData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete survey
   */
  const deleteSurvey = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/surveys/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // SURVEY ACTIONS API
  // ====================

  /**
   * Publish survey
   */
  const publishSurvey = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/surveys/${id}/publish`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Close survey
   */
  const closeSurvey = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/surveys/${id}/close`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Duplicate survey
   */
  const duplicateSurvey = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/surveys/${id}/duplicate`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get survey statistics
   */
  const getSurveyStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/surveys/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get published surveys
   */
  const getPublishedSurveys = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/surveys/published');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get active surveys
   */
  const getActiveSurveys = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/surveys/active');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // QUESTION MANAGEMENT API
  // ====================

  /**
   * Get questions for a survey
   */
  const getSurveyQuestions = async (surveyId) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/surveys/${surveyId}/questions`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new question for survey
   */
  const createQuestion = async (surveyId, questionData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/surveys/${surveyId}/questions`, questionData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update question
   */
  const updateQuestion = async (questionId, questionData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/survey-questions/${questionId}`, questionData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete question
   */
  const deleteQuestion = async (questionId) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/survey-questions/${questionId}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Reorder questions in a survey
   */
  const reorderQuestions = async (surveyId, orderData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/surveys/${surveyId}/questions/reorder`, orderData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Duplicate question
   */
  const duplicateQuestion = async (questionId) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/survey-questions/${questionId}/duplicate`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // RESPONSE MANAGEMENT API
  // ====================

  /**
   * Get responses for a survey
   */
  const getSurveyResponses = async (surveyId, params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/surveys/${surveyId}/responses`, { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Start new response for survey
   */
  const startResponse = async (surveyId) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/surveys/${surveyId}/responses/start`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Submit response answers
   */
  const submitResponse = async (responseId, answers) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/survey-responses/${responseId}/submit`, { answers });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get survey analytics
   */
  const getSurveyAnalytics = async (surveyId) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/surveys/${surveyId}/analytics`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  return {
    loading,
    error,
    // Survey CRUD
    getSurveys,
    getSurvey,
    createSurvey,
    updateSurvey,
    deleteSurvey,
    // Survey Actions
    publishSurvey,
    closeSurvey,
    duplicateSurvey,
    getSurveyStatistics,
    getPublishedSurveys,
    getActiveSurveys,
    // Question Management
    getSurveyQuestions,
    createQuestion,
    updateQuestion,
    deleteQuestion,
    reorderQuestions,
    duplicateQuestion,
    // Response Management
    getSurveyResponses,
    startResponse,
    submitResponse,
    getSurveyAnalytics,
  };
}
