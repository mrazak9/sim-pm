<template>
  <div class="chart-container">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
);

const props = defineProps({
  labels: {
    type: Array,
    required: true,
  },
  datasets: {
    type: Array,
    required: true,
  },
  title: {
    type: String,
    default: '',
  },
  height: {
    type: Number,
    default: 300,
  },
  responsive: {
    type: Boolean,
    default: true,
  },
  maintainAspectRatio: {
    type: Boolean,
    default: false,
  },
  horizontal: {
    type: Boolean,
    default: false,
  },
});

const chartData = computed(() => ({
  labels: props.labels,
  datasets: props.datasets.map((dataset) => ({
    ...dataset,
    borderWidth: 0,
    borderRadius: 4,
  })),
}));

const chartOptions = computed(() => ({
  indexAxis: props.horizontal ? 'y' : 'x',
  responsive: props.responsive,
  maintainAspectRatio: props.maintainAspectRatio,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        padding: 15,
        font: {
          size: 12,
        },
      },
    },
    title: {
      display: !!props.title,
      text: props.title,
      font: {
        size: 16,
        weight: 'bold',
      },
      padding: {
        top: 10,
        bottom: 20,
      },
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      titleFont: {
        size: 14,
      },
      bodyFont: {
        size: 13,
      },
      borderColor: 'rgba(255, 255, 255, 0.2)',
      borderWidth: 1,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
      },
      ticks: {
        font: {
          size: 11,
        },
      },
    },
    x: {
      grid: {
        display: props.horizontal,
        color: 'rgba(0, 0, 0, 0.05)',
      },
      ticks: {
        font: {
          size: 11,
        },
      },
    },
  },
}));
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
}
</style>
