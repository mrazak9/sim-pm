<template>
  <MainLayout>
    <div>
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

      <!-- Content -->
      <div v-else-if="periode">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ periode.nama }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              Detail periode akreditasi {{ periode.jenis_akreditasi }}
            </p>
          </div>
          <div class="flex gap-2">
            <button
              @click="handleExportPDF"
              :disabled="exporting"
              class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
            >
              <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              {{ exporting ? 'Exporting...' : 'Export PDF' }}
            </button>
            <button
              @click="handleExportExcel"
              :disabled="exporting"
              class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"
            >
              <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              {{ exporting ? 'Exporting...' : 'Export Excel' }}
            </button>
            <router-link
              :to="`/akreditasi/periode/${periode.id}/edit`"
              class="inline-flex items-center rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-700"
            >
              <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </router-link>
            <router-link
              to="/akreditasi/periode"
              class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
            >
              Kembali
            </router-link>
          </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
          <span
            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
            :class="getStatusBadgeClass(periode.status)"
          >
            {{ getStatusLabel(periode.status) }}
          </span>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <!-- Left Column - Details -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Informasi Umum -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
              <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Informasi Umum</h2>
              <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Akreditasi</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white capitalize">
                    {{ periode.jenis_akreditasi === 'institusi' ? 'Institusi' : 'Program Studi' }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Lembaga</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ periode.lembaga }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Instrumen</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ periode.instrumen || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenjang</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ periode.jenjang || '-' }}</dd>
                </div>
              </dl>
            </div>

            <!-- Timeline -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
              <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Timeline</h2>
              <div class="space-y-4">
                <div class="flex items-start">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Tanggal Mulai</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatDate(periode.tanggal_mulai) }}</p>
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Deadline Pengumpulan</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatDate(periode.deadline_pengumpulan) }}</p>
                    <p v-if="periode.sisa_hari !== null" class="mt-1 text-xs" :class="periode.sisa_hari < 0 ? 'text-red-600' : 'text-green-600'">
                      {{ periode.sisa_hari < 0 ? 'Terlewat' : `${periode.sisa_hari} hari lagi` }}
                    </p>
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Jadwal Visitasi</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatDate(periode.jadwal_visitasi) }}</p>
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Tanggal Berakhir</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ formatDate(periode.tanggal_berakhir) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Keterangan -->
            <div v-if="periode.keterangan" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
              <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Keterangan</h2>
              <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ periode.keterangan }}</p>
            </div>
          </div>

          <!-- Right Column - Statistics -->
          <div class="space-y-6">
            <!-- Progress Card -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
              <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Progress</h2>
              <div class="text-center">
                <div class="mb-2 text-4xl font-bold text-blue-600">{{ periode.progress_persentase || 0 }}%</div>
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">Progress Pengisian</div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                  <div
                    class="h-full rounded-full bg-blue-600 transition-all"
                    :style="{ width: (periode.progress_persentase || 0) + '%' }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
              <h2 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">Aksi Cepat</h2>
              <div class="space-y-2">
                <button
                  type="button"
                  class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                  Lihat Pengisian Butir
                </button>
                <button
                  type="button"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
                >
                  Lihat Dokumen
                </button>
                <button
                  type="button"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
                >
                  Cetak Laporan
                </button>
              </div>
            </div>

            <!-- Info Card -->
            <div class="rounded-lg bg-blue-50 p-6 dark:bg-blue-900/20">
              <div class="flex items-start">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-blue-800 dark:text-blue-400">Informasi</h3>
                  <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    Periode ini dibuat pada {{ formatDateTime(periode.created_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'
import MainLayout from '@/layouts/MainLayout.vue'

const route = useRoute()
const { loading, error, getPeriodeDetail, exportPeriodePDF, exportPeriodeExcel } = useAkreditasiApi()

const periode = ref(null)
const exporting = ref(false)

const fetchPeriodeDetail = async () => {
  try {
    const response = await getPeriodeDetail(route.params.id)
    periode.value = response.data
  } catch (err) {
    console.error('Error fetching periode detail:', err)
  }
}

const handleExportPDF = async () => {
  exporting.value = true
  try {
    const response = await exportPeriodePDF(route.params.id)
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    const filename = `Laporan_Akreditasi_${periode.value.nama.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Export PDF failed:', error)
    alert('Gagal export PDF. Silakan coba lagi.')
  } finally {
    exporting.value = false
  }
}

const handleExportExcel = async () => {
  exporting.value = true
  try {
    const response = await exportPeriodeExcel(route.params.id)
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    const filename = `Laporan_Akreditasi_${periode.value.nama.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.xlsx`
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Export Excel failed:', error)
    alert('Gagal export Excel. Silakan coba lagi.')
  } finally {
    exporting.value = false
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

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
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
  fetchPeriodeDetail()
})
</script>
