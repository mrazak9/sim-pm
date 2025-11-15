<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
        <svg class="h-5 w-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Riwayat Versi
      </h3>
      <button
        @click="loadVersionHistory"
        class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200"
      >
        <svg class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Refresh
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-8">
      <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <!-- Version Timeline -->
    <div v-else-if="versions.length > 0" class="relative">
      <!-- Vertical Line -->
      <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

      <!-- Version Items -->
      <div v-for="(version, index) in versions" :key="version.id" class="relative pb-8 last:pb-0">
        <!-- Timeline Dot -->
        <div :class="getTimelineDotClass(version.change_type)" class="absolute left-5 w-6 h-6 rounded-full border-4 border-white dark:border-gray-900 flex items-center justify-center">
          <svg v-if="version.change_type === 'created'" class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
          </svg>
          <svg v-else-if="version.change_type === 'approved'" class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
          <svg v-else class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
          </svg>
        </div>

        <!-- Version Card -->
        <div class="ml-16 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
          <!-- Card Header -->
          <div class="flex items-start justify-between mb-2">
            <div>
              <div class="flex items-center space-x-2">
                <span class="font-semibold text-gray-900 dark:text-white">
                  Versi {{ version.version_number }}
                </span>
                <span :class="getChangeTypeBadgeClass(version.change_type)" class="px-2 py-0.5 rounded text-xs font-medium">
                  {{ getChangeTypeLabel(version.change_type) }}
                </span>
                <span v-if="isLatestVersion(index)" class="px-2 py-0.5 bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300 rounded text-xs font-medium">
                  Terbaru
                </span>
              </div>
              <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mt-1">
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ version.user?.name || 'Unknown' }}
                <span class="mx-2">•</span>
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ formatDateTime(version.created_at) }}
              </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-2">
              <button
                @click="viewVersion(version)"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200"
                title="Lihat Detail"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button
                v-if="!isLatestVersion(index) && canRestore"
                @click="confirmRestore(version)"
                class="text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200"
                title="Restore ke versi ini"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Version Details -->
          <div class="space-y-2">
            <div v-if="version.change_summary" class="text-sm text-gray-700 dark:text-gray-300">
              {{ version.change_summary }}
            </div>

            <div class="flex items-center space-x-4 text-xs text-gray-600 dark:text-gray-400">
              <span>Status: <span :class="getStatusBadgeClass(version.status)">{{ getStatusLabel(version.status) }}</span></span>
              <span>Completion: <span class="font-semibold">{{ version.completion_percentage }}%</span></span>
              <span v-if="version.konten">Words: {{ getWordCount(version.konten) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading" class="text-center py-12 text-gray-500 dark:text-gray-400">
      <svg class="h-12 w-12 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p>Belum ada riwayat versi</p>
    </div>

    <!-- View Version Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="showViewModal = false">
      <div class="bg-white dark:bg-gray-800 rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
              Detail Versi {{ selectedVersion?.version_number }}
            </h4>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="selectedVersion" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Konten:</label>
              <div class="prose dark:prose-invert max-w-none bg-gray-50 dark:bg-gray-900 p-4 rounded" v-html="selectedVersion.konten || '-'"></div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                <span class="ml-2" :class="getStatusBadgeClass(selectedVersion.status)">
                  {{ getStatusLabel(selectedVersion.status) }}
                </span>
              </div>
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Completion:</span>
                <span class="ml-2 font-semibold">{{ selectedVersion.completion_percentage }}%</span>
              </div>
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">User:</span>
                <span class="ml-2">{{ selectedVersion.user?.name || '-' }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Tanggal:</span>
                <span class="ml-2">{{ formatDateTime(selectedVersion.created_at) }}</span>
              </div>
            </div>

            <div v-if="selectedVersion.notes">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan:</label>
              <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 p-3 rounded">
                {{ selectedVersion.notes }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div v-if="showRestoreModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="showRestoreModal = false">
      <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Konfirmasi Restore</h4>
        <p class="text-gray-700 dark:text-gray-300 mb-6">
          Apakah Anda yakin ingin me-restore pengisian ke Versi {{ selectedVersion?.version_number }}?
          Versi saat ini akan disimpan sebagai backup.
        </p>
        <div class="flex space-x-3">
          <button
            @click="restoreVersion"
            :disabled="restoring"
            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="!restoring">Ya, Restore</span>
            <span v-else>Restoring...</span>
          </button>
          <button
            @click="showRestoreModal = false"
            :disabled="restoring"
            class="flex-1 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 font-medium py-2 px-4 rounded-md transition-colors"
          >
            Batal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  pengisianButirId: {
    type: Number,
    required: true
  },
  canRestore: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['version-restored'])

const loading = ref(false)
const restoring = ref(false)
const versions = ref([])
const selectedVersion = ref(null)
const showViewModal = ref(false)
const showRestoreModal = ref(false)

const loadVersionHistory = async () => {
  loading.value = true

  try {
    const response = await axios.get(`/api/pengisian-butir/${props.pengisianButirId}/version-history`)

    if (response.data.success) {
      versions.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to load version history:', error)
  } finally {
    loading.value = false
  }
}

const viewVersion = (version) => {
  selectedVersion.value = version
  showViewModal.value = true
}

const confirmRestore = (version) => {
  selectedVersion.value = version
  showRestoreModal.value = true
}

const restoreVersion = async () => {
  if (!selectedVersion.value) return

  restoring.value = true

  try {
    const response = await axios.post(
      `/api/pengisian-butir/${props.pengisianButirId}/restore-version`,
      { version_number: selectedVersion.value.version_number }
    )

    if (response.data.success) {
      showRestoreModal.value = false
      emit('version-restored', response.data.data)
      await loadVersionHistory()
    }
  } catch (error) {
    console.error('Failed to restore version:', error)
    alert(error.response?.data?.message || 'Gagal restore versi')
  } finally {
    restoring.value = false
  }
}

const isLatestVersion = (index) => {
  return index === 0
}

const getTimelineDotClass = (changeType) => {
  const classes = {
    'created': 'bg-blue-500',
    'updated': 'bg-gray-500',
    'submitted': 'bg-purple-500',
    'approved': 'bg-green-500',
    'revision': 'bg-orange-500',
    'restored': 'bg-yellow-500',
    'before_restore': 'bg-gray-400'
  }
  return classes[changeType] || 'bg-gray-500'
}

const getChangeTypeBadgeClass = (changeType) => {
  const classes = {
    'created': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'updated': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'submitted': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    'approved': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'revision': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    'restored': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
  }
  return classes[changeType] || 'bg-gray-100 text-gray-800'
}

const getChangeTypeLabel = (changeType) => {
  const labels = {
    'created': 'Dibuat',
    'updated': 'Diperbarui',
    'submitted': 'Diajukan',
    'approved': 'Disetujui',
    'revision': 'Revisi',
    'restored': 'Direstore',
    'before_restore': 'Before Restore'
  }
  return labels[changeType] || changeType
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'approved': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'review': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'submitted': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    'revision': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }
  return 'px-2 py-0.5 rounded text-xs font-medium ' + (classes[status] || 'bg-gray-100 text-gray-800')
}

const getStatusLabel = (status) => {
  const labels = {
    'approved': 'Disetujui',
    'review': 'Review',
    'submitted': 'Diajukan',
    'revision': 'Revisi',
    'draft': 'Draft'
  }
  return labels[status] || status
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getWordCount = (html) => {
  if (!html) return 0
  const text = html.replace(/<[^>]*>/g, '')
  return text.split(/\s+/).filter(word => word.length > 0).length
}

onMounted(() => {
  loadVersionHistory()
})

defineExpose({
  refresh: loadVersionHistory
})
</script>
