import { ref } from 'vue'
import axios from 'axios'

export function useNotificationApi() {
    const loading = ref(false)
    const error = ref(null)

    /**
     * Get user notifications
     */
    const getNotifications = async (filters = {}) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get('/api/notifications', { params: filters })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    /**
     * Get unread notification count
     */
    const getUnreadCount = async () => {
        error.value = null
        try {
            const response = await axios.get('/api/notifications/unread-count')
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        }
    }

    /**
     * Mark notification as read
     */
    const markAsRead = async (id) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post(`/api/notifications/${id}/mark-as-read`)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    /**
     * Mark all notifications as read
     */
    const markAllAsRead = async () => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post('/api/notifications/mark-all-as-read')
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        error,
        getNotifications,
        getUnreadCount,
        markAsRead,
        markAllAsRead,
    }
}
