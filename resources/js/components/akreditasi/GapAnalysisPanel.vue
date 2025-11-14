<template>
  <div class="gap-analysis-panel space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
      <p class="text-red-800 dark:text-red-200">{{ error }}</p>
    </div>

    <!-- Gap Analysis Content -->
    <div v-else-if="gapData" class="space-y-6">
      <!-- Readiness Score Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Kesiapan Akreditasi</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Readiness Score -->
          <div class="flex flex-col items-center justify-center p-6 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="relative w-32 h-32">
              <svg class="w-32 h-32 transform -rotate-90">
                <circle
                  cx="64"
                  cy="64"
                  r="56"
                  stroke="currentColor"
                  stroke-width="8"
                  fill="none"
                  class="text-gray-200 dark:text-gray-600"
                />
                <circle
                  cx="64"
                  cy="64"
                  r="56"
                  stroke="currentColor"
                  stroke-width="8"
                  fill="none"
                  :stroke-dasharray="circumference"
                  :stroke-dashoffset="readinessOffset"
                  :class="getScoreColor(gapData.summary.readiness_score)"
                  class="transition-all duration-1000"
                />
              </svg>
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ gapData.summary.readiness_score.toFixed(1) }}%
                  </div>
                  <div class="text-xs text-gray-600 dark:text-gray-400">Kesiapan</div>
                </div>
              </div>
            </div>
            <div class="mt-4">
              <span
                class="px-3 py-1 rounded-full text-sm font-medium"
                :class="getStatusBadgeClass(gapData.summary.readiness_status)"
              >
                {{ getStatusLabel(gapData.summary.readiness_status) }}
              </span>
            </div>
          </div>

          <!-- Summary Statistics -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
              <div class="text-2xl font-bold text-blue-900 dark:text-blue-200">
                {{ gapData.summary.completed_butir }}
              </div>
              <div class="text-sm text-blue-700 dark:text-blue-300">Butir Selesai</div>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
              <div class="text-2xl font-bold text-red-900 dark:text-red-200">
                {{ gapData.summary.total_gap }}
              </div>
              <div class="text-sm text-red-700 dark:text-red-300">Total Gap</div>
            </div>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
              <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-200">
                {{ gapData.summary.critical_gap }}
              </div>
              <div class="text-sm text-yellow-700 dark:text-yellow-300">Critical Gap</div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
              <div class="text-2xl font-bold text-green-900 dark:text-green-200">
                {{ gapData.summary.mandatory_score.toFixed(0) }}%
              </div>
              <div class="text-sm text-green-700 dark:text-green-300">Mandatory Score</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recommendations -->
      <div v-if="gapData.recommendations.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Rekomendasi</h3>
        <div class="space-y-3">
          <div
            v-for="(rec, index) in gapData.recommendations"
            :key="index"
            class="border-l-4 p-4 rounded-r-lg"
            :class="getPriorityBorderClass(rec.priority)"
          >
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0 mt-1">
                <svg v-if="rec.priority === 'critical'" class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <svg v-else-if="rec.priority === 'high'" class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900 dark:text-white">{{ rec.title }}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ rec.description }}</p>
                <p class="text-sm text-blue-600 dark:text-blue-400 mt-2">
                  <strong>Action:</strong> {{ rec.action }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gap by Kategori -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Gap Analysis per Kategori</h3>
        <div class="space-y-4">
          <div
            v-for="kategori in gapData.kategori_gaps"
            :key="kategori.kategori"
            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
          >
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-medium text-gray-900 dark:text-white">{{ kategori.kategori }}</h4>
              <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ kategori.completed }}/{{ kategori.total_butir }} butir
              </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
              <div
                class="h-2 rounded-full transition-all duration-500"
                :class="getProgressBarColor(kategori.completion_percentage)"
                :style="{ width: kategori.completion_percentage + '%' }"
              ></div>
            </div>
            <div class="flex items-center justify-between mt-2">
              <span class="text-sm text-gray-600 dark:text-gray-400">
                Completion: {{ kategori.completion_percentage.toFixed(1) }}%
              </span>
              <span
                v-if="kategori.gap > 0"
                class="text-sm font-medium text-red-600 dark:text-red-400"
              >
                Gap: {{ kategori.gap }} butir ({{ kategori.gap_percentage.toFixed(1) }}%)
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Gap Lists -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Missing Butir -->
        <div v-if="gapData.missing_butir.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Butir Belum Dikerjakan ({{ gapData.missing_butir.length }})
          </h3>
          <div class="space-y-2 max-h-96 overflow-y-auto">
            <div
              v-for="butir in gapData.missing_butir"
              :key="butir.id"
              class="border border-red-200 dark:border-red-800 rounded-lg p-3 bg-red-50 dark:bg-red-900/20"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="font-medium text-gray-900 dark:text-white">{{ butir.kode }}</div>
                  <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ butir.nama }}</div>
                  <div class="flex items-center gap-2 mt-2">
                    <span class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">{{ butir.kategori }}</span>
                    <span
                      v-if="butir.is_mandatory"
                      class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 rounded font-medium"
                    >
                      Mandatory
                    </span>
                  </div>
                </div>
                <span
                  class="flex-shrink-0 px-2 py-1 text-xs font-medium rounded"
                  :class="butir.severity === 'critical' ? 'bg-red-600 text-white' : 'bg-orange-600 text-white'"
                >
                  {{ butir.severity }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Incomplete Butir -->
        <div v-if="gapData.incomplete_butir.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Butir Belum Selesai ({{ gapData.incomplete_butir.length }})
          </h3>
          <div class="space-y-2 max-h-96 overflow-y-auto">
            <div
              v-for="butir in gapData.incomplete_butir"
              :key="butir.id"
              class="border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 bg-yellow-50 dark:bg-yellow-900/20"
            >
              <div class="flex-1">
                <div class="font-medium text-gray-900 dark:text-white">{{ butir.kode }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ butir.nama }}</div>
                <div class="flex items-center gap-2 mt-2">
                  <span class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">{{ butir.kategori }}</span>
                  <span class="text-xs px-2 py-1 bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200 rounded">
                    {{ butir.completion_percentage }}% complete
                  </span>
                  <span
                    v-if="butir.is_mandatory"
                    class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 rounded font-medium"
                  >
                    Mandatory
                  </span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ butir.reason }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Needs Revision -->
        <div v-if="gapData.needs_revision.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Perlu Revisi ({{ gapData.needs_revision.length }})
          </h3>
          <div class="space-y-2 max-h-96 overflow-y-auto">
            <div
              v-for="butir in gapData.needs_revision"
              :key="butir.id"
              class="border border-orange-200 dark:border-orange-800 rounded-lg p-3 bg-orange-50 dark:bg-orange-900/20"
            >
              <div class="flex-1">
                <div class="font-medium text-gray-900 dark:text-white">{{ butir.kode }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ butir.nama }}</div>
                <div class="flex items-center gap-2 mt-2">
                  <span class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">{{ butir.kategori }}</span>
                  <span class="text-xs px-2 py-1 bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-200 rounded">
                    Revision
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mandatory Not Approved -->
        <div v-if="gapData.mandatory_not_approved.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Mandatory Belum Approved ({{ gapData.mandatory_not_approved.length }})
          </h3>
          <div class="space-y-2 max-h-96 overflow-y-auto">
            <div
              v-for="butir in gapData.mandatory_not_approved"
              :key="butir.id"
              class="border border-red-200 dark:border-red-800 rounded-lg p-3 bg-red-50 dark:bg-red-900/20"
            >
              <div class="flex-1">
                <div class="font-medium text-gray-900 dark:text-white">{{ butir.kode }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ butir.nama }}</div>
                <div class="flex items-center gap-2 mt-2">
                  <span class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">{{ butir.kategori }}</span>
                  <span class="text-xs px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 rounded font-medium">
                    Mandatory - {{ butir.status || 'Not Started' }}
                  </span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ butir.reason }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'

const props = defineProps({
  periodeId: {
    type: [String, Number],
    required: true
  }
})

const { loading, error, getGapAnalysis } = useAkreditasiApi()
const gapData = ref(null)

// Circle calculations for progress ring
const circumference = 2 * Math.PI * 56
const readinessOffset = computed(() => {
  if (!gapData.value) return circumference
  const progress = gapData.value.summary.readiness_score
  return circumference - (progress / 100) * circumference
})

const fetchGapAnalysis = async () => {
  try {
    const response = await getGapAnalysis(props.periodeId)
    if (response.success) {
      gapData.value = response.data
    }
  } catch (err) {
    console.error('Failed to fetch gap analysis:', err)
  }
}

const getScoreColor = (score) => {
  if (score >= 90) return 'text-green-500'
  if (score >= 70) return 'text-blue-500'
  if (score >= 50) return 'text-yellow-500'
  return 'text-red-500'
}

const getProgressBarColor = (percentage) => {
  if (percentage >= 90) return 'bg-green-500'
  if (percentage >= 70) return 'bg-blue-500'
  if (percentage >= 50) return 'bg-yellow-500'
  return 'bg-red-500'
}

const getStatusLabel = (status) => {
  const labels = {
    ready: 'Siap',
    almost_ready: 'Hampir Siap',
    in_progress: 'Dalam Progress',
    at_risk: 'Berisiko',
    not_ready: 'Belum Siap'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    ready: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
    almost_ready: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
    in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
    at_risk: 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200',
    not_ready: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityBorderClass = (priority) => {
  const classes = {
    critical: 'border-red-500 bg-red-50 dark:bg-red-900/10',
    high: 'border-orange-500 bg-orange-50 dark:bg-orange-900/10',
    medium: 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/10',
    low: 'border-blue-500 bg-blue-50 dark:bg-blue-900/10'
  }
  return classes[priority] || 'border-gray-500 bg-gray-50 dark:bg-gray-900/10'
}

onMounted(() => {
  fetchGapAnalysis()
})

// Expose refresh method
defineExpose({
  refresh: fetchGapAnalysis
})
</script>

<style scoped>
.gap-analysis-panel {
  @apply w-full;
}
</style>
