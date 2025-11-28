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
                {{ ta.nama_tahun }} - {{ ta.semester_label }}
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
              <UserSelect
                v-model="form.chairman_id"
                :multiple="false"
                placeholder="Pilih Pimpinan Rapat"
                class="mt-1"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Sekretaris Rapat
              </label>
              <UserSelect
                v-model="form.secretary_id"
                :multiple="false"
                placeholder="Pilih Sekretaris Rapat"
                class="mt-1"
              />
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

          <!-- Participant Management -->
          <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="mb-4 flex items-center justify-between">
              <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Peserta Rapat</h4>
              <button
                type="button"
                @click="showAddParticipantModal = true"
                class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
              >
                <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Peserta
              </button>
            </div>

            <!-- Participants List -->
            <div v-if="participants.length === 0" class="rounded-lg bg-gray-50 p-4 text-center text-sm text-gray-500 dark:bg-gray-700 dark:text-gray-400">
              Belum ada peserta ditambahkan
            </div>
            <div v-else class="space-y-2">
              <div
                v-for="(participant, index) in participants"
                :key="index"
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-600 dark:bg-gray-700"
              >
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ participant.user_name || 'Loading...' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ participant.role || 'Peserta' }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <label v-if="isEditMode" class="inline-flex items-center">
                    <input
                      type="checkbox"
                      v-model="participant.attended"
                      class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Hadir</span>
                  </label>
                  <button
                    type="button"
                    @click="removeParticipant(index)"
                    class="text-red-600 hover:text-red-700 dark:text-red-400"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Add Participant Modal -->
          <div v-if="showAddParticipantModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.self="showAddParticipantModal = false">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Peserta</h3>
                <button @click="showAddParticipantModal = false" class="text-gray-600 hover:text-gray-700 dark:text-gray-400">
                  <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white">Pilih User</label>
                  <UserSelect
                    v-model="newParticipant.user_id"
                    :multiple="false"
                    placeholder="Pilih User"
                    class="mt-1"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white">Role</label>
                  <select
                    v-model="newParticipant.role"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  >
                    <option value="Peserta">Peserta</option>
                    <option value="Notulen">Notulen</option>
                    <option value="Pembicara">Pembicara</option>
                  </select>
                </div>
              </div>
              <div class="mt-6 flex items-center justify-end gap-3">
                <button
                  type="button"
                  @click="showAddParticipantModal = false"
                  class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                >
                  Batal
                </button>
                <button
                  type="button"
                  @click="addParticipant"
                  class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                  Tambah
                </button>
              </div>
            </div>
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
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import UserSelect from '@/components/common/UserSelect.vue';
import { useSPMIApi } from '@/composables/useSPMIApi';
import { useMasterDataApi } from '@/composables/useMasterDataApi';
import { useToast } from '@/composables/useToast';

const route = useRoute();
const router = useRouter();
const { success, error } = useToast();

const { getRTM, createRTM, updateRTM, loading } = useSPMIApi();
const { getTahunAkademiks } = useMasterDataApi();

const isEditMode = computed(() => !!route.params.id);

const tahunAkademiks = ref([]);
const participants = ref([]);
const showAddParticipantModal = ref(false);
const newParticipant = ref({
  user_id: '',
  role: 'Peserta',
});

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

      // Load participants if available
      if (rtm.participants && Array.isArray(rtm.participants)) {
        participants.value = rtm.participants.map(p => ({
          user_id: p.user_id,
          user_name: p.user_name,
          role: p.role || 'Peserta',
          attended: p.attended || false,
        }));
      }
    }
  } catch (err) {
    console.error('Failed to fetch RTM:', err);
    error('Gagal memuat data RTM');
    router.push('/spmi/rtm');
  }
};

const addParticipant = () => {
  if (!newParticipant.value.user_id) {
    error('Silakan pilih user terlebih dahulu');
    return;
  }

  // Check if participant already exists
  const exists = participants.value.some(p => p.user_id === newParticipant.value.user_id);
  if (exists) {
    error('Peserta sudah ditambahkan');
    return;
  }

  participants.value.push({
    user_id: newParticipant.value.user_id,
    user_name: 'User ' + newParticipant.value.user_id, // Will be updated by UserSelect component
    role: newParticipant.value.role,
    attended: false,
  });

  // Reset form
  newParticipant.value = {
    user_id: '',
    role: 'Peserta',
  };
  showAddParticipantModal.value = false;
  success('Peserta berhasil ditambahkan');
};

const removeParticipant = (index) => {
  if (confirm('Apakah Anda yakin ingin menghapus peserta ini?')) {
    participants.value.splice(index, 1);
    success('Peserta berhasil dihapus');
  }
};

const handleSubmit = async () => {
  try {
    // Prepare data with participants
    const data = {
      ...form.value,
      participants: participants.value.map(p => ({
        user_id: p.user_id,
        role: p.role,
        attended: p.attended || false,
      })),
    };

    const response = isEditMode.value
      ? await updateRTM(route.params.id, data)
      : await createRTM(data);

    if (response.success) {
      success(isEditMode.value ? 'RTM berhasil diperbarui' : 'RTM berhasil dibuat');
      router.push('/spmi/rtm');
    }
  } catch (err) {
    console.error('Failed to save RTM:', err);
    error('Gagal menyimpan RTM: ' + (err.response?.data?.message || err.message));
  }
};

onMounted(() => {
  fetchTahunAkademiks();
  if (isEditMode.value) {
    fetchRTM();
  }
});
</script>
