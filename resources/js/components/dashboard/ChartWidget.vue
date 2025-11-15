<template>
  <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
      <div v-if="actions" class="flex items-center space-x-2">
        <slot name="actions"></slot>
      </div>
    </div>
    <div v-if="loading" class="flex items-center justify-center py-12">
      <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else>
      <canvas ref="chartCanvas" :style="{ height: height + 'px' }"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'line',
    validator: (value) => ['line', 'bar', 'pie', 'doughnut', 'radar', 'polarArea'].includes(value)
  },
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  },
  height: {
    type: Number,
    default: 300
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  },
  actions: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['refresh']);

const chartCanvas = ref(null);
let chartInstance = null;

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'bottom'
    },
    tooltip: {
      enabled: true
    }
  }
};

const initChart = () => {
  if (!chartCanvas.value || props.loading || props.error) return;

  // Destroy existing chart
  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartCanvas.value.getContext('2d');

  const mergedOptions = {
    ...defaultOptions,
    ...props.options
  };

  chartInstance = new Chart(ctx, {
    type: props.type,
    data: props.data,
    options: mergedOptions
  });
};

const updateChart = () => {
  if (!chartInstance || props.loading || props.error) return;

  chartInstance.data = props.data;
  chartInstance.update();
};

watch(() => props.data, () => {
  if (chartInstance) {
    updateChart();
  } else {
    initChart();
  }
}, { deep: true });

watch(() => props.loading, (newVal) => {
  if (!newVal) {
    setTimeout(initChart, 100);
  }
});

onMounted(() => {
  if (!props.loading && !props.error) {
    setTimeout(initChart, 100);
  }
});

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>
