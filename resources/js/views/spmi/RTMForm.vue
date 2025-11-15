<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit RTM' : 'Tambah RTM' }}
          </h3>
          <button
            @click="$router.push('/spmi/rtm')"
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
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Judul RTM <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan judul RTM"
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
              placeholder="Masukkan deskripsi RTM"
            ></textarea>
          </div>

          <!-- Tahun Akademik -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Tahun Akademik <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.tahun_akademik_id"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Pilih Tahun Akademik</option>
              <option v-for="ta in tahunAkademiks" :key="ta.id" :value="ta.id">
                {{ ta.nama }} ({{ ta.tahun_mulai }}/{{ ta.tahun_selesai }})
              </option>
            </select>
          </div>

          <!-- Meeting Date & Times -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Rapat <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.meeting_date"
                type="date"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Waktu Mulai <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.start_time"
                type="time"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Waktu Selesai <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.end_time"
                type="time"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>

          <!-- Location -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Lokasi Rapat
            </label>
            <input
              v-model="form.location"
              type="text"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan lokasi rapat"
            />
          </div>

          <!-- Agenda -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Agenda Rapat
            </label>
            <textarea
              v-model="form.agenda"
              rows="4"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan agenda rapat"
            ></textarea>
          </div>

          <!-- Chairman & Secretary -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Pimpinan Rapat
              </label>
              <input
                v-model="form.chairman_id"
                type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="TODO: User select"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">TODO: Integrasi dengan user select</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Sekretaris Rapat
              </label>
              <input
                v-model="form.secretary_id"
                type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="TODO: User select"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">TODO: Integrasi dengan user select</p>
            </div>
          </div>

          <!-- Completion Details (for ongoing RTMs) -->
          <div v-if="isEditMode && form.status === 'ongoing'" class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-3">Hasil Rapat (Opsional)</h4>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Poin Diskusi
                </label>
                <textarea
                  v-model="form.discussion_points"
                  rows="3"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  placeholder="Masukkan poin-poin diskusi"
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Keputusan
                </label>
                <textarea
                  v-model="form.decisions"
                  rows="3"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  placeholder="Masukkan keputusan rapat"
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Ringkasan Rapat
                </label>
                <textarea
                  v-model="form.minutes"
                  rows="3"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  placeholder="Masukkan ringkasan rapat"
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Rencana Tindak Lanjut
                </label>
                <textarea
                  v-model="form.follow_up_plan"
                  rows="3"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  placeholder="Masukkan rencana tindak lanjut"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Participant Management Todo -->
          <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
            <h4 class="text-sm font-semibold text-yellow-900 dark:text-yellow-300">TODO</h4>
            <p class="text-sm text-yellow-800 dark:text-yellow-200 mt-1">
              Implementasi manajemen peserta RTM (tambah/hapus peserta, mark attendance)
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$router.push('/spmi/rtm')"
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
            {{ isEditMode ? 'Simpan Perubahan' : 'Buat RTM' }}
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

const { getRTM, createRTM, updateRTM, loading } = useSPMIApi();
const { getTahunAkademiks } = useMasterDataApi();

const isEditMode = computed(() => !!route.params.id);

const tahunAkademiks = ref([]);

const form = ref({
  title: '',
  description: '',
  tahun_akademik_id: '',
  meeting_date: '',
  start_time: '',
  end_time: '',
  location: '',
  agenda: '',
  chairman_id: '',
  secretary_id: '',
  status: 'planned',
  discussion_points: '',
  decisions: '',
  minutes: '',
  follow_up_plan: '',
});

const fetchTahunAkademiks = async () => {
  try {
    const response = await getTahunAkademiks({ active_only: true });
    if (response.success) {
      tahunAkademiks.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch tahun akademiks:', error);
  }
};

const fetchRTM = async () => {
  try {
    const response = await getRTM(route.params.id);
    if (response.success) {
      const rtm = response.data;
      form.value = {
        title: rtm.title,
        description: rtm.description || '',
        tahun_akademik_id: rtm.tahun_akademik_id,
        meeting_date: rtm.meeting_date,
        start_time: rtm.start_time || '',
        end_time: rtm.end_time || '',
        location: rtm.location || '',
        agenda: rtm.agenda || '',
        chairman_id: rtm.chairman_id || '',
        secretary_id: rtm.secretary_id || '',
        status: rtm.status,
        discussion_points: rtm.discussion_points || '',
        decisions: rtm.decisions || '',
        minutes: rtm.minutes || '',
        follow_up_plan: rtm.follow_up_plan || '',
      };
    }
  } catch (error) {
    console.error('Failed to fetch RTM:', error);
    alert('Gagal memuat data RTM');
    router.push('/spmi/rtm');
  }
};

const handleSubmit = async () => {
  try {
    const response = isEditMode.value
      ? await updateRTM(route.params.id, form.value)
      : await createRTM(form.value);

    if (response.success) {
      alert(isEditMode.value ? 'RTM berhasil diperbarui' : 'RTM berhasil dibuat');
      router.push('/spmi/rtm');
    }
  } catch (error) {
    alert('Gagal menyimpan RTM: ' + (error.response?.data?.message || error.message));
  }
};

onMounted(() => {
  fetchTahunAkademiks();
  if (isEditMode.value) {
    fetchRTM();
  }
});
</script>
