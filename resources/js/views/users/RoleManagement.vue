<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Manajemen Role
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
          Daftar role dan permissions yang tersedia di sistem
        </p>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 gap-4 border-b border-gray-200 px-6 py-4 dark:border-gray-700 md:grid-cols-3">
        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
          <p class="text-sm text-blue-600 dark:text-blue-400">Total Role</p>
          <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ roles.length }}</p>
        </div>
        <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
          <p class="text-sm text-purple-600 dark:text-purple-400">Total Permissions</p>
          <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ totalPermissions }}</p>
        </div>
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
          <p class="text-sm text-green-600 dark:text-green-400">Total Users</p>
          <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ totalUsers }}</p>
        </div>
      </div>

      <!-- Filter -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari role..."
          class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 md:w-1/3"
        />
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">Role</th>
              <th scope="col" class="px-6 py-3">Guard</th>
              <th scope="col" class="px-6 py-3">Permissions</th>
              <th scope="col" class="px-6 py-3">Users</th>
              <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="px-6 py-8 text-center">
                <div class="flex items-center justify-center">
                  <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </td>
            </tr>
            <tr v-else-if="filteredRoles.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada role
              </td>
            </tr>
            <template v-else>
              <template v-for="role in filteredRoles" :key="role.id">
                <tr class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                  <td class="px-6 py-4">
                    <p class="font-medium text-gray-900 dark:text-white">{{ role.name }}</p>
                  </td>
                  <td class="px-6 py-4 text-gray-900 dark:text-white">
                    {{ role.guard_name || 'web' }}
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                      {{ role.permissions ? role.permissions.length : 0 }} permissions
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                      {{ role.users_count || 0 }} users
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <button
                      @click="toggleExpand(role.id)"
                      class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                      :title="expandedRoles.includes(role.id) ? 'Sembunyikan' : 'Tampilkan Permissions'"
                    >
                      <svg v-if="expandedRoles.includes(role.id)" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                      </svg>
                      <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                  </td>
                </tr>
                <!-- Expanded Row -->
                <tr v-if="expandedRoles.includes(role.id)" class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
                  <td colspan="5" class="px-6 py-4">
                    <div class="rounded-lg bg-white p-4 dark:bg-gray-800">
                      <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                        Permissions untuk role "{{ role.name }}"
                      </h4>
                      <div v-if="role.permissions && role.permissions.length > 0" class="grid grid-cols-1 gap-2 md:grid-cols-3 lg:grid-cols-4">
                        <div
                          v-for="permission in role.permissions"
                          :key="permission.id"
                          class="flex items-center rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-700 dark:bg-gray-800"
                        >
                          <svg class="mr-2 h-4 w-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                          </svg>
                          <span class="text-xs text-gray-700 dark:text-gray-300">{{ permission.name }}</span>
                        </div>
                      </div>
                      <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                        Tidak ada permissions
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Info Footer -->
      <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-start">
          <svg class="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div class="text-sm text-gray-600 dark:text-gray-400">
            <p class="font-medium">Informasi:</p>
            <p class="mt-1">Halaman ini menampilkan daftar role dan permissions yang tersedia. Untuk menambah atau mengubah role dan permissions, silakan hubungi administrator sistem.</p>
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

const { getRoles, loading } = useUserApi();

const roles = ref([]);
const searchQuery = ref('');
const expandedRoles = ref([]);

const filteredRoles = computed(() => {
  if (!searchQuery.value) {
    return roles.value;
  }
  const query = searchQuery.value.toLowerCase();
  return roles.value.filter(role =>
    role.name.toLowerCase().includes(query)
  );
});

const totalPermissions = computed(() => {
  return roles.value.reduce((total, role) => {
    return total + (role.permissions ? role.permissions.length : 0);
  }, 0);
});

const totalUsers = computed(() => {
  return roles.value.reduce((total, role) => {
    return total + (role.users_count || 0);
  }, 0);
});

const fetchRoles = async () => {
  try {
    const response = await getRoles();
    if (response.success) {
      roles.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch roles:', error);
    alert('Gagal memuat data role: ' + (error.response?.data?.message || error.message));
  }
};

const toggleExpand = (roleId) => {
  const index = expandedRoles.value.indexOf(roleId);
  if (index > -1) {
    expandedRoles.value.splice(index, 1);
  } else {
    expandedRoles.value.push(roleId);
  }
};

onMounted(() => {
  fetchRoles();
});
</script>
