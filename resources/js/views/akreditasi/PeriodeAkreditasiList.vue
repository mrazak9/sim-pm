<template>
  <MainLayout>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Periode Akreditasi</h1>
      <router-link
        to="/akreditasi/periode/create"
        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
      >
        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Periode
      </router-link>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
    </div>

    <div v-else class="rounded-lg bg-white shadow dark:bg-gray-800">
      <!-- Empty State -->
      <div v-if="periodes.length === 0" class="py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-white">Belum ada periode akreditasi</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Mulai dengan membuat periode akreditasi baru</p>
        <div class="mt-6">
          <router-link
            to="/akreditasi/periode/create"
            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Periode
          </router-link>
        </div>
      </div>

      <!-- Table -->
      <table v-else class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
        <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th class="px-6 py-3">Nama Periode</th>
            <th class="px-6 py-3">Jenis</th>
            <th class="px-6 py-3">Lembaga</th>
            <th class="px-6 py-3">Status</th>
            <th class="px-6 py-3">Deadline</th>
            <th class="px-6 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="periode in periodes"
            :key="periode.id"
            class="border-b hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-600"
          >
            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
              {{ periode.nama }}
            </td>
            <td class="px-6 py-4 capitalize">
              {{ periode.jenis_akreditasi === 'institusi' ? 'Institusi' : 'Program Studi' }}
            </td>
            <td class="px-6 py-4">{{ periode.lembaga }}</td>
            <td class="px-6 py-4">
              <span class="rounded px-2 py-1 text-xs font-medium" :class="getStatusClass(periode.status)">
                {{ getStatusLabel(periode.status) }}
              </span>
            </td>
            <td class="px-6 py-4">{{ formatDate(periode.deadline_pengumpulan) }}</td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <router-link
                  :to="`/akreditasi/periode/${periode.id}`"
                  class="text-blue-600 hover:underline dark:text-blue-400"
                >
                  Detail
                </router-link>
                <router-link
                  :to="`/akreditasi/periode/${periode.id}/edit`"
                  class="text-yellow-600 hover:underline dark:text-yellow-400"
                >
                  Edit
                </router-link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue'
import { ref, onMounted } from 'vue'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'

const { loading, getPeriodeList } = useAkreditasiApi()
const periodes = ref([])

const fetchPeriodes = async () => {
  try {
    const response = await getPeriodeList()
    periodes.value = response.data.data || []
  } catch (error) {
    console.error('Error:', error)
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
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

const getStatusClass = (status) => {
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
  fetchPeriodes()
})
</script>
