<template>
  <div class="relative">
    <!-- Notification Bell Button -->
    <button
      @click="toggleNotifications"
      class="relative p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
      :class="{ 'bg-gray-100 dark:bg-gray-700': isOpen }"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Unread Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full min-w-[20px]"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Notifications Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        v-click-outside="closeNotifications"
        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifikasi</h3>
          <button
            v-if="unreadCount > 0"
            @click="handleMarkAllAsRead"
            class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
          >
            Tandai semua dibaca
          </button>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>

          <!-- Empty State -->
          <div
            v-else-if="!notifications || notifications.length === 0"
            class="flex flex-col items-center justify-center py-8 text-gray-500 dark:text-gray-400"
          >
            <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-sm">Tidak ada notifikasi</p>
          </div>

          <!-- Notification Items -->
          <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              @click="handleNotificationClick(notification)"
              class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
              :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.is_read }"
            >
              <div class="flex items-start gap-3">
                <!-- Priority Indicator -->
                <div
                  class="w-2 h-2 rounded-full mt-2 flex-shrink-0"
                  :class="getPriorityColor(notification.priority)"
                ></div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ notification.title }}
                  </p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ notification.message }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                    {{ formatRelativeTime(notification.created_at) }}
                  </p>
                </div>

                <!-- Unread Indicator -->
                <div v-if="!notification.is_read" class="w-2 h-2 rounded-full bg-blue-600 mt-2 flex-shrink-0"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-center">
          <router-link
            to="/notifications"
            @click="closeNotifications"
            class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
          >
            Lihat semua notifikasi
          </router-link>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationApi } from '@/composables/useNotificationApi'

const router = useRouter()
const { loading, getNotifications, getUnreadCount, markAsRead, markAllAsRead } = useNotificationApi()

const isOpen = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
let refreshInterval = null

// Toggle notifications dropdown
const toggleNotifications = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value && notifications.value.length === 0) {
    fetchNotifications()
  }
}

// Close notifications dropdown
const closeNotifications = () => {
  isOpen.value = false
}

// Fetch notifications
const fetchNotifications = async () => {
  try {
    const response = await getNotifications({ per_page: 10 })
    if (response.success) {
      notifications.value = response.data
    }
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  }
}

// Fetch unread count
const fetchUnreadCount = async () => {
  try {
    const response = await getUnreadCount()
    if (response.success) {
      unreadCount.value = response.data.unread_count
    }
  } catch (error) {
    console.error('Failed to fetch unread count:', error)
  }
}

// Handle notification click
const handleNotificationClick = async (notification) => {
  // Mark as read
  if (!notification.is_read) {
    try {
      await markAsRead(notification.id)
      notification.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (error) {
      console.error('Failed to mark notification as read:', error)
    }
  }

  // Navigate to action URL if available
  if (notification.action_url) {
    closeNotifications()
    router.push(notification.action_url)
  }
}

// Handle mark all as read
const handleMarkAllAsRead = async () => {
  try {
    const response = await markAllAsRead()
    if (response.success) {
      notifications.value.forEach(n => {
        n.is_read = true
      })
      unreadCount.value = 0
    }
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

// Get priority color
const getPriorityColor = (priority) => {
  const colors = {
    urgent: 'bg-red-500',
    high: 'bg-orange-500',
    medium: 'bg-yellow-500',
    low: 'bg-blue-500',
  }
  return colors[priority] || 'bg-gray-500'
}

// Format relative time
const formatRelativeTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diff = Math.floor((now - date) / 1000) // seconds

  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} jam yang lalu`
  if (diff < 604800) return `${Math.floor(diff / 86400)} hari yang lalu`
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  },
}

// Initialize
onMounted(() => {
  fetchUnreadCount()

  // Refresh unread count every 30 seconds
  refreshInterval = setInterval(() => {
    fetchUnreadCount()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})

// Expose refresh method
defineExpose({
  refresh: () => {
    fetchNotifications()
    fetchUnreadCount()
  },
})
</script>
