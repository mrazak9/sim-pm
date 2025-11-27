<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Jadwal Audit' : 'Tambah Jadwal Audit' }}
          </h3>
          <button @click="$router.push('/audit/schedules')"
            class="text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="space-y-6">
          <!-- Audit Plan -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Rencana Audit <span class="text-red-600">*</span>
            </label>
            <select v-model="form.audit_plan_id" required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
              <option value="">Pilih Rencana Audit</option>
              <option v-for="plan in auditPlans" :key="plan.id" :value="plan.id">
                {{ plan.title }}
              </option>
            </select>
          </div>

          <!-- Unit Kerja -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Unit Kerja <span class="text-red-600">*</span>
            </label>
            <select v-model="form.unit_kerja_id" required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
              <option value="">Pilih Unit Kerja</option>
              <option v-for="unit in unitKerjas" :key="unit.id" :value="unit.id">
                {{ unit.nama_unit || unit.nama }}
              </option>
            </select>
          </div>

          <!-- Auditor Lead -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Auditor Lead <span class="text-red-600">*</span>
            </label>
            <UserSelect
              v-model="form.auditor_lead_id"
              placeholder="Pilih Auditor Lead..."
              :required="true"
            />
          </div>

          <!-- Scheduled Date -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Tanggal & Waktu Jadwal <span class="text-red-600">*</span>
            </label>
            <input v-model="form.scheduled_date" type="datetime-local" required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
          </div>

          <!-- Estimated Duration -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Durasi Perkiraan (menit) <span class="text-red-600">*</span>
            </label>
            <input v-model.number="form.estimated_duration" type="number" required min="30"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="120" />

            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default: 120 menit</p>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Catatan
            </label>
            <textarea v-model="form.notes" rows="4"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan catatan jadwal audit"></textarea>
          </div>

          <!-- Additional Auditors -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Auditor Tambahan
            </label>
            <UserSelect
              v-model="form.auditor_ids"
              :multiple="true"
              placeholder="Pilih auditor tambahan..."
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Pilih satu atau lebih auditor tambahan untuk membantu audit ini
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <button type="button" @click="$router.push('/audit/schedules')"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
            Batal
          </button>
          <button type="submit" :disabled="loading"
            class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50">
            <svg v-if="loading" class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            {{ isEditMode ? 'Simpan Perubahan' : 'Buat Jadwal Audit' }}
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
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const route = useRoute();
const router = useRouter();

const { getAuditSchedule, createAuditSchedule, updateAuditSchedule, getActiveAuditPlans, loading } = useAuditApi();
const { getUnitKerjas } = useMasterDataApi();

const isEditMode = computed(() => !!route.params.id);

const auditPlans = ref([]);
const unitKerjas = ref([]);

const form = ref({
  audit_plan_id: '',
  unit_kerja_id: '',
  auditor_lead_id: '',
  auditor_ids: [],
  scheduled_date: '',
  estimated_duration: 120,
  notes: '',
});

const fetchAuditPlans = async () => {
  try {
    const response = await getActiveAuditPlans();
    console.log('Audit Plans Response:', response);
    if (response.success) {
      auditPlans.value = response.data;
      console.log('Audit Plans:', auditPlans.value);
    }
  } catch (error) {
    console.error('Failed to fetch audit plans:', error);
  }
};

const fetchUnitKerjas = async () => {
  try {
    const response = await getUnitKerjas({ per_page: 100 });
    console.log('Unit Kerjas Response:', response);
    if (response.success) {
      unitKerjas.value = response.data;
      console.log('Unit Kerjas:', unitKerjas.value);
    }
  } catch (error) {
    console.error('Failed to fetch unit kerjas:', error);
  }
};

const fetchAuditSchedule = async () => {
  try {
    const response = await getAuditSchedule(route.params.id);
    if (response.success) {
      const schedule = response.data;
      form.value = {
        audit_plan_id: schedule.audit_plan_id,
        unit_kerja_id: schedule.unit_kerja_id,
        auditor_lead_id: schedule.auditor_lead_id,
        auditor_ids: schedule.auditors ? schedule.auditors.map(a => a.id) : [],
        scheduled_date: schedule.scheduled_date,
        estimated_duration: schedule.estimated_duration,
        notes: schedule.notes || '',
      };
    }
  } catch (error) {
    console.error('Failed to fetch audit schedule:', error);
    alert('Gagal memuat data jadwal audit');
    router.push('/audit/schedules');
  }
};

const handleSubmit = async () => {
  try {
    const response = isEditMode.value
      ? await updateAuditSchedule(route.params.id, form.value)
      : await createAuditSchedule(form.value);

    if (response.success) {
      alert(isEditMode.value ? 'Jadwal audit berhasil diperbarui' : 'Jadwal audit berhasil dibuat');
      router.push('/audit/schedules');
    }
  } catch (error) {
    alert('Gagal menyimpan jadwal audit: ' + (error.response?.data?.message || error.message));
  }
};

onMounted(() => {
  fetchAuditPlans();
  fetchUnitKerjas();
  if (isEditMode.value) {
    fetchAuditSchedule();
  }
});
</script>
