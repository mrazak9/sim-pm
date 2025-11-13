import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    isAuthenticated: !!localStorage.getItem('token'),
  }),

  getters: {
    currentUser: (state) => state.user,
    isLoggedIn: (state) => state.isAuthenticated,
    userRoles: (state) => state.user?.roles || [],
    userPermissions: (state) => state.user?.permissions || [],
    hasRole: (state) => (role) => {
      return state.user?.roles?.some((r) => r.name === role) || false;
    },
    hasPermission: (state) => (permission) => {
      return state.user?.permissions?.some((p) => p.name === permission) || false;
    },
  },

  actions: {
    async login(credentials) {
      try {
        const response = await axios.post('/api/login', credentials);

        if (response.data.success) {
          this.token = response.data.data.token;
          this.user = response.data.data.user;
          this.isAuthenticated = true;

          localStorage.setItem('token', this.token);
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

          return { success: true };
        }
      } catch (error) {
        console.error('Login error:', error);
        return {
          success: false,
          message: error.response?.data?.message || 'Login failed',
          errors: error.response?.data?.errors || null,
        };
      }
    },

    async register(userData) {
      try {
        const response = await axios.post('/api/register', userData);

        if (response.data.success) {
          this.token = response.data.data.token;
          this.user = response.data.data.user;
          this.isAuthenticated = true;

          localStorage.setItem('token', this.token);
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

          return { success: true };
        }
      } catch (error) {
        console.error('Register error:', error);
        return {
          success: false,
          message: error.response?.data?.message || 'Registration failed',
          errors: error.response?.data?.errors || null,
        };
      }
    },

    async logout() {
      try {
        await axios.post('/api/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
        this.token = null;
        this.isAuthenticated = false;

        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
      }
    },

    async fetchUser() {
      if (!this.token) return;

      try {
        const response = await axios.get('/api/me');
        if (response.data.success) {
          this.user = response.data.data;
          this.isAuthenticated = true;
        }
      } catch (error) {
        console.error('Fetch user error:', error);
        // If token is invalid, clear auth state
        if (error.response?.status === 401) {
          this.logout();
        }
      }
    },

    initAuth() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        this.fetchUser();
      }
    },
  },
});
