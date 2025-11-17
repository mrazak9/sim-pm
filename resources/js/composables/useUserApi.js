import { ref } from 'vue';
import axios from 'axios';

export function useUserApi() {
  const loading = ref(false);
  const error = ref(null);

  // =====================
  // USER CRUD OPERATIONS
  // =====================

  /**
   * Get all users with optional filters
   * @param {Object} params - Filter parameters (search, is_active, unit_kerja_id, jabatan_id, role, per_page)
   * @returns {Promise}
   */
  const getUsers = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/users', { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch users';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get single user by ID
   * @param {number} id - User ID
   * @returns {Promise}
   */
  const getUser = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/users/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch user';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Create new user
   * @param {Object} userData - User data
   * @returns {Promise}
   */
  const createUser = async (userData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/users', userData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create user';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update user
   * @param {number} id - User ID
   * @param {Object} userData - User data
   * @returns {Promise}
   */
  const updateUser = async (id, userData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/users/${id}`, userData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update user';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Delete user
   * @param {number} id - User ID
   * @returns {Promise}
   */
  const deleteUser = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`/api/users/${id}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete user';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // ==========================
  // ROLE & PERMISSION OPERATIONS
  // ==========================

  /**
   * Assign roles to user
   * @param {number} userId - User ID
   * @param {Array} roles - Array of role names
   * @returns {Promise}
   */
  const assignRoles = async (userId, roles) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/users/${userId}/assign-roles`, { roles });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to assign roles';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Assign permissions to user
   * @param {number} userId - User ID
   * @param {Array} permissions - Array of permission names
   * @returns {Promise}
   */
  const assignPermissions = async (userId, permissions) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`/api/users/${userId}/assign-permissions`, { permissions });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to assign permissions';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get all roles
   * @returns {Promise}
   */
  const getRoles = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/roles');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch roles';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get all permissions
   * @returns {Promise}
   */
  const getPermissions = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/permissions');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch permissions';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // =====================
  // USER STATISTICS
  // =====================

  /**
   * Get user statistics
   * @returns {Promise}
   */
  const getUserStatistics = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/users/statistics');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch statistics';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get users by role
   * @param {string} role - Role name
   * @returns {Promise}
   */
  const getUsersByRole = async (role) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/users', { params: { role } });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch users by role';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get active users
   * @returns {Promise}
   */
  const getActiveUsers = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/users', { params: { is_active: 1 } });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch active users';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Toggle user active status
   * @param {number} id - User ID
   * @param {boolean} isActive - New active status
   * @returns {Promise}
   */
  const toggleUserActive = async (id, isActive) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/users/${id}`, { is_active: isActive });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to toggle user status';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // =====================
  // USER PROFILE
  // =====================

  /**
   * Get current user profile
   * @returns {Promise}
   */
  const getProfile = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/me');
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch profile';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Update current user profile
   * @param {Object} profileData - Profile data
   * @returns {Promise}
   */
  const updateProfile = async (profileData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put('/api/me', profileData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update profile';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Change password
   * @param {Object} passwordData - Password data (current_password, password, password_confirmation)
   * @returns {Promise}
   */
  const changePassword = async (passwordData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/me/change-password', passwordData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to change password';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  return {
    loading,
    error,
    // User CRUD
    getUsers,
    getUser,
    createUser,
    updateUser,
    deleteUser,
    // Role & Permission
    assignRoles,
    assignPermissions,
    getRoles,
    getPermissions,
    // Statistics
    getUserStatistics,
    getUsersByRole,
    getActiveUsers,
    toggleUserActive,
    // Profile
    getProfile,
    updateProfile,
    changePassword,
  };
}
