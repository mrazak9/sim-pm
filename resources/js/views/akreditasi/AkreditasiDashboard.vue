<template>
  <div class="akreditasi-dashboard">
    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard Akreditasi</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Monitoring dan manajemen periode akreditasi institusi & program studi
        </p>
      </div>
      <router-link
        to="/akreditasi/periode/create"
        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600"
      >
        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Periode Baru
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="h-12 w-12 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
    </div>

    <!-- Main Content -->
    <div v-else>
      <!-- Stats Cards -->
      <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
          <div class="flex items-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
              <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Periode</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
          <div class="flex items-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900">
              <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sedang Berjalan</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.ongoing || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
          <div class="flex items-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
              <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Selesai</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.completed || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
          <div class="flex items-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
              <svg class="h-6 w-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Institusi</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.institusi || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Periods List -->
      <div class="rounded-lg bg-white shadow dark:bg-gray-800">
        <div class="border-b border-gray-200 p-6 dark:border-gray-700">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Periode Aktif</h2>
        </div>
        <div class="p-6">
          <div v-if="periodes.length === 0" class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">Belum ada periode akreditasi aktif</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="periode in periodes"
              :key="periode.id"
              class="rounded-lg border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ periode.nama }}</h3>
                    <span
                      class="rounded-full px-2 py-1 text-xs font-medium"
                      :class="getStatusBadgeClass(periode.status)"
                    >
                      {{ getStatusLabel(periode.status) }}
                    </span>
                  </div>
                  <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <span class="flex items-center gap-1">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                      {{ periode.jenis_akreditasi === 'institusi' ? 'Institusi' : periode.program_studi?.nama || 'Program Studi' }}
                    </span>
                    <span class="flex items-center gap-1">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      Deadline: {{ formatDate(periode.deadline_pengumpulan) }}
                    </span>
                    <span v-if="periode.sisa_hari !== null" class="flex items-center gap-1">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      {{ periode.sisa_hari }} hari lagi
                    </span>
                  </div>
                  <div v-if="periode.progress_persentase !== undefined" class="mt-3">
                    <div class="flex items-center justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-400">Progress Pengisian</span>
                      <span class="font-medium text-gray-900 dark:text-white">{{ periode.progress_persentase }}%</span>
                    </div>
                    <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                      <div
                        class="h-full rounded-full bg-blue-600 transition-all"
                        :style="{ width: periode.progress_persentase + '%' }"
                      ></div>
                    </div>
                  </div>
                </div>
                <router-link
                  :to="`/akreditasi/periode/${periode.id}`"
                  class="ml-4 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                  Lihat Detail
                </router-link>
              </div>
            </div>
          </div>

          <div v-if="periodes.length > 0" class="mt-6 text-center">
            <router-link
              to="/akreditasi/periode"
              class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
            >
              Lihat Semua Periode →
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'

const { loading, getPeriodeList } = useAkreditasiApi()

const stats = ref({
  total: 0,
  ongoing: 0,
  completed: 0,
  institusi: 0,
})

const periodes = ref([])

const fetchDashboardData = async () => {
  try {
    // Fetch active periods
    const response = await getPeriodeList({
      status: 'pengisian,review,submit',
      per_page: 5,
      sort_by: 'deadline_pengumpulan',
      sort_order: 'asc',
    })

    periodes.value = response.data.data || []

    // Calculate stats
    const allPeriodesResponse = await getPeriodeList({ per_page: 100 })
    const allPeriodes = allPeriodesResponse.data.data || []

    stats.value = {
      total: allPeriodes.length,
      ongoing: allPeriodes.filter(p => ['persiapan', 'pengisian', 'review', 'submit'].includes(p.status)).length,
      completed: allPeriodes.filter(p => p.status === 'selesai').length,
      institusi: allPeriodes.filter(p => p.jenis_akreditasi === 'institusi').length,
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

const getStatusLabel = (status) => {
  const labels = {
    persiapan: 'Persiapan',
    pengisian: 'Pengisian',
    review: 'Review',
    submit: 'Submitted',
    visitasi: 'Visitasi',
    selesai: 'Selesai',
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    persiapan: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    pengisian: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    review: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    submit: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    visitasi: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    selesai: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

onMounted(() => {
  fetchDashboardData()
})
</script>
