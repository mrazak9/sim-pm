<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Daftar Butir Akreditasi
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              <span v-if="butirMode === 'template'" class="inline-flex items-center">
                <svg class="mr-1 h-4 w-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                </svg>
                Mode Template - Master butir untuk semua periode
              </span>
              <span v-else class="inline-flex items-center">
                <svg class="mr-1 h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                </svg>
                Mode Per-Periode - Butir untuk periode spesifik
              </span>
            </p>
          </div>
          <button
            @click="openCreateForm"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Butir
          </button>
        </div>
      </div>

      <!-- Mode Toggle -->
      <div class="border-b border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900/50">
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mode:</span>
          <div class="flex rounded-lg bg-white dark:bg-gray-800 p-1 shadow-sm">
            <button
              @click="changeButirMode('template')"
              :class="[
                'flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                butirMode === 'template'
                  ? 'bg-purple-600 text-white shadow'
                  : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
              ]"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Template
            </button>
            <button
              @click="changeButirMode('periode')"
              :class="[
                'flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors',
                butirMode === 'periode'
                  ? 'bg-blue-600 text-white shadow'
                  : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
              ]"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Per-Periode
            </button>
          </div>
          <div v-if="butirMode === 'template'" class="ml-auto">
            <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900/50 dark:text-purple-200">
              <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              Template butir dapat di-copy ke berbagai periode
            </span>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4" :class="butirMode === 'periode' ? 'md:grid-cols-5' : 'md:grid-cols-4'">
          <!-- Periode Filter (only in per-periode mode) -->
          <div v-if="butirMode === 'periode'">
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Periode</label>
            <select
              v-model="filters.periode_akreditasi_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              @change="fetchButirs"
            >
              <option value="">Semua Periode</option>
              <option v-for="periode in periodeList" :key="periode.id" :value="periode.id">
                {{ periode.nama }}
              </option>
            </select>
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Instrumen</label>
            <select
              v-model="filters.instrumen"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              @change="fetchButirs"
            >
              <option value="">Semua Instrumen</option>
              <option v-for="ins in instrumenList" :key="ins" :value="ins">{{ ins }}</option>
            </select>
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Kategori</label>
            <select
              v-model="filters.kategori"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              @change="fetchButirs"
            >
              <option value="">Semua Kategori</option>
              <option v-for="kat in kategoriList" :key="kat" :value="kat">{{ kat }}</option>
            </select>
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Wajib</label>
            <select
              v-model="filters.is_mandatory"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              @change="fetchButirs"
            >
              <option value="">Semua</option>
              <option value="1">Wajib</option>
              <option value="0">Opsional</option>
            </select>
          </div>
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Cari</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari butir..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>
        </div>
      </div>

      <!-- View Toggle -->
      <div class="border-b border-gray-200 px-6 py-3 dark:border-gray-700">
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tampilan:</span>
          <button
            @click="viewMode = 'tree'"
            :class="[
              'rounded px-3 py-1 text-sm font-medium',
              viewMode === 'tree'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            Hierarki
          </button>
          <button
            @click="viewMode = 'flat'"
            :class="[
              'rounded px-3 py-1 text-sm font-medium',
              viewMode === 'flat'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            ]"
          >
            Datar
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
      </div>

      <!-- Hierarchical Tree View -->
      <div v-else-if="viewMode === 'tree'" class="p-6">
        <div v-if="treeData.length === 0" class="py-12 text-center text-gray-500 dark:text-gray-400">
          Tidak ada data butir akreditasi
        </div>
        <div v-else class="space-y-2">
          <TreeNode
            v-for="node in treeData"
            :key="node.id"
            :node="node"
            :level="0"
            @edit="editButir"
            @delete="confirmDelete"
            @add-child="addChild"
          />
        </div>
      </div>

      <!-- Flat Table View -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">Kode</th>
              <th scope="col" class="px-6 py-3">Nama Butir</th>
              <th scope="col" class="px-6 py-3">Kategori</th>
              <th scope="col" class="px-6 py-3">Instrumen</th>
              <th scope="col" class="px-6 py-3">Bobot</th>
              <th scope="col" class="px-6 py-3">Wajib</th>
              <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="butirs.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada data butir akreditasi
              </td>
            </tr>
            <tr
              v-else
              v-for="butir in butirs"
              :key="butir.id"
              class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
            >
              <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                {{ butir.kode }}
              </td>
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white">{{ butir.nama }}</p>
                <p v-if="butir.deskripsi" class="text-xs text-gray-500 dark:text-gray-400">
                  {{ truncate(butir.deskripsi, 60) }}
                </p>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                  {{ butir.kategori || '-' }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ butir.instrumen }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ butir.bobot || '-' }}
              </td>
              <td class="px-6 py-4">
                <span
                  :class="[
                    'inline-flex rounded-full px-3 py-1 text-xs font-medium',
                    butir.is_mandatory
                      ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                      : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                  ]"
                >
                  {{ butir.is_mandatory ? 'Wajib' : 'Opsional' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    @click="editButir(butir)"
                    class="text-yellow-600 hover:text-yellow-700 dark:text-yellow-400 dark:hover:text-yellow-300"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(butir)"
                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    title="Hapus"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="showDeleteModal = false"
    >
      <div class="rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800" style="max-width: 400px">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Hapus</h3>
        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
          Apakah Anda yakin ingin menghapus butir "{{ butirToDelete?.nama }}"?
          <span v-if="butirToDelete?.has_children" class="mt-2 block text-red-600 dark:text-red-400">
            Perhatian: Butir ini memiliki sub-butir yang akan ikut terhapus.
          </span>
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            @click="deleteButir"
            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
          >
            Hapus
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import TreeNode from '@/components/TreeNode.vue'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'

const router = useRouter()
const { loading, getButirList, getInstrumenList, getKategoriList, getPeriodeList, deleteButir: deleteButirApi } = useAkreditasiApi()

const butirs = ref([])
const instrumenList = ref([])
const kategoriList = ref([])
const periodeList = ref([])
const viewMode = ref('tree')
const butirMode = ref('template') // 'template' or 'periode'
const showDeleteModal = ref(false)
const butirToDelete = ref(null)

const filters = ref({
  instrumen: '',
  kategori: '',
  is_mandatory: '',
  periode_akreditasi_id: '',
  search: '',
})

// Build tree structure from flat data
const treeData = computed(() => {
  const filteredButirs = butirs.value
  const map = new Map()
  const roots = []

  // Create a map of all butirs
  filteredButirs.forEach(butir => {
    map.set(butir.id, { ...butir, children: [] })
  })

  // Build the tree
  filteredButirs.forEach(butir => {
    const node = map.get(butir.id)
    if (butir.parent_id && map.has(butir.parent_id)) {
      map.get(butir.parent_id).children.push(node)
    } else {
      roots.push(node)
    }
  })

  return roots
})

const fetchButirs = async () => {
  try {
    const params = { ...filters.value, per_page: 'all' }

    // Apply template_only filter in template mode
    if (butirMode.value === 'template') {
      params.template_only = true
      delete params.periode_akreditasi_id
    }

    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key]
    })

    const response = await getButirList(params)
    butirs.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch butir akreditasi:', error)
  }
}

const fetchInstrumen = async () => {
  try {
    const response = await getInstrumenList()
    instrumenList.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch instrumen:', error)
  }
}

const fetchKategori = async () => {
  try {
    const instrumen = filters.value.instrumen || '4.0'
    const response = await getKategoriList(instrumen)
    kategoriList.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch kategori:', error)
  }
}

const fetchPeriodes = async () => {
  try {
    const response = await getPeriodeList({ per_page: 100 })
    periodeList.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch periodes:', error)
  }
}

const changeButirMode = (mode) => {
  butirMode.value = mode
  // Clear periode filter when switching to template mode
  if (mode === 'template') {
    filters.value.periode_akreditasi_id = ''
  }
  fetchButirs()
}

let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchButirs()
  }, 300)
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const openCreateForm = () => {
  router.push({ name: 'butir-akreditasi-create' })
}

const addChild = (parentButir) => {
  router.push({
    name: 'butir-akreditasi-create',
    query: { parent_id: parentButir.id }
  })
}

const editButir = (butir) => {
  router.push({ name: 'butir-akreditasi-edit', params: { id: butir.id } })
}

const confirmDelete = (butir) => {
  butirToDelete.value = butir
  showDeleteModal.value = true
}

const deleteButir = async () => {
  try {
    await deleteButirApi(butirToDelete.value.id)
    showDeleteModal.value = false
    await fetchButirs()
  } catch (error) {
    console.error('Failed to delete butir:', error)
    alert('Gagal menghapus butir akreditasi')
  }
}

onMounted(async () => {
  await fetchInstrumen()
  await fetchKategori()
  await fetchPeriodes()
  await fetchButirs()
})
</script>
