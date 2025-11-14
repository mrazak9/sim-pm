<template>
  <div class="space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dashboard Content -->
    <div v-else-if="dashboardData" class="space-y-6">
      <!-- Overall Progress Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Overall Progress</h3>
        <div class="flex items-center justify-center">
          <!-- Progress Ring -->
          <div class="relative inline-flex items-center justify-center">
            <svg class="transform -rotate-90 w-48 h-48">
              <!-- Background Circle -->
              <circle
                cx="96"
                cy="96"
                r="80"
                stroke="currentColor"
                stroke-width="12"
                fill="transparent"
                class="text-gray-200 dark:text-gray-700"
              />
              <!-- Progress Circle -->
              <circle
                cx="96"
                cy="96"
                r="80"
                stroke="currentColor"
                stroke-width="12"
                fill="transparent"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="progressOffset"
                class="text-blue-600 dark:text-blue-500 transition-all duration-1000 ease-out"
                stroke-linecap="round"
              />
            </svg>
            <!-- Percentage Text -->
            <div class="absolute text-center">
              <div class="text-4xl font-bold text-gray-900 dark:text-white">
                {{ dashboardData.basic_stats.completion_percentage }}%
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Complete</div>
            </div>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
          <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ dashboardData.basic_stats.total_butir }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Butir</div>
          </div>
          <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
              {{ dashboardData.basic_stats.pengisian_approved }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Approved</div>
          </div>
          <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
              {{ dashboardData.basic_stats.pengisian_review + dashboardData.basic_stats.pengisian_submitted }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">In Review</div>
          </div>
          <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">
              {{ dashboardData.basic_stats.pengisian_draft }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Draft</div>
          </div>
        </div>
      </div>

      <!-- Deadline Alerts -->
      <div
        v-if="dashboardData.deadline_alerts && dashboardData.deadline_alerts.length > 0"
        class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6"
      >
        <div class="flex items-start gap-3">
          <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-red-800 dark:text-red-300 mb-3">
              Deadline Alerts ({{ dashboardData.deadline_alerts.length }} items need attention)
            </h3>
            <div class="space-y-2">
              <div
                v-for="alert in dashboardData.deadline_alerts.slice(0, 5)"
                :key="alert.id"
                class="flex items-center justify-between text-sm bg-white dark:bg-gray-800 p-3 rounded"
              >
                <div class="flex-1">
                  <span class="font-medium text-gray-900 dark:text-white">{{ alert.butir_kode }}</span>
                  <span class="text-gray-600 dark:text-gray-400 ml-2">- {{ alert.pic_name }}</span>
                </div>
                <div class="text-red-600 dark:text-red-400 font-semibold">
                  {{ alert.is_overdue ? 'OVERDUE' : `${alert.days_left} days left` }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Progress by Kategori -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Progress by Kategori</h3>
        <div class="space-y-4">
          <div
            v-for="kategori in dashboardData.kategori_progress"
            :key="kategori.kategori"
            class="space-y-2"
          >
            <div class="flex items-center justify-between text-sm">
              <span class="font-medium text-gray-700 dark:text-gray-300">{{ kategori.kategori }}</span>
              <span class="text-gray-500 dark:text-gray-400">
                {{ kategori.approved }}/{{ kategori.total }} ({{ kategori.percentage }}%)
              </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
              <div
                class="h-2.5 rounded-full transition-all duration-500"
                :class="getProgressColor(kategori.percentage)"
                :style="{ width: kategori.percentage + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Two Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activities -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activities</h3>
          <div class="space-y-3">
            <div
              v-for="activity in dashboardData.recent_activities.slice(0, 8)"
              :key="activity.id"
              class="flex items-start gap-3 pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0"
            >
              <div
                class="w-2 h-2 rounded-full mt-2 flex-shrink-0"
                :class="getStatusDotColor(activity.status)"
              ></div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ activity.butir_kode }} - {{ activity.butir_nama }}
                </div>
                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                  <span>{{ activity.pic_name }}</span>
                  <span>•</span>
                  <span :class="getStatusColor(activity.status)">{{ formatStatus(activity.status) }}</span>
                  <span>•</span>
                  <span>{{ formatRelativeTime(activity.updated_at) }}</span>
                </div>
              </div>
              <div class="text-sm font-semibold text-gray-600 dark:text-gray-400 flex-shrink-0">
                {{ activity.completion }}%
              </div>
            </div>
          </div>
        </div>

        <!-- PIC Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Contributors</h3>
          <div class="space-y-3">
            <div
              v-for="(pic, index) in dashboardData.pic_performance"
              :key="index"
              class="flex items-center gap-4"
            >
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                <span class="text-sm font-bold text-blue-600 dark:text-blue-400">#{{ index + 1 }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ pic.pic_name }}
                </div>
                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                  <span>{{ pic.total_butir }} butir</span>
                  <span>•</span>
                  <span class="text-green-600 dark:text-green-400 font-medium">
                    {{ pic.approved_butir }} approved
                  </span>
                </div>
              </div>
              <div class="text-right flex-shrink-0">
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ pic.avg_completion }}%
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">avg</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Progress Trend Chart -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Progress Trend (Last 30 Days)</h3>
        <div class="relative h-64">
          <!-- Simple line chart using SVG -->
          <svg class="w-full h-full" preserveAspectRatio="none">
            <!-- Grid lines -->
            <line
              v-for="i in 5"
              :key="'grid-' + i"
              :x1="0"
              :y1="(i - 1) * 25 + '%'"
              x2="100%"
              :y2="(i - 1) * 25 + '%'"
              stroke="currentColor"
              class="text-gray-200 dark:text-gray-700"
              stroke-width="1"
            />
            <!-- Trend line -->
            <polyline
              :points="trendPoints"
              fill="none"
              stroke="currentColor"
              class="text-blue-600 dark:text-blue-500"
              stroke-width="2"
            />
            <!-- Area fill -->
            <polygon
              :points="trendAreaPoints"
              fill="currentColor"
              class="text-blue-500/20 dark:text-blue-500/10"
            />
          </svg>
          <!-- Y-axis labels -->
          <div class="absolute left-0 top-0 h-full flex flex-col justify-between text-xs text-gray-500 dark:text-gray-400 -ml-8">
            <span>100%</span>
            <span>75%</span>
            <span>50%</span>
            <span>25%</span>
            <span>0%</span>
          </div>
        </div>
        <!-- X-axis labels -->
        <div class="flex justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
          <span>{{ formatDate(dashboardData.trend_data[0]?.date) }}</span>
          <span>{{ formatDate(dashboardData.trend_data[14]?.date) }}</span>
          <span>{{ formatDate(dashboardData.trend_data[29]?.date) }}</span>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
      <div class="flex items-center gap-3 text-red-800 dark:text-red-300">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ error }}</span>
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
    required: true,
  },
})

const { getPeriodeDashboard, loading, error } = useAkreditasiApi()

const dashboardData = ref(null)

// Progress circle calculations
const circumference = computed(() => 2 * Math.PI * 80)
const progressOffset = computed(() => {
  if (!dashboardData.value) return circumference.value
  const percentage = dashboardData.value.basic_stats.completion_percentage
  return circumference.value - (percentage / 100) * circumference.value
})

// Trend chart points
const trendPoints = computed(() => {
  if (!dashboardData.value?.trend_data) return ''
  const data = dashboardData.value.trend_data
  const width = 100
  const height = 100

  return data
    .map((point, index) => {
      const x = (index / (data.length - 1)) * width
      const y = height - (point.percentage / 100) * height
      return `${x},${y}`
    })
    .join(' ')
})

const trendAreaPoints = computed(() => {
  if (!trendPoints.value) return ''
  return `0,100 ${trendPoints.value} 100,100`
})

// Helper functions
const getProgressColor = (percentage) => {
  if (percentage >= 80) return 'bg-green-500'
  if (percentage >= 60) return 'bg-blue-500'
  if (percentage >= 40) return 'bg-yellow-500'
  return 'bg-red-500'
}

const getStatusColor = (status) => {
  const colors = {
    approved: 'text-green-600 dark:text-green-400',
    review: 'text-blue-600 dark:text-blue-400',
    submitted: 'text-indigo-600 dark:text-indigo-400',
    revision: 'text-orange-600 dark:text-orange-400',
    draft: 'text-gray-600 dark:text-gray-400',
  }
  return colors[status] || 'text-gray-600 dark:text-gray-400'
}

const getStatusDotColor = (status) => {
  const colors = {
    approved: 'bg-green-500',
    review: 'bg-blue-500',
    submitted: 'bg-indigo-500',
    revision: 'bg-orange-500',
    draft: 'bg-gray-400',
  }
  return colors[status] || 'bg-gray-400'
}

const formatStatus = (status) => {
  const labels = {
    approved: 'Approved',
    review: 'In Review',
    submitted: 'Submitted',
    revision: 'Needs Revision',
    draft: 'Draft',
  }
  return labels[status] || status
}

const formatRelativeTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diff = Math.floor((now - date) / 1000) // seconds

  if (diff < 60) return 'just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`
  return date.toLocaleDateString()
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
}

// Load dashboard data
const loadDashboard = async () => {
  try {
    const response = await getPeriodeDashboard(props.periodeId)
    if (response.success) {
      dashboardData.value = response.data
    }
  } catch (err) {
    console.error('Failed to load dashboard:', err)
  }
}

onMounted(() => {
  loadDashboard()
})

// Expose refresh function
defineExpose({
  refresh: loadDashboard,
})
</script>
