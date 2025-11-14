<template>
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
      <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
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
            <td class="px-6 py-4">{{ periode.jenis_akreditasi }}</td>
            <td class="px-6 py-4">{{ periode.lembaga }}</td>
            <td class="px-6 py-4">
              <span class="rounded px-2 py-1 text-xs font-medium" :class="getStatusClass(periode.status)">
                {{ periode.status }}
              </span>
            </td>
            <td class="px-6 py-4">{{ formatDate(periode.deadline_pengumpulan) }}</td>
            <td class="px-6 py-4">
              <router-link
                :to="`/akreditasi/periode/${periode.id}`"
                class="text-blue-600 hover:underline dark:text-blue-400"
              >
                Detail
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
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

const getStatusClass = (status) => {
  const classes = {
    persiapan: 'bg-gray-100 text-gray-800',
    pengisian: 'bg-blue-100 text-blue-800',
    review: 'bg-yellow-100 text-yellow-800',
    submit: 'bg-purple-100 text-purple-800',
    selesai: 'bg-green-100 text-green-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  fetchPeriodes()
})
</script>
