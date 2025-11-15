<template>
  <div>
    <div
      :class="[
        'flex items-center justify-between rounded-lg border p-4 transition-colors',
        category.is_active
          ? 'border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600'
          : 'border-gray-300 bg-gray-100 dark:border-gray-500 dark:bg-gray-800'
      ]"
      :style="{ marginLeft: level * 24 + 'px' }"
    >
      <div class="flex flex-1 items-center space-x-3">
        <!-- Color Badge -->
        <div
          class="h-8 w-8 rounded-lg"
          :style="{ backgroundColor: category.color }"
        ></div>

        <!-- Category Info -->
        <div class="flex-1">
          <div class="flex items-center space-x-2">
            <h4 class="font-medium text-gray-900 dark:text-white">{{ category.name }}</h4>
            <span class="rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-600 dark:text-gray-300">
              {{ category.code }}
            </span>
            <span
              v-if="!category.is_active"
              class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-800 dark:bg-red-900 dark:text-red-300"
            >
              Tidak Aktif
            </span>
          </div>
          <p v-if="category.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ category.description }}
          </p>
          <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
            <span v-if="category.icon" class="flex items-center">
              <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
              </svg>
              {{ category.icon }}
            </span>
            <span>Order: {{ category.order }}</span>
            <span v-if="hasChildren">{{ childrenCount }} sub-kategori</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-2">
          <button
            @click="$emit('add-child', category.id)"
            class="rounded-lg bg-green-100 p-2 text-green-600 hover:bg-green-200 dark:bg-green-900 dark:text-green-400 dark:hover:bg-green-800"
            title="Tambah Sub-kategori"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </button>
          <button
            @click="$emit('edit', category)"
            class="rounded-lg bg-yellow-100 p-2 text-yellow-600 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-400 dark:hover:bg-yellow-800"
            title="Edit"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button
            @click="$emit('delete', category)"
            class="rounded-lg bg-red-100 p-2 text-red-600 hover:bg-red-200 dark:bg-red-900 dark:text-red-400 dark:hover:bg-red-800"
            title="Hapus"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
          <button
            v-if="hasChildren"
            @click="expanded = !expanded"
            class="rounded-lg bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
            title="Toggle"
          >
            <svg
              :class="['h-4 w-4 transition-transform', expanded ? 'rotate-180' : '']"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Children (Recursive) -->
    <transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="max-h-0 opacity-0"
      enter-to-class="max-h-[2000px] opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="max-h-[2000px] opacity-100"
      leave-to-class="max-h-0 opacity-0"
    >
      <div v-show="expanded && hasChildren" class="mt-2 space-y-2">
        <CategoryTreeItem
          v-for="child in category.children"
          :key="child.id"
          :category="child"
          :level="level + 1"
          @edit="$emit('edit', $event)"
          @delete="$emit('delete', $event)"
          @add-child="$emit('add-child', $event)"
        />
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  category: {
    type: Object,
    required: true,
  },
  level: {
    type: Number,
    default: 0,
  },
});

defineEmits(['edit', 'delete', 'add-child']);

const expanded = ref(true);

const hasChildren = computed(() => {
  return props.category.children && props.category.children.length > 0;
});

const childrenCount = computed(() => {
  return props.category.children?.length || 0;
});
</script>
