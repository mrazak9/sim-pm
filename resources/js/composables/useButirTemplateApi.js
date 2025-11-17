import { ref } from 'vue'
import axios from 'axios'

export function useButirTemplateApi() {
  const loading = ref(false)
  const error = ref(null)

  /**
   * Get all butir with template status
   */
  const getButirTemplates = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get('/api/butir-templates', { params })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch butir templates'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Get template configuration for specific butir
   */
  const getTemplateById = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/butir-templates/${id}`)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch template'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create or update template
   */
  const saveTemplate = async (id, templateData) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`/api/butir-templates/${id}`, templateData)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to save template'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete template
   */
  const deleteTemplate = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.delete(`/api/butir-templates/${id}`)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete template'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Copy template from another butir
   */
  const copyTemplate = async (targetId, sourceId) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`/api/butir-templates/${targetId}/copy`, {
        source_butir_id: sourceId
      })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to copy template'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    getButirTemplates,
    getTemplateById,
    saveTemplate,
    deleteTemplate,
    copyTemplate,
  }
}
