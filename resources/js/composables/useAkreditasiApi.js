import { ref } from 'vue'
import axios from 'axios'

export function useAkreditasiApi() {
    const loading = ref(false)
    const error = ref(null)

    // Periode Akreditasi API
    const getPeriodeList = async (filters = {}) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get('/api/periode-akreditasi', { params: filters })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const getPeriodeDetail = async (id) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get(`/api/periode-akreditasi/${id}`)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const createPeriode = async (data) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post('/api/periode-akreditasi', data)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const updatePeriode = async (id, data) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.put(`/api/periode-akreditasi/${id}`, data)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const deletePeriode = async (id) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.delete(`/api/periode-akreditasi/${id}`)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const getPeriodeStatistics = async (id) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get(`/api/periode-akreditasi/${id}/statistics`)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const exportPeriodePDF = async (id) => {
        try {
            const response = await axios.get(`/api/periode-akreditasi/${id}/export/pdf`, {
                responseType: 'blob'
            })
            return response
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        }
    }

    const exportPeriodeExcel = async (id) => {
        try {
            const response = await axios.get(`/api/periode-akreditasi/${id}/export/excel`, {
                responseType: 'blob'
            })
            return response
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        }
    }

    // Butir Akreditasi API
    const getButirList = async (filters = {}) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get('/api/butir-akreditasi', { params: filters })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const getButirByKategori = async (instrumen = '4.0') => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get('/api/butir-akreditasi/by-kategori', {
                params: { instrumen }
            })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const getInstrumenList = async () => {
        try {
            const response = await axios.get('/api/butir-akreditasi/instrumen')
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        }
    }

    const getKategoriList = async (instrumen = '4.0') => {
        try {
            const response = await axios.get('/api/butir-akreditasi/kategori', {
                params: { instrumen }
            })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        }
    }

    const getButirDetail = async (id) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get(`/api/butir-akreditasi/${id}`)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const createButir = async (data) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post('/api/butir-akreditasi', data)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const updateButir = async (id, data) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.put(`/api/butir-akreditasi/${id}`, data)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const deleteButir = async (id) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.delete(`/api/butir-akreditasi/${id}`)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    // Pengisian Butir API
    const getPengisianList = async (filters = {}) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get('/api/pengisian-butir', { params: filters })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const savePengisian = async (data) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post('/api/pengisian-butir', data)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const updatePengisian = async (id, data) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.put(`/api/pengisian-butir/${id}`, data)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const submitPengisian = async (id) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post(`/api/pengisian-butir/${id}/submit`)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const approvePengisian = async (id, notes = null) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post(`/api/pengisian-butir/${id}/approve`, {
                review_notes: notes
            })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const revisionPengisian = async (id, notes) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post(`/api/pengisian-butir/${id}/revision`, {
                review_notes: notes
            })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const getPengisianSummary = async (periodeId) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get(`/api/pengisian-butir/periode/${periodeId}/summary`)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    // Dokumen Akreditasi API
    const getDokumenList = async (filters = {}) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.get('/api/dokumen-akreditasi', { params: filters })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const uploadDokumen = async (formData) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post('/api/dokumen-akreditasi', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const deleteDokumen = async (id) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.delete(`/api/dokumen-akreditasi/${id}`)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const downloadDokumen = async (id) => {
        try {
            const response = await axios.get(`/api/dokumen-akreditasi/${id}/download`, {
                responseType: 'blob'
            })
            return response
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        }
    }

    const attachDokumenToButir = async (dokumenId, butirIds) => {
        loading.value = true
        error.value = null
        try {
            const response = await axios.post(`/api/dokumen-akreditasi/${dokumenId}/attach-butir`, {
                butir_akreditasi_ids: butirIds
            })
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
        // Periode Akreditasi
        getPeriodeList,
        getPeriodeDetail,
        createPeriode,
        updatePeriode,
        deletePeriode,
        getPeriodeStatistics,
        exportPeriodePDF,
        exportPeriodeExcel,
        // Butir Akreditasi
        getButirList,
        getButirByKategori,
        getInstrumenList,
        getKategoriList,
        getButirDetail,
        createButir,
        updateButir,
        deleteButir,
        // Pengisian Butir
        getPengisianList,
        savePengisian,
        updatePengisian,
        submitPengisian,
        approvePengisian,
        revisionPengisian,
        getPengisianSummary,
        // Dokumen Akreditasi
        getDokumenList,
        uploadDokumen,
        deleteDokumen,
        downloadDokumen,
        attachDokumenToButir,
    }
}
