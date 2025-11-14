import { ref } from 'vue';
import axios from 'axios';

export function useMasterDataApi() {
    const loading = ref(false);
    const error = ref(null);

    // ========================================
    // UNIT KERJA API
    // ========================================

    const getUnitKerjas = async (params = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get('/api/unit-kerja', { params });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Unit Kerja';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getUnitKerja = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/unit-kerja/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Unit Kerja';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createUnitKerja = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post('/api/unit-kerja', data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create Unit Kerja';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateUnitKerja = async (id, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.put(`/api/unit-kerja/${id}`, data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update Unit Kerja';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteUnitKerja = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.delete(`/api/unit-kerja/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete Unit Kerja';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getActiveUnitKerjas = async () => {
        try {
            const response = await axios.get('/api/unit-kerja/active');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch active Unit Kerja';
            throw err;
        }
    };

    const getUnitKerjasByJenis = async (jenis) => {
        try {
            const response = await axios.get('/api/unit-kerja/by-jenis', { params: { jenis } });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Unit Kerja by jenis';
            throw err;
        }
    };

    const getRootUnitKerjas = async () => {
        try {
            const response = await axios.get('/api/unit-kerja/roots');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch root Unit Kerja';
            throw err;
        }
    };

    const getUnitKerjaChildren = async (parentId) => {
        try {
            const response = await axios.get(`/api/unit-kerja/children/${parentId}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Unit Kerja children';
            throw err;
        }
    };

    const getUnitKerjaStatistics = async () => {
        try {
            const response = await axios.get('/api/unit-kerja/statistics');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Unit Kerja statistics';
            throw err;
        }
    };

    const toggleUnitKerjaActive = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post(`/api/unit-kerja/${id}/toggle-active`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to toggle Unit Kerja active status';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // ========================================
    // PROGRAM STUDI API
    // ========================================

    const getProgramStudis = async (params = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get('/api/program-studi', { params });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Program Studi';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getProgramStudi = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/program-studi/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Program Studi';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createProgramStudi = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post('/api/program-studi', data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create Program Studi';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateProgramStudi = async (id, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.put(`/api/program-studi/${id}`, data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update Program Studi';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteProgramStudi = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.delete(`/api/program-studi/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete Program Studi';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getActiveProgramStudis = async () => {
        try {
            const response = await axios.get('/api/program-studi/active');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch active Program Studi';
            throw err;
        }
    };

    const getProgramStudisByJenjang = async (jenjang) => {
        try {
            const response = await axios.get('/api/program-studi/by-jenjang', { params: { jenjang } });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Program Studi by jenjang';
            throw err;
        }
    };

    const getProgramStudisByUnitKerja = async (unitKerjaId) => {
        try {
            const response = await axios.get('/api/program-studi/by-unit-kerja', { params: { unit_kerja_id: unitKerjaId } });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Program Studi by unit kerja';
            throw err;
        }
    };

    const getProgramStudisByAkreditasi = async (akreditasi) => {
        try {
            const response = await axios.get('/api/program-studi/by-akreditasi', { params: { akreditasi } });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Program Studi by akreditasi';
            throw err;
        }
    };

    const getProgramStudiStatistics = async () => {
        try {
            const response = await axios.get('/api/program-studi/statistics');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Program Studi statistics';
            throw err;
        }
    };

    const toggleProgramStudiActive = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post(`/api/program-studi/${id}/toggle-active`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to toggle Program Studi active status';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // ========================================
    // JABATAN API
    // ========================================

    const getJabatans = async (params = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get('/api/jabatan', { params });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Jabatan';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getJabatan = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/jabatan/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Jabatan';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createJabatan = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post('/api/jabatan', data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create Jabatan';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateJabatan = async (id, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.put(`/api/jabatan/${id}`, data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update Jabatan';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteJabatan = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.delete(`/api/jabatan/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete Jabatan';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getActiveJabatans = async () => {
        try {
            const response = await axios.get('/api/jabatan/active');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch active Jabatan';
            throw err;
        }
    };

    const getJabatansByKategori = async (kategori) => {
        try {
            const response = await axios.get('/api/jabatan/by-kategori', { params: { kategori } });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Jabatan by kategori';
            throw err;
        }
    };

    const getJabatansByLevel = async (level) => {
        try {
            const response = await axios.get('/api/jabatan/by-level', { params: { level } });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Jabatan by level';
            throw err;
        }
    };

    const getJabatanCategories = async () => {
        try {
            const response = await axios.get('/api/jabatan/categories');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Jabatan categories';
            throw err;
        }
    };

    const getJabatanStatistics = async () => {
        try {
            const response = await axios.get('/api/jabatan/statistics');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Jabatan statistics';
            throw err;
        }
    };

    const toggleJabatanActive = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post(`/api/jabatan/${id}/toggle-active`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to toggle Jabatan active status';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // ========================================
    // TAHUN AKADEMIK API
    // ========================================

    const getTahunAkademiks = async (params = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get('/api/tahun-akademik', { params });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Tahun Akademik';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getTahunAkademik = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/tahun-akademik/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Tahun Akademik';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createTahunAkademik = async (data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post('/api/tahun-akademik', data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create Tahun Akademik';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateTahunAkademik = async (id, data) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.put(`/api/tahun-akademik/${id}`, data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update Tahun Akademik';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteTahunAkademik = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.delete(`/api/tahun-akademik/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete Tahun Akademik';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const getActiveTahunAkademiks = async () => {
        try {
            const response = await axios.get('/api/tahun-akademik/active');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch active Tahun Akademik';
            throw err;
        }
    };

    const getCurrentTahunAkademik = async () => {
        try {
            const response = await axios.get('/api/tahun-akademik/current');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch current Tahun Akademik';
            throw err;
        }
    };

    const getUpcomingTahunAkademik = async () => {
        try {
            const response = await axios.get('/api/tahun-akademik/upcoming');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch upcoming Tahun Akademik';
            throw err;
        }
    };

    const getTahunAkademiksBySemester = async (semester) => {
        try {
            const response = await axios.get('/api/tahun-akademik/by-semester', { params: { semester } });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Tahun Akademik by semester';
            throw err;
        }
    };

    const getTahunAkademikStatistics = async () => {
        try {
            const response = await axios.get('/api/tahun-akademik/statistics');
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch Tahun Akademik statistics';
            throw err;
        }
    };

    const toggleTahunAkademikActive = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post(`/api/tahun-akademik/${id}/toggle-active`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to toggle Tahun Akademik active status';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        loading,
        error,
        // Unit Kerja
        getUnitKerjas,
        getUnitKerja,
        createUnitKerja,
        updateUnitKerja,
        deleteUnitKerja,
        getActiveUnitKerjas,
        getUnitKerjasByJenis,
        getRootUnitKerjas,
        getUnitKerjaChildren,
        getUnitKerjaStatistics,
        toggleUnitKerjaActive,
        // Program Studi
        getProgramStudis,
        getProgramStudi,
        createProgramStudi,
        updateProgramStudi,
        deleteProgramStudi,
        getActiveProgramStudis,
        getProgramStudisByJenjang,
        getProgramStudisByUnitKerja,
        getProgramStudisByAkreditasi,
        getProgramStudiStatistics,
        toggleProgramStudiActive,
        // Jabatan
        getJabatans,
        getJabatan,
        createJabatan,
        updateJabatan,
        deleteJabatan,
        getActiveJabatans,
        getJabatansByKategori,
        getJabatansByLevel,
        getJabatanCategories,
        getJabatanStatistics,
        toggleJabatanActive,
        // Tahun Akademik
        getTahunAkademiks,
        getTahunAkademik,
        createTahunAkademik,
        updateTahunAkademik,
        deleteTahunAkademik,
        getActiveTahunAkademiks,
        getCurrentTahunAkademik,
        getUpcomingTahunAkademik,
        getTahunAkademiksBySemester,
        getTahunAkademikStatistics,
        toggleTahunAkademikActive,
    };
}
