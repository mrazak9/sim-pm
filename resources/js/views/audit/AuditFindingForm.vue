<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Temuan Audit' : 'Tambah Temuan Audit' }}
          </h3>
          <button
            @click="$router.push('/audit/findings')"
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
          <!-- Audit Schedule -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Jadwal Audit <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.audit_schedule_id"
              @change="onAuditScheduleChange"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Pilih Jadwal Audit</option>
              <option v-for="schedule in auditSchedules" :key="schedule.id" :value="schedule.id">
                {{ schedule.unit_kerja?.nama }} ({{ formatDate(schedule.scheduled_date) }})
              </option>
            </select>
            <p v-if="form.unit_kerja_id" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Unit Kerja: {{ selectedUnitKerjaName }}
            </p>
          </div>

          <!-- Category Selection (Radio) -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Kategori Temuan <span class="text-red-600">*</span>
            </label>
            <div class="mt-3 space-y-3">
              <div class="flex items-center">
                <input
                  v-model="form.category"
                  id="category-major"
                  type="radio"
                  value="major"
                  class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                  required
                />
                <label for="category-major" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                  Major
                </label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.category"
                  id="category-minor"
                  type="radio"
                  value="minor"
                  class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                  required
                />
                <label for="category-minor" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                  Minor
                </label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.category"
                  id="category-ofi"
                  type="radio"
                  value="ofi"
                  class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                  required
                />
                <label for="category-ofi" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                  OFI
                </label>
              </div>
            </div>
          </div>

          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Judul Temuan <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan judul temuan"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi Temuan <span class="text-red-600">*</span>
            </label>
            <textarea
              v-model="form.description"
              rows="4"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan deskripsi temuan"
            ></textarea>
          </div>

          <!-- Evidence Description -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi Bukti <span class="text-red-600">*</span>
            </label>
            <textarea
              v-model="form.evidence_description"
              rows="4"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan deskripsi bukti yang ditemukan"
            ></textarea>
          </div>

          <!-- Root Cause -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Akar Penyebab <span class="text-red-600">*</span>
            </label>
            <textarea
              v-model="form.root_cause"
              rows="4"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Jelaskan akar penyebab dari temuan ini"
            ></textarea>
          </div>

          <!-- Recommendation -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Rekomendasi <span class="text-red-600">*</span>
            </label>
            <textarea
              v-model="form.recommendation"
              rows="4"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan rekomendasi untuk mengatasi temuan"
            ></textarea>
          </div>

          <!-- Priority -->
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

          <!-- Severity -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Tingkat Keparahan <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.severity"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Pilih Tingkat Keparahan</option>
              <option value="low">Rendah</option>
              <option value="medium">Sedang</option>
              <option value="high">Tinggi</option>
            </select>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$router.push('/audit/findings')"
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
            {{ isEditMode ? 'Simpan Perubahan' : 'Buat Temuan Audit' }}
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
import { useAuditApi } from '@/composables/useAuditApi';
import { useToast } from '@/composables/useToast';

const route = useRoute();
const router = useRouter();

const { getAuditSchedules, getAuditFinding, createAuditFinding, updateAuditFinding, loading } = useAuditApi();
const { success, error } = useToast();

const isEditMode = computed(() => !!route.params.id);

const auditSchedules = ref([]);

const form = ref({
  audit_schedule_id: '',
  unit_kerja_id: '',
  category: 'major',
  title: '',
  description: '',
  evidence_description: '',
  root_cause: '',
  recommendation: '',
  priority: 'medium',
  severity: 'medium',
});

const fetchAuditSchedules = async () => {
  try {
    const response = await getAuditSchedules({ per_page: 100 });
    if (response.success) {
      auditSchedules.value = response.data;
    }
  } catch (err) {
    console.error('Failed to fetch audit schedules:', err);
    error('Gagal memuat daftar jadwal audit');
  }
};

const fetchAuditFinding = async () => {
  try {
    const response = await getAuditFinding(route.params.id);
    if (response.success) {
      const finding = response.data;
      form.value = {
        audit_schedule_id: finding.audit_schedule_id,
        unit_kerja_id: finding.unit_kerja_id,
        category: finding.category,
        title: finding.title,
        description: finding.description,
        evidence_description: finding.evidence_description || '',
        root_cause: finding.root_cause || '',
        recommendation: finding.recommendation || '',
        priority: finding.priority,
        severity: finding.severity,
      };
    }
  } catch (err) {
    console.error('Failed to fetch audit finding:', err);
    error('Gagal memuat data temuan audit');
    router.push('/audit/findings');
  }
};

const handleSubmit = async () => {
  try {
    const response = isEditMode.value
      ? await updateAuditFinding(route.params.id, form.value)
      : await createAuditFinding(form.value);

    if (response.success) {
      success(isEditMode.value ? 'Temuan audit berhasil diperbarui' : 'Temuan audit berhasil dibuat');
      router.push('/audit/findings');
    } else {
      // Handle case when success is false
      const errorMsg = response.message || 'Gagal menyimpan temuan audit';
      error(errorMsg);
    }
  } catch (err) {
    console.error('Error saving audit finding:', err);
    console.error('Error response:', err.response);
    console.error('Error response data:', err.response?.data);

    // Extract error message from different possible error structures
    let errorMessage = 'Gagal menyimpan temuan audit';

    if (err.response?.data) {
      const data = err.response.data;

      console.log('Parsing error data:', data);

      // Check for validation errors
      if (data.errors) {
        const errorList = Object.values(data.errors).flat();
        errorMessage = errorList.join(', ');
        console.log('Validation errors:', errorMessage);
      } else if (data.message) {
        errorMessage = data.message;
        console.log('Error message:', errorMessage);
      }
    } else if (err.message) {
      errorMessage = err.message;
    }

    console.log('Final error message to show:', errorMessage);
    error(errorMessage, 5000); // Show for 5 seconds
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const onAuditScheduleChange = () => {
  const selectedSchedule = auditSchedules.value.find(s => s.id === form.value.audit_schedule_id);
  if (selectedSchedule && selectedSchedule.unit_kerja_id) {
    form.value.unit_kerja_id = selectedSchedule.unit_kerja_id;
  }
};

const selectedUnitKerjaName = computed(() => {
  if (!form.value.unit_kerja_id) return '';
  const selectedSchedule = auditSchedules.value.find(s => s.id === form.value.audit_schedule_id);
  return selectedSchedule?.unit_kerja?.nama || '';
});

onMounted(() => {
  fetchAuditSchedules();
  if (isEditMode.value) {
    fetchAuditFinding();
  }
});
</script>
