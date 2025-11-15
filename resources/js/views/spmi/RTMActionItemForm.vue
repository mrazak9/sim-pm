<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Action Item' : 'Tambah Action Item' }}
          </h3>
          <button
            @click="$router.push('/spmi/rtm-actions')"
            class="text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="space-y-6">
          <!-- RTM -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              RTM <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.rtm_id"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Pilih RTM</option>
              <option v-for="rtm in rtms" :key="rtm.id" :value="rtm.id">
                {{ rtm.title }} ({{ formatDate(rtm.meeting_date) }})
              </option>
            </select>
          </div>

          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Judul Action Item <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan judul action item"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan deskripsi action item"
            ></textarea>
          </div>

          <!-- Priority & PIC -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Prioritas <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.priority"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Prioritas</option>
                <option value="low">Rendah</option>
                <option value="medium">Sedang</option>
                <option value="high">Tinggi</option>
                <option value="critical">Kritis</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Penanggung Jawab (PIC)
              </label>
              <input
                v-model="form.pic_id"
                type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="TODO: User select"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">TODO: Integrasi dengan user select</p>
            </div>
          </div>

          <!-- Unit Kerja & Due Date -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Unit Kerja <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.unit_kerja_id"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Unit Kerja</option>
                <option v-for="unit in unitKerjas" :key="unit.id" :value="unit.id">
                  {{ unit.nama }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Jatuh Tempo <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.due_date"
                type="date"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$router.push('/spmi/rtm-actions')"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <svg v-if="loading" class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isEditMode ? 'Simpan Perubahan' : 'Buat Action Item' }}
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useSPMIApi } from '@/composables/useSPMIApi';
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const route = useRoute();
const router = useRouter();

const { getRTMActionItem, createRTMActionItem, updateRTMActionItem, getRTMs, loading } = useSPMIApi();
const { getUnitKerjas } = useMasterDataApi();

const isEditMode = computed(() => !!route.params.id);

const rtms = ref([]);
const unitKerjas = ref([]);

const form = ref({
  rtm_id: '',
  title: '',
  description: '',
  priority: '',
  pic_id: '',
  unit_kerja_id: '',
  due_date: '',
});

const fetchRTMs = async () => {
  try {
    const response = await getRTMs({ per_page: 100 });
    if (response.success) {
      rtms.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch RTMs:', error);
  }
};

const fetchUnitKerjas = async () => {
  try {
    const response = await getUnitKerjas({ active_only: true });
    if (response.success) {
      unitKerjas.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch unit kerjas:', error);
  }
};

const fetchActionItem = async () => {
  try {
    const response = await getRTMActionItem(route.params.id);
    if (response.success) {
      const item = response.data;
      form.value = {
        rtm_id: item.rtm_id,
        title: item.title,
        description: item.description || '',
        priority: item.priority,
        pic_id: item.pic_id || '',
        unit_kerja_id: item.unit_kerja_id,
        due_date: item.due_date,
      };
    }
  } catch (error) {
    console.error('Failed to fetch action item:', error);
    alert('Gagal memuat data action item');
    router.push('/spmi/rtm-actions');
  }
};

const handleSubmit = async () => {
  try {
    const response = isEditMode.value
      ? await updateRTMActionItem(route.params.id, form.value)
      : await createRTMActionItem(form.value);

    if (response.success) {
      alert(isEditMode.value ? 'Action item berhasil diperbarui' : 'Action item berhasil dibuat');
      router.push('/spmi/rtm-actions');
    }
  } catch (error) {
    alert('Gagal menyimpan action item: ' + (error.response?.data?.message || error.message));
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

onMounted(() => {
  fetchRTMs();
  fetchUnitKerjas();
  if (isEditMode.value) {
    fetchActionItem();
  }
});
</script>
