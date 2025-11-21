import { ref, computed } from 'vue'
import axios from 'axios'

export function useButirData() {
  const data = ref([])
  const mappings = ref([])
  const loading = ref(false)
  const error = ref(null)

  /**
   * Fetch column mappings for a butir
   */
  const fetchMappings = async (butirId) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/butir-akreditasis/${butirId}/mappings`)
      mappings.value = response.data.data
      return mappings.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch mappings'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch data for a pengisian butir
   */
  const fetchData = async (pengisianButirId) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/pengisian-butirs/${pengisianButirId}/data`)
      data.value = response.data.data
      return data.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch data'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a single row
   */
  const createRow = async (pengisianButirId, rowData) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`/api/pengisian-butirs/${pengisianButirId}/data`, rowData)

      // Add to local data
      data.value.push(response.data.data)

      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create row'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Update a row
   */
  const updateRow = async (rowId, rowData) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`/api/butir-data/${rowId}`, rowData)

      // Update local data
      const index = data.value.findIndex(row => row.id === rowId)
      if (index !== -1) {
        data.value[index] = response.data.data
      }

      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update row'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete a row
   */
  const deleteRow = async (rowId) => {
    loading.value = true
    error.value = null

    try {
      await axios.delete(`/api/butir-data/${rowId}`)

      // Remove from local data
      data.value = data.value.filter(row => row.id !== rowId)

      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete row'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Bulk create rows
   */
  const bulkCreateRows = async (pengisianButirId, rows) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`/api/pengisian-butirs/${pengisianButirId}/data/bulk`, {
        rows
      })

      // Replace local data
      data.value = response.data.data

      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to bulk create rows'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Export data
   */
  const exportData = async (pengisianButirId) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/pengisian-butirs/${pengisianButirId}/data/export`)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to export data'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Query data with filters
   */
  const queryData = async (butirId, filters = [], orderBy = null, orderDirection = 'asc', perPage = 15) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post('/api/butir-data/query', {
        butir_id: butirId,
        filters,
        order_by: orderBy,
        order_direction: orderDirection,
        per_page: perPage
      })

      data.value = response.data.data.data

      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to query data'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Get form config from mappings
   * Transforms column mappings to form config format
   */
  const getFormConfigFromMappings = computed(() => {
    if (!mappings.value || mappings.value.length === 0) {
      return null
    }

    return {
      type: 'table',
      label: 'Data Form',
      columns: mappings.value.map(mapping => ({
        name: mapping.field_name,
        label: mapping.field_label,
        type: mapping.field_type,
        required: mapping.is_required,
        width: mapping.width,
        placeholder: mapping.placeholder,
        help_text: mapping.help_text,
        ...mapping.field_config
      })),
      options: {
        allow_add: true,
        allow_delete: true,
        allow_export: true
      }
    }
  })

  return {
    // State
    data,
    mappings,
    loading,
    error,

    // Actions
    fetchMappings,
    fetchData,
    createRow,
    updateRow,
    deleteRow,
    bulkCreateRows,
    exportData,
    queryData,

    // Computed
    getFormConfigFromMappings
  }
}
