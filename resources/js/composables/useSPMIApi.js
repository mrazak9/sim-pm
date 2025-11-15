import axios from 'axios';
import { ref } from 'vue';

export function useSPMIApi() {
  const loading = ref(false);
  const error = ref(null);

  // ====================
  // SPMI STANDARD API
  // ====================

  /**
   * Get all SPMI standards with optional filters
   */
  const getSPMIStandards = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/spmi-standards', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get SPMI standard statistics
   */
  const getSPMIStandardStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/spmi-standards/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get standards due for review
   */
  const getStandardsDueForReview = async (days = 30) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/spmi-standards/due-for-review', { params: { days } });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get SPMI standard by ID
   */
  const getSPMIStandard = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/spmi-standards/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new SPMI standard
   */
  const createSPMIStandard = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/spmi-standards', data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update SPMI standard
   */
  const updateSPMIStandard = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/spmi-standards/${id}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete SPMI standard
   */
  const deleteSPMIStandard = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/spmi-standards/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Approve SPMI standard
   */
  const approveSPMIStandard = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/spmi-standards/${id}/approve`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Revise SPMI standard
   */
  const reviseSPMIStandard = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/spmi-standards/${id}/revise`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Archive SPMI standard
   */
  const archiveSPMIStandard = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/spmi-standards/${id}/archive`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // SPMI INDICATOR API
  // ====================

  /**
   * Get all SPMI indicators with optional filters
   */
  const getSPMIIndicators = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/spmi-indicators', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get SPMI indicator statistics
   */
  const getSPMIIndicatorStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/spmi-indicators/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get SPMI indicator by ID
   */
  const getSPMIIndicator = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/spmi-indicators/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new SPMI indicator
   */
  const createSPMIIndicator = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/spmi-indicators', data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update SPMI indicator
   */
  const updateSPMIIndicator = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/spmi-indicators/${id}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete SPMI indicator
   */
  const deleteSPMIIndicator = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/spmi-indicators/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Activate SPMI indicator
   */
  const activateSPMIIndicator = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/spmi-indicators/${id}/activate`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Deactivate SPMI indicator
   */
  const deactivateSPMIIndicator = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/spmi-indicators/${id}/deactivate`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create target for indicator
   */
  const createIndicatorTarget = async (indicatorId, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/spmi-indicators/${indicatorId}/targets`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update indicator target
   */
  const updateIndicatorTarget = async (indicatorId, targetId, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/spmi-indicators/${indicatorId}/targets/${targetId}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Record achievement for indicator
   */
  const recordIndicatorAchievement = async (indicatorId, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/spmi-indicators/${indicatorId}/achievement`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // SPMI MONITORING API
  // ====================

  /**
   * Get all SPMI monitorings with optional filters
   */
  const getSPMIMonitorings = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/spmi-monitorings', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get SPMI monitoring statistics
   */
  const getSPMIMonitoringStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/spmi-monitorings/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get SPMI monitoring dashboard data
   */
  const getSPMIMonitoringDashboard = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/spmi-monitorings/dashboard-data');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get SPMI monitoring by ID
   */
  const getSPMIMonitoring = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/spmi-monitorings/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new SPMI monitoring
   */
  const createSPMIMonitoring = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/spmi-monitorings', data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update SPMI monitoring
   */
  const updateSPMIMonitoring = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/spmi-monitorings/${id}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete SPMI monitoring
   */
  const deleteSPMIMonitoring = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/spmi-monitorings/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Start SPMI monitoring
   */
  const startSPMIMonitoring = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/spmi-monitorings/${id}/start`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Complete SPMI monitoring
   */
  const completeSPMIMonitoring = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/spmi-monitorings/${id}/complete`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Upload monitoring report
   */
  const uploadMonitoringReport = async (id, file) => {
    loading.value = true;
    error.value = null;
    try {
      const formData = new FormData();
      formData.append('report_file', file);

      const response = await axios.post(`/api/spmi-monitorings/${id}/upload-report`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // RTM API
  // ====================

  /**
   * Get all RTMs with optional filters
   */
  const getRTMs = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtms', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTM statistics
   */
  const getRTMStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtms/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get upcoming RTMs
   */
  const getUpcomingRTMs = async (days = 30) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtms/upcoming', { params: { days } });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTM by ID
   */
  const getRTM = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/rtms/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new RTM
   */
  const createRTM = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/rtms', data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update RTM
   */
  const updateRTM = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/rtms/${id}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete RTM
   */
  const deleteRTM = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/rtms/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Start RTM
   */
  const startRTM = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtms/${id}/start`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Complete RTM
   */
  const completeRTM = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtms/${id}/complete`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Cancel RTM
   */
  const cancelRTM = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtms/${id}/cancel`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Add participant to RTM
   */
  const addRTMParticipant = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtms/${id}/participants`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Remove participant from RTM
   */
  const removeRTMParticipant = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/rtms/${id}/participants`, { data });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Mark attendance for RTM
   */
  const markRTMAttendance = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtms/${id}/attendance`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Upload RTM minutes
   */
  const uploadRTMMinutes = async (id, file) => {
    loading.value = true;
    error.value = null;
    try {
      const formData = new FormData();
      formData.append('minutes_file', file);

      const response = await axios.post(`/api/rtms/${id}/upload-minutes`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Upload RTM attendance
   */
  const uploadRTMAttendance = async (id, file) => {
    loading.value = true;
    error.value = null;
    try {
      const formData = new FormData();
      formData.append('attendance_file', file);

      const response = await axios.post(`/api/rtms/${id}/upload-attendance`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // RTM ACTION ITEM API
  // ====================

  /**
   * Get all RTM action items with optional filters
   */
  const getRTMActionItems = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtm-action-items', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTM action item statistics
   */
  const getRTMActionItemStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtm-action-items/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTM action item dashboard statistics
   */
  const getRTMActionItemDashboard = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtm-action-items/dashboard-statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get overdue RTM action items
   */
  const getOverdueRTMActionItems = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtm-action-items/overdue');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTM action items due soon
   */
  const getRTMActionItemsDueSoon = async (days = 7) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtm-action-items/due-soon', { params: { days } });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTM action item by ID
   */
  const getRTMActionItem = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/rtm-action-items/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new RTM action item
   */
  const createRTMActionItem = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/rtm-action-items', data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update RTM action item
   */
  const updateRTMActionItem = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/rtm-action-items/${id}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete RTM action item
   */
  const deleteRTMActionItem = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/rtm-action-items/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Start RTM action item
   */
  const startRTMActionItem = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtm-action-items/${id}/start`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Complete RTM action item
   */
  const completeRTMActionItem = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtm-action-items/${id}/complete`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Cancel RTM action item
   */
  const cancelRTMActionItem = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtm-action-items/${id}/cancel`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Add progress to RTM action item
   */
  const addRTMActionItemProgress = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const formData = new FormData();
      Object.keys(data).forEach(key => {
        if (data[key] !== null && data[key] !== undefined) {
          formData.append(key, data[key]);
        }
      });

      const response = await axios.post(`/api/rtm-action-items/${id}/progress`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Extend RTM action item due date
   */
  const extendRTMActionItem = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtm-action-items/${id}/extend`, data);
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

    // SPMI Standard methods
    getSPMIStandards,
    getSPMIStandardStatistics,
    getStandardsDueForReview,
    getSPMIStandard,
    createSPMIStandard,
    updateSPMIStandard,
    deleteSPMIStandard,
    approveSPMIStandard,
    reviseSPMIStandard,
    archiveSPMIStandard,

    // SPMI Indicator methods
    getSPMIIndicators,
    getSPMIIndicatorStatistics,
    getSPMIIndicator,
    createSPMIIndicator,
    updateSPMIIndicator,
    deleteSPMIIndicator,
    activateSPMIIndicator,
    deactivateSPMIIndicator,
    createIndicatorTarget,
    updateIndicatorTarget,
    recordIndicatorAchievement,

    // SPMI Monitoring methods
    getSPMIMonitorings,
    getSPMIMonitoringStatistics,
    getSPMIMonitoringDashboard,
    getSPMIMonitoring,
    createSPMIMonitoring,
    updateSPMIMonitoring,
    deleteSPMIMonitoring,
    startSPMIMonitoring,
    completeSPMIMonitoring,
    uploadMonitoringReport,

    // RTM methods
    getRTMs,
    getRTMStatistics,
    getUpcomingRTMs,
    getRTM,
    createRTM,
    updateRTM,
    deleteRTM,
    startRTM,
    completeRTM,
    cancelRTM,
    addRTMParticipant,
    removeRTMParticipant,
    markRTMAttendance,
    uploadRTMMinutes,
    uploadRTMAttendance,

    // RTM Action Item methods
    getRTMActionItems,
    getRTMActionItemStatistics,
    getRTMActionItemDashboard,
    getOverdueRTMActionItems,
    getRTMActionItemsDueSoon,
    getRTMActionItem,
    createRTMActionItem,
    updateRTMActionItem,
    deleteRTMActionItem,
    startRTMActionItem,
    completeRTMActionItem,
    cancelRTMActionItem,
    addRTMActionItemProgress,
    extendRTMActionItem,
  };
}
