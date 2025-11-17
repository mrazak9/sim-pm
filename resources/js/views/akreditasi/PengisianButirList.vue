<template>
  <MainLayout>
    <div>
      <!-- Header -->
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengisian Butir Akreditasi</h1>
          <p v-if="periode" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Periode: {{ periode.nama }} - {{ periode.jenis_akreditasi }}
          </p>
        </div>
        <router-link
          :to="`/akreditasi/periode/${periodeId}`"
          class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
        >
          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Kembali ke Detail Periode
        </router-link>
      </div>

      <!-- Progress Summary -->
      <div v-if="summary" class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
          <div class="flex items-center">
            <div class="flex-shrink-0 rounded-full bg-blue-100 p-3 dark:bg-blue-900">
              <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Butir</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.total_butir || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
          <div class="flex items-center">
            <div class="flex-shrink-0 rounded-full bg-green-100 p-3 dark:bg-green-900">
              <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sudah Diisi</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.total_filled || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
          <div class="flex items-center">
            <div class="flex-shrink-0 rounded-full bg-yellow-100 p-3 dark:bg-yellow-900">
              <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Belum Diisi</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.total_unfilled || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
          <div class="flex items-center">
            <div class="flex-shrink-0 rounded-full bg-purple-100 p-3 dark:bg-purple-900">
              <svg class="h-6 w-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Progress</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ summary.completion_percentage || 0 }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-6 rounded-lg bg-white p-4 shadow dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Kategori</label>
            <select
              v-model="filters.kategori"
              @change="fetchButirList"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Semua Kategori</option>
              <option v-for="kat in availableKategori" :key="kat" :value="kat">{{ kat }}</option>
            </select>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Status Pengisian</label>
            <select
              v-model="filters.status"
              @change="fetchButirList"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Semua Status</option>
              <option value="belum_diisi">Belum Diisi</option>
              <option value="draft">Draft</option>
              <option value="submitted">Submitted</option>
              <option value="review">Review</option>
              <option value="revision">Revision</option>
              <option value="approved">Approved</option>
            </select>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Pencarian</label>
            <input
              type="text"
              v-model="filters.search"
              @input="handleSearch"
              placeholder="Cari kode atau nama butir..."
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
            />
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="h-12 w-12 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
        <div class="flex items-start">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-400">{{ error }}</h3>
          </div>
        </div>
      </div>

      <!-- Butir List Table -->
      <div v-else class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th scope="col" class="px-6 py-3">Kode</th>
                <th scope="col" class="px-6 py-3">Nama Butir</th>
                <th scope="col" class="px-6 py-3">Kategori</th>
                <th scope="col" class="px-6 py-3">Bobot</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Progress</th>
                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="butir in butirList"
                :key="butir.id"
                class="border-b bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
              >
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                  {{ butir.kode }}
                </td>
                <td class="px-6 py-4">
                  <div class="max-w-md">
                    <p class="font-medium text-gray-900 dark:text-white">{{ butir.nama }}</p>
                    <p v-if="butir.deskripsi" class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                      {{ butir.deskripsi }}
                    </p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                    {{ butir.kategori || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  {{ butir.bobot || 0 }}
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="getStatusBadgeClass(butir.pengisian_status)"
                  >
                    {{ getStatusLabel(butir.pengisian_status) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="mr-2 h-2 w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                      <div
                        class="h-full rounded-full transition-all"
                        :class="getProgressBarClass(butir.completion_percentage)"
                        :style="{ width: (butir.completion_percentage || 0) + '%' }"
                      ></div>
                    </div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">{{ butir.completion_percentage || 0 }}%</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <router-link
                    v-if="!butir.pengisian_id"
                    :to="`/akreditasi/pengisian/create?periode_id=${periodeId}&butir_id=${butir.id}`"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700"
                  >
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Isi Butir
                  </router-link>
                  <router-link
                    v-else
                    :to="`/akreditasi/pengisian/${butir.pengisian_id}/edit`"
                    class="inline-flex items-center rounded-lg bg-yellow-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-yellow-700"
                  >
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                  </router-link>
                </td>
              </tr>
              <tr v-if="!butirList.length">
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                    <svg class="mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm">Tidak ada butir akreditasi yang tersedia</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'
import MainLayout from '@/layouts/MainLayout.vue'

const route = useRoute()
const { loading, error, getPeriodeDetail, getButirList, getPengisianList, getPengisianSummary } = useAkreditasiApi()

const periodeId = computed(() => route.params.periodeId)
const periode = ref(null)
const butirList = ref([])
const summary = ref(null)
const availableKategori = ref([])

const filters = ref({
  kategori: '',
  status: '',
  search: ''
})

let searchTimeout = null

const fetchPeriodeDetail = async () => {
  try {
    const response = await getPeriodeDetail(periodeId.value)
    periode.value = response.data
  } catch (err) {
    console.error('Error fetching periode detail:', err)
  }
}

const fetchSummary = async () => {
  try {
    const response = await getPengisianSummary(periodeId.value)
    summary.value = response.data
  } catch (err) {
    console.error('Error fetching summary:', err)
  }
}

const fetchButirList = async () => {
  try {
    console.log('Fetching butir list for periode:', periodeId.value)
    console.log('Instrumen:', periode.value?.instrumen)

    // Fetch all butir for the current instrumen
    const butirResponse = await getButirList({
      instrumen: periode.value?.instrumen,
      per_page: 'all'
    })

    console.log('Butir response:', butirResponse)

    // Fetch pengisian data for this periode
    const pengisianResponse = await getPengisianList({
      periode_akreditasi_id: periodeId.value,
      per_page: 'all'
    })

    console.log('Pengisian response:', pengisianResponse)

    // Merge butir with pengisian data
    const pengisianMap = {}
    if (pengisianResponse.data?.data) {
      pengisianResponse.data.data.forEach(pengisian => {
        pengisianMap[pengisian.butir_akreditasi_id] = pengisian
      })
    } else if (Array.isArray(pengisianResponse.data)) {
      // Handle non-paginated response
      pengisianResponse.data.forEach(pengisian => {
        pengisianMap[pengisian.butir_akreditasi_id] = pengisian
      })
    }

    // Handle both paginated and non-paginated responses
    let butirData = []
    if (butirResponse.data?.data) {
      butirData = butirResponse.data.data
    } else if (Array.isArray(butirResponse.data)) {
      butirData = butirResponse.data
    }

    console.log('Butir data:', butirData.length, 'items')
    console.log('Pengisian map:', Object.keys(pengisianMap).length, 'items')

    // Add pengisian info to each butir
    butirData = butirData.map(butir => {
      const pengisian = pengisianMap[butir.id]
      return {
        ...butir,
        pengisian_id: pengisian?.id || null,
        pengisian_status: pengisian?.status || 'belum_diisi',
        completion_percentage: pengisian?.completion_percentage || 0
      }
    })

    // Apply filters
    if (filters.value.kategori) {
      butirData = butirData.filter(b => b.kategori === filters.value.kategori)
    }

    if (filters.value.status) {
      butirData = butirData.filter(b => b.pengisian_status === filters.value.status)
    }

    if (filters.value.search) {
      const search = filters.value.search.toLowerCase()
      butirData = butirData.filter(b =>
        b.kode.toLowerCase().includes(search) ||
        b.nama.toLowerCase().includes(search)
      )
    }

    butirList.value = butirData

    // Extract unique kategori
    const kategoriSet = new Set(butirData.map(b => b.kategori).filter(Boolean))
    availableKategori.value = Array.from(kategoriSet).sort()
  } catch (err) {
    console.error('Error fetching butir list:', err)
  }
}

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchButirList()
  }, 300)
}

const getStatusLabel = (status) => {
  const labels = {
    belum_diisi: 'Belum Diisi',
    draft: 'Draft',
    submitted: 'Submitted',
    review: 'Review',
    revision: 'Perlu Revisi',
    approved: 'Approved'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    belum_diisi: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    draft: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    submitted: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    review: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    revision: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const getProgressBarClass = (percentage) => {
  if (percentage >= 100) return 'bg-green-600'
  if (percentage >= 75) return 'bg-blue-600'
  if (percentage >= 50) return 'bg-yellow-600'
  if (percentage >= 25) return 'bg-orange-600'
  return 'bg-red-600'
}

onMounted(async () => {
  await fetchPeriodeDetail()
  await fetchSummary()
  await fetchButirList()
})
</script>
