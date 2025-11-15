<template>
  <MainLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Simulasi Penilaian Akreditasi
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Lihat prediksi nilai dan rekomendasi untuk meningkatkan skor akreditasi
          </p>
        </div>

        <!-- Refresh Button -->
        <button
          v-if="periodeId"
          @click="refreshScoring"
          :disabled="refreshing"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <svg
            class="mr-2 h-4 w-4"
            :class="{ 'animate-spin': refreshing }"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          {{ refreshing ? 'Memuat ulang...' : 'Refresh Data' }}
        </button>
      </div>

      <!-- Breadcrumb -->
      <nav class="mt-4 flex" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <router-link to="/dashboard" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
              Dashboard
            </router-link>
          </li>
          <li>
            <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
          </li>
          <li>
            <router-link to="/akreditasi" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
              Akreditasi
            </router-link>
          </li>
          <li>
            <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
          </li>
          <li class="text-gray-700 dark:text-gray-300 font-medium">
            Simulasi Penilaian
          </li>
        </ol>
      </nav>
    </div>

    <!-- Periode Selection -->
    <div v-if="!periodeId" class="mb-6">
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Pilih Periode Akreditasi
        </h2>

        <div v-if="loadingPeriodes" class="flex items-center justify-center py-8">
          <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>

        <div v-else-if="periodes.length === 0" class="text-center py-8">
          <p class="text-gray-500 dark:text-gray-400">
            Tidak ada periode akreditasi yang tersedia
          </p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <button
            v-for="periode in periodes"
            :key="periode.id"
            @click="selectPeriode(periode.id)"
            class="text-left p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all"
          >
            <h3 class="font-medium text-gray-900 dark:text-white">
              {{ periode.nama }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ periode.tahun_akademik }}
            </p>
            <div class="flex items-center mt-2">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="getStatusClass(periode.status)"
              >
                {{ periode.status }}
              </span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Scoring Simulation Panel -->
    <div v-else>
      <div class="mb-4">
        <button
          @click="clearSelection"
          class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
        >
          <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Kembali ke Pilihan Periode
        </button>
      </div>

      <ScoringSimulationPanel
        :key="scoringKey"
        :periode-id="periodeId"
      />
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import MainLayout from '@/layouts/MainLayout.vue'
import ScoringSimulationPanel from '@/components/akreditasi/ScoringSimulationPanel.vue'

const route = useRoute()
const router = useRouter()

const periodeId = ref(null)
const periodes = ref([])
const loadingPeriodes = ref(false)
const refreshing = ref(false)
const scoringKey = ref(0)

const loadPeriodes = async () => {
  loadingPeriodes.value = true
  try {
    const response = await axios.get('/api/periode-akreditasi')
    if (response.data.success) {
      periodes.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to load periodes:', error)
  } finally {
    loadingPeriodes.value = false
  }
}

const selectPeriode = (id) => {
  periodeId.value = id
  router.push({ query: { periode: id } })
}

const clearSelection = () => {
  periodeId.value = null
  router.push({ query: {} })
}

const refreshScoring = () => {
  refreshing.value = true
  scoringKey.value++
  setTimeout(() => {
    refreshing.value = false
  }, 1000)
}

const getStatusClass = (status) => {
  const classes = {
    'aktif': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'selesai': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

onMounted(() => {
  // Check if periode is in query params
  if (route.query.periode) {
    periodeId.value = parseInt(route.query.periode)
  } else {
    loadPeriodes()
  }
})
</script>
