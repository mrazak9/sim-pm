<template>
  <div v-if="lockInfo.is_locked && !lockInfo.locked_by_current_user" class="mb-4">
    <div class="border-l-4 border-orange-500 bg-orange-50 dark:bg-orange-900/20 p-4 rounded-r-lg">
      <div class="flex items-center">
        <svg class="h-5 w-5 text-orange-600 dark:text-orange-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <div class="flex-1">
          <p class="text-sm font-medium text-orange-800 dark:text-orange-300">
            <strong>{{ lockInfo.locked_by.name }}</strong> sedang mengedit dokumen ini
          </p>
          <p class="text-xs text-orange-700 dark:text-orange-400 mt-1">
            Sisa waktu: {{ lockInfo.remaining_minutes }} menit
          </p>
        </div>
      </div>
    </div>
  </div>

  <div v-else-if="lockInfo.locked_by_current_user" class="mb-4">
    <div class="border-l-4 border-blue-500 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-r-lg">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
            Anda sedang mengedit ({{ lockInfo.remaining_minutes }} menit tersisa)
          </p>
        </div>
        <button
          @click="extendLock"
          :disabled="extending"
          class="text-xs px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
        >
          {{ extending ? 'Memperpanjang...' : 'Perpanjang +30 menit' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  pengisianButirId: {
    type: Number,
    required: true
  },
  autoRefresh: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['lock-acquired', 'lock-released', 'lock-extended'])

const lockInfo = ref({
  is_locked: false,
  locked_by: null,
  locked_by_current_user: false,
  expires_at: null,
  remaining_minutes: 0
})

const extending = ref(false)
let refreshTimer = null

const checkLock = async () => {
  try {
    const response = await axios.get(`/api/pengisian-butir/${props.pengisianButirId}/check-edit-lock`)
    if (response.data.success) {
      lockInfo.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to check edit lock:', error)
  }
}

const extendLock = async () => {
  extending.value = true
  try {
    const response = await axios.post(`/api/pengisian-butir/${props.pengisianButirId}/extend-lock`)
    if (response.data.success) {
      await checkLock()
      emit('lock-extended', response.data.data)
    }
  } catch (error) {
    console.error('Failed to extend lock:', error)
    alert('Gagal memperpanjang waktu edit')
  } finally {
    extending.value = false
  }
}

onMounted(() => {
  checkLock()
  if (props.autoRefresh) {
    refreshTimer = setInterval(checkLock, 30000) // Refresh every 30 seconds
  }
})

onUnmounted(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
  }
})

defineExpose({ checkLock, lockInfo })
</script>
