<template>
  <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
      <slot name="header-action">
        <router-link
          v-if="viewAllLink"
          :to="viewAllLink"
          class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400"
        >
          {{ viewAllText }} →
        </router-link>
      </slot>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-8">
      <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>

    <div v-else-if="items.length === 0" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
      {{ emptyMessage }}
    </div>

    <div v-else class="space-y-3">
      <slot name="item" v-for="(item, index) in displayedItems" :key="getItemKey(item, index)" :item="item" :index="index">
        <!-- Default item template -->
        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-600">
          <div class="flex items-center space-x-3">
            <div v-if="item.icon || item.color" class="flex h-10 w-10 items-center justify-center rounded-lg"
                 :style="{ backgroundColor: item.color ? item.color + '20' : '#3B82F620' }">
              <component v-if="item.icon" :is="item.icon" class="h-6 w-6" :style="{ color: item.color || '#3B82F6' }" />
              <svg v-else class="h-6 w-6" :style="{ color: item.color || '#3B82F6' }" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="4" />
              </svg>
            </div>
            <div>
              <p class="font-medium text-gray-900 dark:text-white">{{ item.title || item.name }}</p>
              <p v-if="item.subtitle || item.description" class="text-xs text-gray-500 dark:text-gray-400">
                {{ item.subtitle || item.description }}
              </p>
            </div>
          </div>
          <div v-if="item.action" class="flex items-center space-x-2">
            <component :is="item.action" :item="item" />
          </div>
        </div>
      </slot>
    </div>

    <div v-if="showPagination && totalPages > 1" class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-600">
      <button
        @click="previousPage"
        :disabled="currentPage === 1"
        class="rounded-lg border border-gray-300 px-3 py-1 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
      >
        Previous
      </button>
      <span class="text-sm text-gray-600 dark:text-gray-400">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button
        @click="nextPage"
        :disabled="currentPage === totalPages"
        class="rounded-lg border border-gray-300 px-3 py-1 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  items: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: null
  },
  emptyMessage: {
    type: String,
    default: 'Tidak ada data'
  },
  viewAllLink: {
    type: String,
    default: null
  },
  viewAllText: {
    type: String,
    default: 'Lihat Semua'
  },
  itemsPerPage: {
    type: Number,
    default: 5
  },
  showPagination: {
    type: Boolean,
    default: false
  },
  itemKey: {
    type: String,
    default: 'id'
  }
});

const currentPage = ref(1);

const totalPages = computed(() => {
  return Math.ceil(props.items.length / props.itemsPerPage);
});

const displayedItems = computed(() => {
  if (!props.showPagination) {
    return props.items.slice(0, props.itemsPerPage);
  }
  const start = (currentPage.value - 1) * props.itemsPerPage;
  const end = start + props.itemsPerPage;
  return props.items.slice(start, end);
});

const getItemKey = (item, index) => {
  return item[props.itemKey] || index;
};

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};
</script>
