<template>
  <MainLayout>
    <div>
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Template Management
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Kelola template form untuk butir akreditasi
        </p>
      </div>

      <!-- Stats Cards -->
      <div v-if="stats" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
          <div class="text-sm text-gray-600 dark:text-gray-400">Total Butir</div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</div>
        </div>
        <div class="rounded-lg bg-green-50 p-4 shadow dark:bg-green-900/20">
          <div class="text-sm text-green-600 dark:text-green-400">Dengan Template</div>
          <div class="text-2xl font-bold text-green-900 dark:text-green-300">{{ stats.with_template }}</div>
        </div>
        <div class="rounded-lg bg-orange-50 p-4 shadow dark:bg-orange-900/20">
          <div class="text-sm text-orange-600 dark:text-orange-400">Tanpa Template</div>
          <div class="text-2xl font-bold text-orange-900 dark:text-orange-300">{{ stats.without_template }}</div>
        </div>
        <div class="rounded-lg bg-blue-50 p-4 shadow dark:bg-blue-900/20">
          <div class="text-sm text-blue-600 dark:text-blue-400">Progress</div>
          <div class="text-2xl font-bold text-blue-900 dark:text-blue-300">{{ stats.percentage }}%</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-4 flex flex-col gap-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:flex-row">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari butir (kode/nama)..."
            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          />
        </div>
        <div class="w-full sm:w-48">
          <select
            v-model="filterStatus"
            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="">Semua Butir</option>
            <option value="has_template">Dengan Template</option>
            <option value="no_template">Tanpa Template</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="rounded-lg bg-white p-8 text-center shadow dark:bg-gray-800">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-blue-600 border-r-transparent"></div>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Loading...</p>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Kode
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Nama Butir
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Tipe Template
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-700 dark:text-gray-300">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
            <tr v-if="filteredButirs.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                Tidak ada data
              </td>
            </tr>
            <tr v-for="butir in filteredButirs" :key="butir.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                {{ butir.kode }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                {{ butir.nama }}
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-sm">
                <span v-if="butir.has_template" class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 dark:bg-green-900 dark:text-green-300">
                  ✓ Punya Template
                </span>
                <span v-else class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                  Belum Ada
                </span>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                <span v-if="butir.template_type" class="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                  {{ formatTemplateType(butir.template_type) }}
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <!-- Add/Edit Button -->
                  <router-link
                    :to="`/akreditasi/butir-templates/${butir.id}`"
                    :class="butir.has_template ? 'text-blue-600 hover:text-blue-900 dark:text-blue-400' : 'text-green-600 hover:text-green-900 dark:text-green-400'"
                  >
                    {{ butir.has_template ? 'Edit' : '+ Tambah' }}
                  </router-link>

                  <!-- Copy Button -->
                  <button
                    v-if="!butir.has_template"
                    @click="showCopyModal(butir)"
                    class="text-purple-600 hover:text-purple-900 dark:text-purple-400"
                  >
                    Copy
                  </button>

                  <!-- Delete Button -->
                  <button
                    v-if="butir.has_template"
                    @click="confirmDelete(butir)"
                    class="text-red-600 hover:text-red-900 dark:text-red-400"
                  >
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Copy Template Modal -->
      <div v-if="copyModal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="max-h-[90vh] w-full max-w-md overflow-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
          <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">
            Copy Template ke: {{ copyModal.targetButir?.nama }}
          </h3>

          <div class="mb-4">
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Pilih Butir Sumber:
            </label>
            <select
              v-model="copyModal.sourceButirId"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">-- Pilih Butir --</option>
              <option v-for="butir in butirsWithTemplate" :key="butir.id" :value="butir.id">
                {{ butir.kode }} - {{ butir.nama }} ({{ formatTemplateType(butir.template_type) }})
              </option>
            </select>
          </div>

          <div class="flex gap-3">
            <button
              @click="executeCopy"
              :disabled="!copyModal.sourceButirId"
              class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
            >
              Copy Template
            </button>
            <button
              @click="closeCopyModal"
              class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
            >
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useButirTemplateApi } from '@/composables/useButirTemplateApi'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()
const { loading, getButirTemplates, deleteTemplate, copyTemplate } = useButirTemplateApi()

const butirs = ref([])
const stats = ref(null)
const searchQuery = ref('')
const filterStatus = ref('')

const copyModal = ref({
  show: false,
  targetButir: null,
  sourceButirId: null,
})

// Computed
const filteredButirs = computed(() => {
  let result = butirs.value

  // Filter by search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(b =>
      b.kode.toLowerCase().includes(query) ||
      b.nama.toLowerCase().includes(query)
    )
  }

  // Filter by status
  if (filterStatus.value === 'has_template') {
    result = result.filter(b => b.has_template)
  } else if (filterStatus.value === 'no_template') {
    result = result.filter(b => !b.has_template)
  }

  return result
})

const butirsWithTemplate = computed(() => {
  return butirs.value.filter(b => b.has_template)
})

// Methods
const fetchButirs = async () => {
  try {
    const response = await getButirTemplates()
    butirs.value = response.data
    stats.value = response.stats
  } catch (error) {
    console.error('Error fetching butirs:', error)
  }
}

const formatTemplateType = (type) => {
  const types = {
    table: 'Tabel',
    narrative: 'Narasi',
    checklist: 'Checklist',
    metric: 'Metrik',
    mixed: 'Campuran'
  }
  return types[type] || type
}

const confirmDelete = async (butir) => {
  if (!confirm(`Hapus template dari butir "${butir.nama}"?`)) {
    return
  }

  try {
    await deleteTemplate(butir.id)
    alert('Template berhasil dihapus!')
    await fetchButirs()
  } catch (error) {
    alert('Gagal menghapus template: ' + (error.response?.data?.message || error.message))
  }
}

const showCopyModal = (butir) => {
  copyModal.value = {
    show: true,
    targetButir: butir,
    sourceButirId: null,
  }
}

const closeCopyModal = () => {
  copyModal.value = {
    show: false,
    targetButir: null,
    sourceButirId: null,
  }
}

const executeCopy = async () => {
  try {
    await copyTemplate(copyModal.value.targetButir.id, copyModal.value.sourceButirId)
    alert('Template berhasil dicopy!')
    closeCopyModal()
    await fetchButirs()
  } catch (error) {
    alert('Gagal copy template: ' + (error.response?.data?.message || error.message))
  }
}

onMounted(() => {
  fetchButirs()
})
</script>
