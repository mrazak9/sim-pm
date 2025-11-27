<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Rencana Tindak Lanjut' : 'Tambah Rencana Tindak Lanjut' }}
          </h3>
          <button
            @click="$router.push('/audit/rtls')"
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
          <!-- Audit Finding -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Temuan Audit <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.audit_finding_id"
              @change="onAuditFindingChange"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Pilih Temuan Audit</option>
              <option v-for="finding in auditFindings" :key="finding.id" :value="finding.id">
                {{ finding.finding_code || 'N/A' }} - {{ finding.description?.substring(0, 100) }}{{ finding.description?.length > 100 ? '...' : '' }}
              </option>
            </select>
            <p v-if="form.unit_kerja_id" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Unit Kerja: {{ selectedUnitKerjaName }}
            </p>
          </div>

          <!-- Action Plan -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Rencana Tindakan <span class="text-red-600">*</span>
            </label>
            <textarea
              v-model="form.action_plan"
              rows="4"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Jelaskan rencana tindakan yang akan dilakukan"
            ></textarea>
          </div>

          <!-- Success Indicator -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Indikator Keberhasilan <span class="text-red-600">*</span>
            </label>
            <textarea
              v-model="form.success_indicator"
              rows="4"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Jelaskan indikator yang menunjukkan keberhasilan tindakan"
            ></textarea>
          </div>

          <!-- PIC (Person In Charge) -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Penanggung Jawab (PIC) <span class="text-red-600">*</span>
            </label>
            <UserSelect
              v-model="form.pic_id"
              :required="true"
              placeholder="Pilih Penanggung Jawab"
            />
          </div>

          <!-- Target Date -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Target Tanggal Selesai <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.target_date"
              type="date"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <!-- Budget -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Anggaran (opsional)
            </label>
            <input
              v-model.number="form.budget"
              type="number"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan anggaran"
            />
          </div>

          <!-- Risk Level -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Tingkat Risiko <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.risk_level"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Pilih Tingkat Risiko</option>
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
            @click="$router.push('/audit/rtls')"
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
            {{ isEditMode ? 'Simpan Perubahan' : 'Buat RTL' }}
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
import UserSelect from '@/components/common/UserSelect.vue';
import { useAuditApi } from '@/composables/useAuditApi';
import { useToast } from '@/composables/useToast';

const route = useRoute();
const router = useRouter();

const { getAuditFindings, getRTL, createRTL, updateRTL, loading } = useAuditApi();
const { success, error } = useToast();

const isEditMode = computed(() => !!route.params.id);

const auditFindings = ref([]);

const form = ref({
  audit_finding_id: '',
  unit_kerja_id: '',
  action_plan: '',
  success_indicator: '',
  pic_id: '',
  target_date: '',
  budget: null,
  risk_level: 'medium',
});

const fetchAuditFindings = async () => {
  try {
    const response = await getAuditFindings({ per_page: 100, status: 'open' });
    if (response.success) {
      auditFindings.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch audit findings:', error);
  }
};

const fetchRTL = async () => {
  try {
    const response = await getRTL(route.params.id);
    if (response.success) {
      const rtl = response.data;
      form.value = {
        audit_finding_id: rtl.audit_finding_id,
        unit_kerja_id: rtl.unit_kerja_id,
        action_plan: rtl.action_plan,
        success_indicator: rtl.success_indicator || '',
        pic_id: rtl.pic_id,
        target_date: rtl.target_date,
        budget: rtl.budget,
        risk_level: rtl.risk_level,
      };
    }
  } catch (err) {
    console.error('Failed to fetch RTL:', err);
    error('Gagal memuat data RTL');
    router.push('/audit/rtls');
  }
};

const onAuditFindingChange = () => {
  const selectedFinding = auditFindings.value.find(f => f.id === form.value.audit_finding_id);
  if (selectedFinding && selectedFinding.unit_kerja_id) {
    form.value.unit_kerja_id = selectedFinding.unit_kerja_id;
  }
};

const selectedUnitKerjaName = computed(() => {
  if (!form.value.unit_kerja_id) return '';
  const selectedFinding = auditFindings.value.find(f => f.id === form.value.audit_finding_id);
  return selectedFinding?.unit_kerja?.nama || '';
});

const handleSubmit = async () => {
  try {
    const response = isEditMode.value
      ? await updateRTL(route.params.id, form.value)
      : await createRTL(form.value);

    if (response.success) {
      success(isEditMode.value ? 'RTL berhasil diperbarui' : 'RTL berhasil dibuat');
      router.push('/audit/rtls');
    }
  } catch (err) {
    console.error('Error saving RTL:', err);

    let errorMessage = 'Gagal menyimpan RTL';

    if (err.response?.data) {
      const data = err.response.data;
      if (data.errors) {
        const errorList = Object.values(data.errors).flat();
        errorMessage = errorList.join(', ');
      } else if (data.message) {
        errorMessage = data.message;
      }
    } else if (err.message) {
      errorMessage = err.message;
    }

    error(errorMessage, 5000);
  }
};

onMounted(() => {
  fetchAuditFindings();
  if (isEditMode.value) {
    fetchRTL();
  }
});
</script>
