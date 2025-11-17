<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Manajemen Permission
          </h3>
          <button
            @click="$router.push('/users')"
            class="text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
        </div>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Daftar permissions yang tersedia di sistem
        </p>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 gap-4 border-b border-gray-200 px-6 py-4 dark:border-gray-700 md:grid-cols-3">
        <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
          <p class="text-sm text-purple-600 dark:text-purple-400">Total Permissions</p>
          <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ permissions.length }}</p>
        </div>
        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
          <p class="text-sm text-blue-600 dark:text-blue-400">Grup Module</p>
          <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ Object.keys(groupedPermissions).length }}</p>
        </div>
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
          <p class="text-sm text-green-600 dark:text-green-400">Guards</p>
          <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ uniqueGuards.length }}</p>
        </div>
      </div>

      <!-- Filter -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari permission..."
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
          />
          <select
            v-model="selectedGuard"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="">Semua Guards</option>
            <option v-for="guard in uniqueGuards" :key="guard" :value="guard">{{ guard }}</option>
          </select>
        </div>
      </div>

      <!-- Grouped Permissions -->
      <div class="p-6">
        <div v-if="loading" class="py-8 text-center">
          <div class="flex items-center justify-center">
            <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
        </div>

        <div v-else-if="Object.keys(filteredGroupedPermissions).length === 0" class="py-8 text-center text-gray-500 dark:text-gray-400">
          Tidak ada permission
        </div>

        <div v-else class="space-y-6">
          <div
            v-for="(perms, module) in filteredGroupedPermissions"
            :key="module"
            class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
          >
            <!-- Module Header -->
            <div
              @click="toggleModule(module)"
              class="flex cursor-pointer items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900"
            >
              <div>
                <h4 class="font-semibold text-gray-900 dark:text-white">{{ formatModuleName(module) }}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ perms.length }} permissions</p>
              </div>
              <svg
                :class="[
                  'h-5 w-5 text-gray-600 transition-transform dark:text-gray-400',
                  expandedModules.includes(module) ? 'rotate-180' : ''
                ]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>

            <!-- Permissions List -->
            <div v-if="expandedModules.includes(module)" class="p-6">
              <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                  <thead class="border-b border-gray-200 text-xs uppercase dark:border-gray-700">
                    <tr>
                      <th class="pb-3 pr-6 font-medium text-gray-700 dark:text-gray-400">Permission</th>
                      <th class="pb-3 pr-6 font-medium text-gray-700 dark:text-gray-400">Guard</th>
                      <th class="pb-3 font-medium text-gray-700 dark:text-gray-400">Roles</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="permission in perms" :key="permission.id">
                      <td class="py-3 pr-6">
                        <span class="font-medium text-gray-900 dark:text-white">{{ permission.name }}</span>
                      </td>
                      <td class="py-3 pr-6">
                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                          {{ permission.guard_name || 'web' }}
                        </span>
                      </td>
                      <td class="py-3">
                        <div class="flex flex-wrap gap-1">
                          <span
                            v-for="role in permission.roles"
                            :key="role.id"
                            class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                          >
                            {{ role.name }}
                          </span>
                          <span v-if="!permission.roles || permission.roles.length === 0" class="text-gray-500 dark:text-gray-400">-</span>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Info Footer -->
      <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-start">
          <svg class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div class="text-sm text-gray-600 dark:text-gray-400">
            <p class="font-medium">Informasi:</p>
            <p class="mt-1">Halaman ini menampilkan daftar permissions yang dikelompokkan berdasarkan module. Untuk menambah atau mengubah permissions, silakan hubungi administrator sistem.</p>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useUserApi } from '@/composables/useUserApi';

const router = useRouter();

const { getPermissions, loading } = useUserApi();

const permissions = ref([]);
const searchQuery = ref('');
const selectedGuard = ref('');
const expandedModules = ref([]);

const groupedPermissions = computed(() => {
  const groups = {};
  permissions.value.forEach(permission => {
    // Extract module name from permission name (e.g., "users.create" -> "users")
    const parts = permission.name.split('.');
    const module = parts.length > 1 ? parts[0] : 'other';

    if (!groups[module]) {
      groups[module] = [];
    }
    groups[module].push(permission);
  });
  return groups;
});

const filteredGroupedPermissions = computed(() => {
  let filtered = { ...groupedPermissions.value };

  // Filter by guard
  if (selectedGuard.value) {
    filtered = Object.fromEntries(
      Object.entries(filtered).map(([module, perms]) => [
        module,
        perms.filter(p => p.guard_name === selectedGuard.value)
      ]).filter(([module, perms]) => perms.length > 0)
    );
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = Object.fromEntries(
      Object.entries(filtered).map(([module, perms]) => [
        module,
        perms.filter(p => p.name.toLowerCase().includes(query))
      ]).filter(([module, perms]) => perms.length > 0)
    );
  }

  return filtered;
});

const uniqueGuards = computed(() => {
  const guards = new Set(permissions.value.map(p => p.guard_name || 'web'));
  return Array.from(guards).sort();
});

const formatModuleName = (module) => {
  // Capitalize first letter and replace underscores/hyphens with spaces
  return module
    .replace(/[-_]/g, ' ')
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

const toggleModule = (module) => {
  const index = expandedModules.value.indexOf(module);
  if (index > -1) {
    expandedModules.value.splice(index, 1);
  } else {
    expandedModules.value.push(module);
  }
};

const fetchPermissions = async () => {
  try {
    const response = await getPermissions();
    if (response.success) {
      permissions.value = response.data;
      // Expand all modules by default
      expandedModules.value = Object.keys(groupedPermissions.value);
    }
  } catch (error) {
    console.error('Failed to fetch permissions:', error);
    alert('Gagal memuat data permission: ' + (error.response?.data?.message || error.message));
  }
};

onMounted(() => {
  fetchPermissions();
});
</script>
