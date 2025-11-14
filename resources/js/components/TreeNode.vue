<template>
  <div class="tree-node">
    <div
      :class="[
        'flex items-center justify-between rounded-lg border p-3 transition-colors',
        'hover:bg-gray-50 dark:hover:bg-gray-700',
        level === 0
          ? 'border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800'
          : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-750'
      ]"
      :style="{ marginLeft: `${level * 24}px` }"
    >
      <div class="flex flex-1 items-center gap-3">
        <!-- Expand/Collapse Button -->
        <button
          v-if="node.children && node.children.length > 0"
          @click="toggleExpand"
          class="flex-shrink-0 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        >
          <svg
            class="h-5 w-5 transition-transform"
            :class="{ 'rotate-90': isExpanded }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <div v-else class="w-5 flex-shrink-0"></div>

        <!-- Butir Info -->
        <div class="flex-1">
          <div class="flex items-center gap-2">
            <span class="font-mono text-sm font-medium text-blue-600 dark:text-blue-400">
              {{ node.kode }}
            </span>
            <span class="font-medium text-gray-900 dark:text-white">
              {{ node.nama }}
            </span>
          </div>
          <div class="mt-1 flex items-center gap-2">
            <span
              v-if="node.kategori"
              class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-300"
            >
              {{ node.kategori }}
            </span>
            <span
              v-if="node.instrumen"
              class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
            >
              {{ node.instrumen }}
            </span>
            <span
              v-if="node.bobot"
              class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300"
            >
              Bobot: {{ node.bobot }}
            </span>
            <span
              v-if="node.is_mandatory"
              class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300"
            >
              Wajib
            </span>
          </div>
          <p v-if="node.deskripsi" class="mt-1 text-xs text-gray-600 dark:text-gray-400">
            {{ truncate(node.deskripsi, 100) }}
          </p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-2">
        <button
          @click="$emit('add-child', node)"
          class="rounded p-1.5 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/30"
          title="Tambah Sub-Butir"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </button>
        <button
          @click="$emit('edit', node)"
          class="rounded p-1.5 text-yellow-600 hover:bg-yellow-50 dark:text-yellow-400 dark:hover:bg-yellow-900/30"
          title="Edit"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        </button>
        <button
          @click="$emit('delete', node)"
          class="rounded p-1.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
          title="Hapus"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Children -->
    <div v-if="isExpanded && node.children && node.children.length > 0" class="mt-2 space-y-2">
      <TreeNode
        v-for="child in node.children"
        :key="child.id"
        :node="child"
        :level="level + 1"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
        @add-child="$emit('add-child', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  node: {
    type: Object,
    required: true
  },
  level: {
    type: Number,
    default: 0
  }
})

defineEmits(['edit', 'delete', 'add-child'])

const isExpanded = ref(true)

const toggleExpand = () => {
  isExpanded.value = !isExpanded.value
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}
</script>
