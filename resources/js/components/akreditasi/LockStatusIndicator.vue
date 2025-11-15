<template>
  <div v-if="lockStatus.is_locked" class="mb-4">
    <!-- Lock Warning Banner -->
    <div class="border-l-4 border-red-500 bg-red-50 dark:bg-red-900/20 p-4 rounded-r-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <!-- Lock Icon -->
          <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">
            Pengisian Terkunci
          </h3>
          <div class="mt-2 text-sm text-red-700 dark:text-red-400">
            <p>{{ lockStatus.reason }}</p>

            <!-- Deadline Info -->
            <div v-if="lockStatus.deadline" class="mt-2 space-y-1">
              <p class="font-medium">
                📅 Deadline: {{ formatDate(lockStatus.deadline) }}
              </p>
              <p v-if="lockStatus.is_expired" class="text-xs">
                ⏰ Deadline telah terlewati {{ Math.abs(lockStatus.sisa_hari) }} hari yang lalu
              </p>
            </div>
          </div>

          <!-- Contact Admin Button -->
          <div class="mt-3">
            <button
              type="button"
              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-800/50 dark:text-red-300 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
              @click="$emit('contact-admin')"
            >
              <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Hubungi Admin
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Active Status (not locked) -->
  <div v-else-if="showActiveStatus" class="mb-4">
    <div class="border-l-4 border-green-500 bg-green-50 dark:bg-green-900/20 p-3 rounded-r-lg">
      <div class="flex items-center">
        <svg class="h-5 w-5 text-green-600 dark:text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
        </svg>
        <div class="flex-1">
          <p class="text-sm font-medium text-green-800 dark:text-green-300">
            Pengisian Aktif - Dapat Diedit
          </p>
          <p v-if="lockStatus.deadline" class="text-xs text-green-700 dark:text-green-400 mt-1">
            📅 Deadline: {{ formatDate(lockStatus.deadline) }}
            <span v-if="lockStatus.sisa_hari !== null" class="ml-2">
              ({{ lockStatus.sisa_hari }} hari lagi)
            </span>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="loading" class="mb-4">
    <div class="border-l-4 border-gray-300 bg-gray-50 dark:bg-gray-800 p-3 rounded-r-lg">
      <div class="flex items-center">
        <svg class="animate-spin h-5 w-5 text-gray-600 dark:text-gray-400 mr-2" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm text-gray-600 dark:text-gray-400">Memeriksa status lock...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  pengisianButirId: {
    type: Number,
    required: true
  },
  showActiveStatus: {
    type: Boolean,
    default: false
  },
  autoRefresh: {
    type: Boolean,
    default: false
  },
  refreshInterval: {
    type: Number,
    default: 60000 // 1 minute
  }
})

const emit = defineEmits(['lock-status-changed', 'contact-admin'])

const loading = ref(false)
const lockStatus = ref({
  is_locked: false,
  reason: null,
  deadline: null,
  is_expired: false,
  sisa_hari: null,
  status: null
})

let refreshTimer = null

const checkLockStatus = async () => {
  if (!props.pengisianButirId) return

  loading.value = true

  try {
    const response = await axios.get(`/api/pengisian-butir/${props.pengisianButirId}/check-lock-status`)

    if (response.data.success) {
      lockStatus.value = response.data.data
      emit('lock-status-changed', response.data.data)
    }
  } catch (error) {
    console.error('Failed to check lock status:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const startAutoRefresh = () => {
  if (props.autoRefresh && props.refreshInterval > 0) {
    refreshTimer = setInterval(checkLockStatus, props.refreshInterval)
  }
}

const stopAutoRefresh = () => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
    refreshTimer = null
  }
}

watch(() => props.pengisianButirId, (newId) => {
  if (newId) {
    checkLockStatus()
  }
})

watch(() => props.autoRefresh, (newVal) => {
  if (newVal) {
    startAutoRefresh()
  } else {
    stopAutoRefresh()
  }
})

onMounted(() => {
  checkLockStatus()
  startAutoRefresh()
})

// Cleanup on unmount
import { onUnmounted } from 'vue'
onUnmounted(() => {
  stopAutoRefresh()
})

// Expose method for manual refresh
defineExpose({
  refresh: checkLockStatus,
  lockStatus
})
</script>
