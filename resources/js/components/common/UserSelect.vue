<template>
  <div class="relative">
    <div class="relative">
      <input
        v-model="searchQuery"
        @input="handleSearch"
        @focus="showDropdown = true"
        @blur="handleBlur"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 pr-10 text-gray-900 focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:disabled:bg-gray-800"
        :class="{ 'border-red-500': error }"
      />
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
        <svg
          v-if="loading"
          class="h-5 w-5 animate-spin text-gray-400"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <!-- Dropdown -->
    <div
      v-if="showDropdown && !disabled"
      class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
    >
      <div v-if="loading" class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
        Memuat...
      </div>
      <div v-else-if="filteredUsers.length === 0" class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
        Tidak ada pengguna ditemukan
      </div>
      <ul v-else class="py-1">
        <li
          v-for="user in filteredUsers"
          :key="user.id"
          @mousedown.prevent="selectUser(user)"
          class="cursor-pointer px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600"
          :class="{ 'bg-blue-50 dark:bg-blue-900/20': isSelected(user.id) }"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="text-sm font-medium text-gray-900 dark:text-white">
                {{ user.name }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ user.email }}
              </div>
            </div>
            <div v-if="multiple && isSelected(user.id)" class="ml-2">
              <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- Selected users (for multiple selection) -->
    <div v-if="multiple && selectedUsersList.length > 0" class="mt-2 flex flex-wrap gap-2">
      <span
        v-for="user in selectedUsersList"
        :key="user.id"
        class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-800 dark:bg-blue-900 dark:text-blue-200"
      >
        {{ user.name }}
        <button
          @click="removeUser(user.id)"
          type="button"
          class="hover:text-blue-600 dark:hover:text-blue-400"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </span>
    </div>

    <!-- Error message -->
    <p v-if="error" class="mt-1 text-xs text-red-600 dark:text-red-400">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useUserApi } from '@/composables/useUserApi';

const props = defineProps({
  modelValue: {
    type: [Number, String, Array],
    default: null
  },
  multiple: {
    type: Boolean,
    default: false
  },
  placeholder: {
    type: String,
    default: 'Pilih pengguna...'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  role: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['update:modelValue']);

const { getUsers, getUsersByRole } = useUserApi();

const users = ref([]);
const searchQuery = ref('');
const showDropdown = ref(false);
const loading = ref(false);

const filteredUsers = computed(() => {
  if (!searchQuery.value) {
    return users.value;
  }
  const query = searchQuery.value.toLowerCase();
  return users.value.filter(user =>
    user.name?.toLowerCase().includes(query) ||
    user.email?.toLowerCase().includes(query)
  );
});

const selectedUsersList = computed(() => {
  if (!props.multiple || !props.modelValue) {
    return [];
  }
  const selectedIds = Array.isArray(props.modelValue) ? props.modelValue : [props.modelValue];
  return users.value.filter(user => selectedIds.includes(user.id));
});

const isSelected = (userId) => {
  if (props.multiple) {
    return Array.isArray(props.modelValue) && props.modelValue.includes(userId);
  }
  return props.modelValue === userId;
};

const fetchUsers = async () => {
  loading.value = true;
  try {
    let response;
    if (props.role) {
      response = await getUsersByRole(props.role);
    } else {
      response = await getUsers({ is_active: 1, per_page: 100 });
    }

    if (response.success) {
      users.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch users:', error);
  } finally {
    loading.value = false;
  }
};

const selectUser = (user) => {
  if (props.multiple) {
    const currentValue = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
    const index = currentValue.indexOf(user.id);
    if (index > -1) {
      currentValue.splice(index, 1);
    } else {
      currentValue.push(user.id);
    }
    emit('update:modelValue', currentValue);
    searchQuery.value = '';
  } else {
    emit('update:modelValue', user.id);
    searchQuery.value = user.name;
    showDropdown.value = false;
  }
};

const removeUser = (userId) => {
  if (props.multiple) {
    const currentValue = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
    const index = currentValue.indexOf(userId);
    if (index > -1) {
      currentValue.splice(index, 1);
      emit('update:modelValue', currentValue);
    }
  }
};

const handleSearch = () => {
  showDropdown.value = true;
};

const handleBlur = () => {
  setTimeout(() => {
    showDropdown.value = false;
    if (!props.multiple && props.modelValue) {
      const selectedUser = users.value.find(u => u.id === props.modelValue);
      if (selectedUser) {
        searchQuery.value = selectedUser.name;
      }
    } else if (!props.multiple) {
      searchQuery.value = '';
    }
  }, 200);
};

// Watch modelValue changes to update display
watch(() => props.modelValue, (newVal) => {
  if (!props.multiple && newVal) {
    const selectedUser = users.value.find(u => u.id === newVal);
    if (selectedUser) {
      searchQuery.value = selectedUser.name;
    }
  } else if (!props.multiple) {
    searchQuery.value = '';
  }
});

onMounted(() => {
  fetchUsers();

  // Set initial display value for single select
  if (!props.multiple && props.modelValue) {
    const selectedUser = users.value.find(u => u.id === props.modelValue);
    if (selectedUser) {
      searchQuery.value = selectedUser.name;
    }
  }
});
</script>
