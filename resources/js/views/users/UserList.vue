<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Manajemen Pengguna
          </h3>
          <button
            @click="$router.push('/users/create')"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Pengguna
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div v-if="statistics" class="grid grid-cols-1 gap-4 border-b border-gray-200 px-6 py-4 dark:border-gray-700 md:grid-cols-4">
        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
          <p class="text-sm text-gray-600 dark:text-gray-400">Total Pengguna</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total_users || 0 }}</p>
        </div>
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
          <p class="text-sm text-green-600 dark:text-green-400">Aktif</p>
          <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ statistics.active_users || 0 }}</p>
        </div>
        <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
          <p class="text-sm text-red-600 dark:text-red-400">Tidak Aktif</p>
          <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ statistics.inactive_users || 0 }}</p>
        </div>
        <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
          <p class="text-sm text-purple-600 dark:text-purple-400">Total Role</p>
          <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ statistics.total_roles || 0 }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <div>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari nama, email, NIP, NIDN..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="filters.role"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchUsers()"
            >
              <option value="">Semua Role</option>
              <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.is_active"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchUsers()"
            >
              <option value="">Semua Status</option>
              <option value="1">Aktif</option>
              <option value="0">Tidak Aktif</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.unit_kerja_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchUsers()"
            >
              <option value="">Semua Unit Kerja</option>
              <option v-for="uk in unitKerjas" :key="uk.id" :value="uk.id">{{ uk.nama }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">Nama</th>
              <th scope="col" class="px-6 py-3">Email</th>
              <th scope="col" class="px-6 py-3">NIP/NIDN</th>
              <th scope="col" class="px-6 py-3">Unit Kerja</th>
              <th scope="col" class="px-6 py-3">Jabatan</th>
              <th scope="col" class="px-6 py-3">Role</th>
              <th scope="col" class="px-6 py-3">Status</th>
              <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="8" class="px-6 py-8 text-center">
                <div class="flex items-center justify-center">
                  <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </td>
            </tr>
            <tr v-else-if="users.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada pengguna
              </td>
            </tr>
            <tr v-else v-for="user in users" :key="user.id" class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                <p v-if="user.phone" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  {{ user.phone }}
                </p>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ user.email }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                <p v-if="user.nip">NIP: {{ user.nip }}</p>
                <p v-if="user.nidn">NIDN: {{ user.nidn }}</p>
                <p v-if="!user.nip && !user.nidn">-</p>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ user.unit_kerja?.nama || '-' }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ user.jabatan?.nama || '-' }}
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                  >
                    {{ role.name }}
                  </span>
                  <span v-if="!user.roles || user.roles.length === 0" class="text-gray-500 dark:text-gray-400">-</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="user.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'" class="inline-flex rounded-full px-3 py-1 text-xs font-medium">
                  {{ user.is_active ? 'Aktif' : 'Tidak Aktif' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    @click="$router.push(`/users/${user.id}/edit`)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="toggleActive(user)"
                    :class="user.is_active ? 'text-orange-600 hover:text-orange-700 dark:text-orange-400' : 'text-green-600 hover:text-green-700 dark:text-green-400'"
                    :title="user.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                  >
                    <svg v-if="user.is_active" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button
                    @click="openRolesModal(user)"
                    class="text-purple-600 hover:text-purple-700 dark:text-purple-400"
                    title="Atur Role"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(user)"
                    class="text-red-600 hover:text-red-700 dark:text-red-400"
                    title="Hapus"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="flex items-center justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="text-sm text-gray-700 dark:text-gray-400">
          Menampilkan <span class="font-medium text-gray-900 dark:text-white">{{ pagination.from }}</span> sampai
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.to }}</span> dari
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.total }}</span> pengguna
        </div>
        <div class="flex items-center space-x-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Previous
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'rounded-lg border px-3 py-2 text-sm font-medium',
              page === pagination.current_page
                ? 'border-blue-600 bg-blue-600 text-white hover:bg-blue-700'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Roles Assignment Modal -->
    <div v-if="showRolesModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50">
      <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Atur Role: {{ selectedUser?.name }}
          </h3>
          <button
            @click="closeRolesModal"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="space-y-2">
          <label v-for="role in roles" :key="role.id" class="flex items-center">
            <input
              type="checkbox"
              :value="role.name"
              v-model="selectedRoles"
              class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
            />
            <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ role.name }}</span>
          </label>
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="closeRolesModal"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Batal
          </button>
          <button
            @click="saveRoles"
            :disabled="loading"
            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            Simpan
          </button>
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
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const router = useRouter();

const { getUsers, getUserStatistics, deleteUser, toggleUserActive, assignRoles, getRoles, loading } = useUserApi();
const { getActiveUnitKerjas } = useMasterDataApi();

const users = ref([]);
const statistics = ref(null);
const roles = ref([]);
const unitKerjas = ref([]);

const filters = ref({
  search: '',
  role: '',
  is_active: '',
  unit_kerja_id: '',
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const showRolesModal = ref(false);
const selectedUser = ref(null);
const selectedRoles = ref([]);

const visiblePages = computed(() => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;

  let start = Math.max(1, current - 2);
  let end = Math.min(last, current + 2);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchUsers();
  }, 500);
};

const fetchUsers = async (page = 1) => {
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value,
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key];
    });

    const response = await getUsers(params);

    if (response.success) {
      users.value = response.data;
      pagination.value = response.meta;
    }
  } catch (error) {
    console.error('Failed to fetch users:', error);
    alert('Gagal memuat data pengguna: ' + (error.response?.data?.message || error.message));
  }
};

const fetchStatistics = async () => {
  try {
    const response = await getUserStatistics();
    if (response.success) {
      statistics.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch statistics:', error);
  }
};

const fetchRoles = async () => {
  try {
    const response = await getRoles();
    if (response.success) {
      roles.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch roles:', error);
  }
};

const fetchUnitKerjas = async () => {
  try {
    const response = await getActiveUnitKerjas();
    if (response.success) {
      unitKerjas.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch unit kerja:', error);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchUsers(page);
  }
};

const toggleActive = async (user) => {
  const action = user.is_active ? 'menonaktifkan' : 'mengaktifkan';
  if (confirm(`Apakah Anda yakin ingin ${action} pengguna "${user.name}"?`)) {
    try {
      const response = await toggleUserActive(user.id, !user.is_active);
      if (response.success) {
        alert(`Pengguna berhasil ${user.is_active ? 'dinonaktifkan' : 'diaktifkan'}`);
        fetchUsers(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert(`Gagal ${action} pengguna: ` + (error.response?.data?.message || error.message));
    }
  }
};

const openRolesModal = (user) => {
  selectedUser.value = user;
  selectedRoles.value = user.roles ? user.roles.map(r => r.name) : [];
  showRolesModal.value = true;
};

const closeRolesModal = () => {
  showRolesModal.value = false;
  selectedUser.value = null;
  selectedRoles.value = [];
};

const saveRoles = async () => {
  try {
    const response = await assignRoles(selectedUser.value.id, selectedRoles.value);
    if (response.success) {
      alert('Role berhasil diatur');
      closeRolesModal();
      fetchUsers(pagination.value.current_page);
    }
  } catch (error) {
    alert('Gagal mengatur role: ' + (error.response?.data?.message || error.message));
  }
};

const confirmDelete = async (user) => {
  if (confirm(`Apakah Anda yakin ingin menghapus pengguna "${user.name}"? Tindakan ini tidak dapat dibatalkan.`)) {
    try {
      const response = await deleteUser(user.id);
      if (response.success) {
        alert('Pengguna berhasil dihapus');
        fetchUsers(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menghapus pengguna: ' + (error.response?.data?.message || error.message));
    }
  }
};

onMounted(() => {
  fetchStatistics();
  fetchUsers();
  fetchRoles();
  fetchUnitKerjas();
});
</script>
