<template>
  <MainLayout>
    <div>
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Instrumen Akreditasi</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola instrumen akreditasi untuk periode akreditasi
          </p>
        </div>
        <router-link
          to="/akreditasi/instrumen/create"
          class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
        >
          <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah Instrumen
        </router-link>
      </div>

      <!-- Filters -->
      <div class="mb-6 rounded-lg bg-white p-4 shadow dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <!-- Search -->
          <div>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari instrumen..."
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <!-- Jenis Filter -->
          <div>
            <select
              v-model="filters.jenis"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Semua Jenis</option>
              <option value="program_studi">Program Studi</option>
              <option value="institusi">Institusi</option>
              <option value="both">Keduanya</option>
            </select>
          </div>

          <!-- Lembaga Filter -->
          <div>
            <select
              v-model="filters.lembaga"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Semua Lembaga</option>
              <option value="BAN-PT">BAN-PT</option>
              <option value="LAM">LAM</option>
              <option value="Internasional">Internasional</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <select
              v-model="filters.is_active"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Semua Status</option>
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th scope="col" class="px-6 py-3">Kode</th>
                <th scope="col" class="px-6 py-3">Nama</th>
                <th scope="col" class="px-6 py-3">Jenis</th>
                <th scope="col" class="px-6 py-3">Lembaga</th>
                <th scope="col" class="px-6 py-3">Tahun</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Aksi</th>
              </tr>
            </thead>
            <tbody v-if="loading">
              <tr>
                <td colspan="7" class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center">
                    <svg class="mr-2 h-5 w-5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat data...
                  </div>
                </td>
              </tr>
            </tbody>
            <tbody v-else-if="instrumens.length === 0">
              <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                  Tidak ada data instrumen
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr
                v-for="instrumen in instrumens"
                :key="instrumen.id"
                class="border-b bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600"
              >
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                  {{ instrumen.kode }}
                </td>
                <td class="px-6 py-4">{{ instrumen.nama }}</td>
                <td class="px-6 py-4">{{ instrumen.jenis_label }}</td>
                <td class="px-6 py-4">{{ instrumen.lembaga }}</td>
                <td class="px-6 py-4">{{ instrumen.tahun_berlaku }}</td>
                <td class="px-6 py-4">
                  <button
                    @click="toggleStatus(instrumen)"
                    :class="[
                      'rounded-full px-3 py-1 text-xs font-medium',
                      instrumen.is_active
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                    ]"
                  >
                    {{ instrumen.is_active ? 'Aktif' : 'Nonaktif' }}
                  </button>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center space-x-3">
                    <router-link
                      :to="`/akreditasi/instrumen/${instrumen.id}/edit`"
                      class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                    >
                      Edit
                    </router-link>
                    <button
                      @click="confirmDelete(instrumen)"
                      class="font-medium text-red-600 hover:underline dark:text-red-500"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="meta.total > 0" class="border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-400">
              Menampilkan <span class="font-medium">{{ meta.from }}</span> sampai
              <span class="font-medium">{{ meta.to }}</span> dari
              <span class="font-medium">{{ meta.total }}</span> hasil
            </div>
            <div class="flex space-x-2">
              <button
                @click="changePage(meta.current_page - 1)"
                :disabled="meta.current_page === 1"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
              >
                Sebelumnya
              </button>
              <button
                @click="changePage(meta.current_page + 1)"
                :disabled="meta.current_page === meta.last_page"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
              >
                Selanjutnya
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import axios from 'axios'

const router = useRouter()

const instrumens = ref([])
const loading = ref(false)

const filters = reactive({
  search: '',
  jenis: '',
  lembaga: '',
  is_active: '',
})

const meta = ref({
  current_page: 1,
  from: 0,
  to: 0,
  total: 0,
  last_page: 1,
})

const fetchInstrumens = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: 15,
      ...filters,
    }

    const response = await axios.get('/api/instrumen-akreditasi', { params })
    if (response.data.success) {
      instrumens.value = response.data.data
      meta.value = response.data.meta
    }
  } catch (error) {
    console.error('Failed to fetch instrumens:', error)
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= meta.value.last_page) {
    fetchInstrumens(page)
  }
}

const toggleStatus = async (instrumen) => {
  if (!confirm(`Apakah Anda yakin ingin ${instrumen.is_active ? 'menonaktifkan' : 'mengaktifkan'} instrumen ini?`)) {
    return
  }

  try {
    const response = await axios.post(`/api/instrumen-akreditasi/${instrumen.id}/toggle-active`)
    if (response.data.success) {
      fetchInstrumens(meta.value.current_page)
    }
  } catch (error) {
    console.error('Failed to toggle status:', error)
    alert('Gagal mengubah status instrumen')
  }
}

const confirmDelete = async (instrumen) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus instrumen "${instrumen.nama}"?`)) {
    return
  }

  try {
    const response = await axios.delete(`/api/instrumen-akreditasi/${instrumen.id}`)
    if (response.data.success) {
      fetchInstrumens(meta.value.current_page)
      alert('Instrumen berhasil dihapus')
    }
  } catch (error) {
    console.error('Failed to delete instrumen:', error)
    alert('Gagal menghapus instrumen')
  }
}

// Watch filters
watch(filters, () => {
  fetchInstrumens(1)
}, { deep: true })

onMounted(() => {
  fetchInstrumens()
})
</script>
