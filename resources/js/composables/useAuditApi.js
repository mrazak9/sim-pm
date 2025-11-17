import axios from 'axios';
import { ref } from 'vue';

export function useAuditApi() {
  const loading = ref(false);
  const error = ref(null);

  // ====================
  // AUDIT PLAN API
  // ====================

  /**
   * Get all audit plans with optional filters
   */
  const getAuditPlans = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-plans', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get active audit plans
   */
  const getActiveAuditPlans = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-plans/active');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get audit plan statistics
   */
  const getAuditPlanStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-plans/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get audit plan by ID
   */
  const getAuditPlan = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/audit-plans/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new audit plan
   */
  const createAuditPlan = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/audit-plans', data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update audit plan
   */
  const updateAuditPlan = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/audit-plans/${id}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete audit plan
   */
  const deleteAuditPlan = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/audit-plans/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Approve audit plan
   */
  const approveAuditPlan = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-plans/${id}/approve`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Start audit plan execution
   */
  const startAuditPlan = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-plans/${id}/start`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Complete audit plan
   */
  const completeAuditPlan = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-plans/${id}/complete`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // AUDIT SCHEDULE API
  // ====================

  /**
   * Get all audit schedules with optional filters
   */
  const getAuditSchedules = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-schedules', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get upcoming audit schedules
   */
  const getUpcomingAuditSchedules = async (days = 7) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-schedules/upcoming', { params: { days } });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get audit schedules for calendar
   */
  const getAuditScheduleCalendar = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-schedules/calendar', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get audit schedule statistics
   */
  const getAuditScheduleStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-schedules/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get audit schedule by ID
   */
  const getAuditSchedule = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/audit-schedules/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new audit schedule
   */
  const createAuditSchedule = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/audit-schedules', data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update audit schedule
   */
  const updateAuditSchedule = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/audit-schedules/${id}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete audit schedule
   */
  const deleteAuditSchedule = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/audit-schedules/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Start audit
   */
  const startAudit = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-schedules/${id}/start`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Complete audit
   */
  const completeAudit = async (id, data = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-schedules/${id}/complete`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Reschedule audit
   */
  const rescheduleAudit = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-schedules/${id}/reschedule`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // AUDIT FINDING API
  // ====================

  /**
   * Get all audit findings with optional filters
   */
  const getAuditFindings = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-findings', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get overdue audit findings
   */
  const getOverdueFindings = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-findings/overdue');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get findings needing attention
   */
  const getFindingsNeedingAttention = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-findings/needing-attention');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get audit finding statistics
   */
  const getAuditFindingStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-findings/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get finding statistics by category
   */
  const getFindingStatisticsByCategory = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/audit-findings/statistics-by-category');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get audit finding by ID
   */
  const getAuditFinding = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/audit-findings/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new audit finding
   */
  const createAuditFinding = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/audit-findings', data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update audit finding
   */
  const updateAuditFinding = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/audit-findings/${id}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete audit finding
   */
  const deleteAuditFinding = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/audit-findings/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Resolve audit finding
   */
  const resolveAuditFinding = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-findings/${id}/resolve`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Verify audit finding
   */
  const verifyAuditFinding = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-findings/${id}/verify`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Close audit finding
   */
  const closeAuditFinding = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-findings/${id}/close`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Reopen audit finding
   */
  const reopenAuditFinding = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/audit-findings/${id}/reopen`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ====================
  // RTL API
  // ====================

  /**
   * Get all RTLs with optional filters
   */
  const getRTLs = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtls', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get overdue RTLs
   */
  const getOverdueRTLs = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtls/overdue');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTLs due soon
   */
  const getRTLsDueSoon = async (days = 7) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtls/due-soon', { params: { days } });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTLs pending verification
   */
  const getRTLsPendingVerification = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtls/pending-verification');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTL statistics
   */
  const getRTLStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtls/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTL dashboard statistics
   */
  const getRTLDashboardStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtls/dashboard-statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTL statistics by unit kerja
   */
  const getRTLStatisticsByUnitKerja = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/rtls/statistics-by-unit-kerja');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get RTL by ID
   */
  const getRTL = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/rtls/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new RTL
   */
  const createRTL = async (data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/rtls', data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update RTL
   */
  const updateRTL = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/rtls/${id}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete RTL
   */
  const deleteRTL = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/rtls/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Start RTL
   */
  const startRTL = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtls/${id}/start`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Complete RTL
   */
  const completeRTL = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtls/${id}/complete`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Add progress to RTL
   */
  const addRTLProgress = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const formData = new FormData();
      Object.keys(data).forEach(key => {
        if (data[key] !== null && data[key] !== undefined) {
          formData.append(key, data[key]);
        }
      });

      const response = await axios.post(`/api/rtls/${id}/progress`, formData, {
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
   * Verify RTL
   */
  const verifyRTL = async (id, data) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/rtls/${id}/verify`, data);
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

    // Audit Plan methods
    getAuditPlans,
    getActiveAuditPlans,
    getAuditPlanStatistics,
    getAuditPlan,
    createAuditPlan,
    updateAuditPlan,
    deleteAuditPlan,
    approveAuditPlan,
    startAuditPlan,
    completeAuditPlan,

    // Audit Schedule methods
    getAuditSchedules,
    getUpcomingAuditSchedules,
    getAuditScheduleCalendar,
    getAuditScheduleStatistics,
    getAuditSchedule,
    createAuditSchedule,
    updateAuditSchedule,
    deleteAuditSchedule,
    startAudit,
    completeAudit,
    rescheduleAudit,

    // Audit Finding methods
    getAuditFindings,
    getOverdueFindings,
    getFindingsNeedingAttention,
    getAuditFindingStatistics,
    getFindingStatisticsByCategory,
    getAuditFinding,
    createAuditFinding,
    updateAuditFinding,
    deleteAuditFinding,
    resolveAuditFinding,
    verifyAuditFinding,
    closeAuditFinding,
    reopenAuditFinding,

    // RTL methods
    getRTLs,
    getOverdueRTLs,
    getRTLsDueSoon,
    getRTLsPendingVerification,
    getRTLStatistics,
    getRTLDashboardStatistics,
    getRTLStatisticsByUnitKerja,
    getRTL,
    createRTL,
    updateRTL,
    deleteRTL,
    startRTL,
    completeRTL,
    addRTLProgress,
    verifyRTL,
  };
}
