<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ isEdit ? 'Edit Progress RTL' : 'Tambah Progress RTL' }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              {{ rtlInfo ? `RTL: ${rtlInfo.rtl_code} - ${rtlInfo.audit_finding?.description}` : 'Memuat informasi RTL...' }}
            </p>
          </div>
          <button
            @click="handleCancel"
            type="button"
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Progress Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tanggal Progress <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.progress_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <!-- Progress Percentage -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Persentase Progress (%) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.progress_percentage"
              type="number"
              min="0"
              max="100"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            />
            <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
              <div
                class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                :style="{ width: `${form.progress_percentage}%` }"
              ></div>
            </div>
          </div>

          <!-- Description -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Deskripsi Progress <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="Jelaskan progress yang telah dicapai..."
            ></textarea>
          </div>

          <!-- Achievements -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Pencapaian
            </label>
            <textarea
              v-model="form.achievements"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="Capaian atau hasil yang telah diperoleh..."
            ></textarea>
          </div>

          <!-- Challenges -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tantangan/Kendala
            </label>
            <textarea
              v-model="form.challenges"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="Kendala atau tantangan yang dihadapi..."
            ></textarea>
          </div>

          <!-- Next Steps -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Langkah Selanjutnya
            </label>
            <textarea
              v-model="form.next_steps"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="Rencana atau langkah yang akan dilakukan selanjutnya..."
            ></textarea>
          </div>

          <!-- Next Review Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tanggal Review Selanjutnya
            </label>
            <input
              v-model="form.next_review_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <!-- Evidence File -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              File Bukti
            </label>
            <input
              @change="handleFileChange"
              type="file"
              accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Format: PDF, Word, Excel, Gambar (Max: 10MB)
            </p>
          </div>

          <!-- Is Milestone -->
          <div class="md:col-span-2">
            <label class="flex items-center">
              <input
                v-model="form.is_milestone"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              />
              <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                Tandai sebagai Milestone (pencapaian penting)
              </span>
            </label>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            @click="handleCancel"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Progress' }}
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useToast } from '@/composables/useToast';
import { useAuditApi } from '@/composables/useAuditApi';

const router = useRouter();
const route = useRoute();
const { success, error } = useToast();
const { getRTL, addRTLProgress } = useAuditApi();

const rtlId = computed(() => route.params.id);
const isEdit = ref(false);
const isSubmitting = ref(false);
const rtlInfo = ref(null);

const form = ref({
  progress_date: new Date().toISOString().split('T')[0],
  progress_percentage: 0,
  description: '',
  achievements: '',
  challenges: '',
  next_steps: '',
  next_review_date: '',
  evidence_file: null,
  is_milestone: false,
});

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Check file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
      error('Ukuran file maksimal 10MB');
      event.target.value = '';
      return;
    }
    form.value.evidence_file = file;
  }
};

const loadRTLInfo = async () => {
  try {
    const response = await getRTL(rtlId.value);
    if (response.data.success) {
      rtlInfo.value = response.data.data;
    }
  } catch (err) {
    console.error('Error loading RTL info:', err);
    error('Gagal memuat informasi RTL');
  }
};

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;

    // Prepare data object - addRTLProgress will handle FormData conversion
    const data = {
      progress_date: form.value.progress_date,
      progress_percentage: form.value.progress_percentage,
      description: form.value.description,
      achievements: form.value.achievements || '',
      challenges: form.value.challenges || '',
      next_steps: form.value.next_steps || '',
      next_review_date: form.value.next_review_date || '',
      is_milestone: form.value.is_milestone ? '1' : '0',
    };

    if (form.value.evidence_file) {
      data.evidence_file = form.value.evidence_file;
    }

    const response = await addRTLProgress(rtlId.value, data);

    if (response.success) {
      success('Progress RTL berhasil ditambahkan');
      router.push('/audit/rtls');
    } else {
      error(response.message || 'Gagal menyimpan progress RTL');
    }
  } catch (err) {
    console.error('Error saving RTL progress:', err);
    let errorMessage = 'Gagal menyimpan progress RTL';

    if (err.response?.data) {
      const data = err.response.data;
      console.log('Error response:', data);

      if (data.errors) {
        const errorList = Object.values(data.errors).flat();
        errorMessage = errorList.join(', ');
      } else if (data.message) {
        errorMessage = data.message;
      }
    }

    error(errorMessage, 5000);
  } finally {
    isSubmitting.value = false;
  }
};

const handleCancel = () => {
  router.push('/audit/rtls');
};

onMounted(() => {
  loadRTLInfo();
});
</script>
