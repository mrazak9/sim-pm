<template>
  <div class="space-y-6">
    <!-- Toolbar -->
    <div v-if="customizable" class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
      <div class="flex items-center space-x-4">
        <button
          @click="toggleEditMode"
          class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium"
          :class="editMode
            ? 'bg-blue-600 text-white hover:bg-blue-700'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
        >
          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          {{ editMode ? 'Selesai Edit' : 'Customize Layout' }}
        </button>

        <button
          v-if="editMode"
          @click="resetLayout"
          class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
        >
          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Reset Default
        </button>
      </div>

      <div v-if="editMode" class="text-sm text-gray-600 dark:text-gray-400">
        Drag & drop widget untuk mengatur ulang
      </div>
    </div>

    <!-- Widget Grid -->
    <div
      ref="widgetGrid"
      class="grid gap-6"
      :class="gridClass"
    >
      <div
        v-for="(widget, index) in displayedWidgets"
        :key="widget.id"
        :data-widget-id="widget.id"
        :class="[
          'widget-item',
          editMode && 'cursor-move',
          widget.hidden && 'hidden'
        ]"
        :style="getWidgetStyle(widget)"
      >
        <!-- Widget Header (Edit Mode) -->
        <div
          v-if="editMode"
          class="mb-2 flex items-center justify-between rounded-t-lg border border-b-0 border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700"
        >
          <div class="flex items-center space-x-2">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ widget.title }}</span>
          </div>
          <div class="flex items-center space-x-1">
            <button
              @click="toggleWidgetVisibility(widget.id)"
              class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-600 dark:hover:text-gray-200"
              :title="widget.hidden ? 'Show' : 'Hide'"
            >
              <svg v-if="widget.hidden" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
              <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Widget Content -->
        <component
          :is="widget.component"
          v-bind="widget.props"
          :class="editMode && 'pointer-events-none'"
        />
      </div>
    </div>

    <!-- Add Widget Panel (Edit Mode) -->
    <div v-if="editMode && availableWidgets.length > 0" class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
      <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Widget Tersedia</h4>
      <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <button
          v-for="widget in availableWidgets"
          :key="widget.id"
          @click="addWidget(widget)"
          class="rounded-lg border-2 border-dashed border-gray-300 p-3 text-center text-sm font-medium text-gray-700 hover:border-blue-500 hover:bg-blue-50 dark:border-gray-600 dark:text-gray-300 dark:hover:border-blue-500 dark:hover:bg-blue-900/20"
        >
          + {{ widget.title }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import Sortable from 'sortablejs';

const props = defineProps({
  widgets: {
    type: Array,
    required: true
  },
  columns: {
    type: Number,
    default: 3,
    validator: (value) => [1, 2, 3, 4].includes(value)
  },
  customizable: {
    type: Boolean,
    default: false
  },
  storageKey: {
    type: String,
    default: 'dashboard-layout'
  }
});

const emit = defineEmits(['update:widgets', 'layout-changed']);

const editMode = ref(false);
const widgetGrid = ref(null);
const displayedWidgets = ref([...props.widgets]);
let sortableInstance = null;

const gridClass = computed(() => {
  const classes = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 md:grid-cols-2',
    3: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4'
  };
  return classes[props.columns] || classes[3];
});

const availableWidgets = computed(() => {
  // Widgets that can be added (not currently displayed)
  return [];
});

const getWidgetStyle = (widget) => {
  if (!widget.gridSpan) return {};

  return {
    gridColumn: `span ${widget.gridSpan.cols || 1}`,
    gridRow: `span ${widget.gridSpan.rows || 1}`
  };
};

const toggleEditMode = () => {
  editMode.value = !editMode.value;

  if (editMode.value) {
    initSortable();
  } else {
    if (sortableInstance) {
      sortableInstance.destroy();
      sortableInstance = null;
    }
    saveLayout();
  }
};

const initSortable = () => {
  if (!widgetGrid.value) return;

  sortableInstance = Sortable.create(widgetGrid.value, {
    animation: 150,
    ghostClass: 'sortable-ghost',
    chosenClass: 'sortable-chosen',
    dragClass: 'sortable-drag',
    onEnd: (evt) => {
      const item = displayedWidgets.value.splice(evt.oldIndex, 1)[0];
      displayedWidgets.value.splice(evt.newIndex, 0, item);
    }
  });
};

const toggleWidgetVisibility = (widgetId) => {
  const widget = displayedWidgets.value.find(w => w.id === widgetId);
  if (widget) {
    widget.hidden = !widget.hidden;
  }
};

const addWidget = (widget) => {
  displayedWidgets.value.push({ ...widget, hidden: false });
};

const resetLayout = () => {
  if (confirm('Reset layout ke default? Perubahan Anda akan hilang.')) {
    displayedWidgets.value = [...props.widgets];
    localStorage.removeItem(props.storageKey);
  }
};

const saveLayout = () => {
  const layout = displayedWidgets.value.map((w, index) => ({
    id: w.id,
    order: index,
    hidden: w.hidden || false
  }));

  localStorage.setItem(props.storageKey, JSON.stringify(layout));
  emit('layout-changed', layout);
};

const loadLayout = () => {
  const saved = localStorage.getItem(props.storageKey);
  if (!saved) return;

  try {
    const layout = JSON.parse(saved);
    const ordered = [];

    // Restore order and visibility
    layout.forEach(item => {
      const widget = props.widgets.find(w => w.id === item.id);
      if (widget) {
        ordered.push({
          ...widget,
          hidden: item.hidden || false
        });
      }
    });

    // Add any new widgets that weren't in saved layout
    props.widgets.forEach(widget => {
      if (!ordered.find(w => w.id === widget.id)) {
        ordered.push(widget);
      }
    });

    displayedWidgets.value = ordered;
  } catch (error) {
    console.error('Failed to load layout:', error);
  }
};

watch(() => props.widgets, () => {
  if (!editMode.value) {
    loadLayout();
  }
}, { deep: true });

onMounted(() => {
  if (props.customizable) {
    loadLayout();
  }
});
</script>

<style scoped>
.sortable-ghost {
  opacity: 0.4;
  background: #e5e7eb;
}

.sortable-chosen {
  cursor: grabbing;
}

.sortable-drag {
  cursor: grabbing;
}
</style>
