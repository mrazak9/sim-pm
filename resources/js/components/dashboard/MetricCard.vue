<template>
  <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-1">
          <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
            {{ title }}
          </h4>
        </div>
        <div class="flex items-baseline gap-2">
          <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ value }}
          </h3>
          <span
            v-if="change"
            :class="[
              'text-xs font-medium px-2 py-0.5 rounded',
              changeType === 'increase'
                ? 'bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400'
                : 'bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400'
            ]"
          >
            {{ changeType === 'increase' ? '↑' : '↓' }} {{ change }}%
          </span>
        </div>
        <p v-if="subtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          {{ subtitle }}
        </p>
      </div>

      <!-- Icon -->
      <div
        :class="[
          'flex h-12 w-12 items-center justify-center rounded-full flex-shrink-0',
          iconBgColor
        ]"
      >
        <component :is="iconComponent" :class="[iconColor, 'w-6 h-6']" />
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  change: {
    type: [String, Number],
    default: null,
  },
  changeType: {
    type: String,
    default: 'increase', // 'increase' or 'decrease'
  },
  subtitle: {
    type: String,
    default: '',
  },
  iconComponent: {
    type: Object,
    required: true,
  },
  iconColor: {
    type: String,
    default: 'text-blue-600 dark:text-blue-400',
  },
  iconBgColor: {
    type: String,
    default: 'bg-blue-100 dark:bg-blue-900/20',
  },
});
</script>
