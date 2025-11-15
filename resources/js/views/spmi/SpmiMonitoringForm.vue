<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Monitoring' : 'Tambah Monitoring' }}
          </h3>
          <button
            @click="$router.push('/spmi/monitorings')"
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
          <!-- Standard & Tahun Akademik -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Standar SPMI <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.spmi_standard_id"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Standar</option>
                <option v-for="standard in standards" :key="standard.id" :value="standard.id">
                  {{ standard.name }}
                </option>
              </select>
            </div>

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
          </div>

          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Judul Monitoring <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan judul monitoring"
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
              placeholder="Masukkan deskripsi monitoring"
            ></textarea>
          </div>

          <!-- Monitoring Date & Type -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Monitoring <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.monitoring_date"
                type="date"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tipe Monitoring <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.monitoring_type"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Tipe Monitoring</option>
                <option value="desk_evaluation">Desk Evaluation</option>
                <option value="field_visit">Field Visit</option>
                <option value="interview">Interview</option>
                <option value="document_review">Document Review</option>
                <option value="mixed">Mixed</option>
              </select>
            </div>
          </div>

          <!-- Unit Kerja & Monitored By -->
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
                Dimonitor Oleh
              </label>
              <input
                v-model="form.monitored_by"
                type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="TODO: User select"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">TODO: Integrasi dengan user select</p>
            </div>
          </div>

          <!-- Findings (only for completion) -->
          <div v-if="isEditMode && form.status === 'ongoing'" class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-3">Hasil Monitoring (Opsional)</h4>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Temuan
                </label>
                <textarea
                  v-model="form.findings"
                  rows="3"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  placeholder="Masukkan temuan monitoring"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white">
                    Kekuatan
                  </label>
                  <textarea
                    v-model="form.strengths"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Masukkan kekuatan"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white">
                    Kelemahan
                  </label>
                  <textarea
                    v-model="form.weaknesses"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Masukkan kelemahan"
                  ></textarea>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white">
                    Peluang
                  </label>
                  <textarea
                    v-model="form.opportunities"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Masukkan peluang"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white">
                    Ancaman
                  </label>
                  <textarea
                    v-model="form.threats"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="Masukkan ancaman"
                  ></textarea>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Rekomendasi
                </label>
                <textarea
                  v-model="form.recommendations"
                  rows="3"
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  placeholder="Masukkan rekomendasi"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white">
                    Level Compliance
                  </label>
                  <select
                    v-model="form.compliance_level"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  >
                    <option value="">Pilih Level</option>
                    <option value="very_low">Sangat Rendah</option>
                    <option value="low">Rendah</option>
                    <option value="medium">Sedang</option>
                    <option value="high">Tinggi</option>
                    <option value="very_high">Sangat Tinggi</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-900 dark:text-white">
                    Skor Compliance (0-100)
                  </label>
                  <input
                    v-model.number="form.compliance_score"
                    type="number"
                    min="0"
                    max="100"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="0-100"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$router.push('/spmi/monitorings')"
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
            {{ isEditMode ? 'Simpan Perubahan' : 'Buat Monitoring' }}
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

const { getSPMIMonitoring, createSPMIMonitoring, updateSPMIMonitoring, getSPMIStandards, loading } = useSPMIApi();
const { getTahunAkademiks, getUnitKerjas } = useMasterDataApi();

const isEditMode = computed(() => !!route.params.id);

const standards = ref([]);
const tahunAkademiks = ref([]);
const unitKerjas = ref([]);

const form = ref({
  spmi_standard_id: '',
  tahun_akademik_id: '',
  title: '',
  description: '',
  monitoring_date: '',
  monitoring_type: '',
  unit_kerja_id: '',
  monitored_by: '',
  status: 'planned',
  findings: '',
  strengths: '',
  weaknesses: '',
  opportunities: '',
  threats: '',
  recommendations: '',
  compliance_level: '',
  compliance_score: null,
});

const fetchStandards = async () => {
  try {
    const response = await getSPMIStandards({ per_page: 100 });
    if (response.success) {
      standards.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch standards:', error);
  }
};

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

const fetchMonitoring = async () => {
  try {
    const response = await getSPMIMonitoring(route.params.id);
    if (response.success) {
      const monitoring = response.data;
      form.value = {
        spmi_standard_id: monitoring.spmi_standard_id,
        tahun_akademik_id: monitoring.tahun_akademik_id,
        title: monitoring.title,
        description: monitoring.description || '',
        monitoring_date: monitoring.monitoring_date,
        monitoring_type: monitoring.monitoring_type,
        unit_kerja_id: monitoring.unit_kerja_id,
        monitored_by: monitoring.monitored_by || '',
        status: monitoring.status,
        findings: monitoring.findings || '',
        strengths: monitoring.strengths || '',
        weaknesses: monitoring.weaknesses || '',
        opportunities: monitoring.opportunities || '',
        threats: monitoring.threats || '',
        recommendations: monitoring.recommendations || '',
        compliance_level: monitoring.compliance_level || '',
        compliance_score: monitoring.compliance_score,
      };
    }
  } catch (error) {
    console.error('Failed to fetch monitoring:', error);
    alert('Gagal memuat data monitoring');
    router.push('/spmi/monitorings');
  }
};

const handleSubmit = async () => {
  try {
    const response = isEditMode.value
      ? await updateSPMIMonitoring(route.params.id, form.value)
      : await createSPMIMonitoring(form.value);

    if (response.success) {
      alert(isEditMode.value ? 'Monitoring berhasil diperbarui' : 'Monitoring berhasil dibuat');
      router.push('/spmi/monitorings');
    }
  } catch (error) {
    alert('Gagal menyimpan monitoring: ' + (error.response?.data?.message || error.message));
  }
};

onMounted(() => {
  fetchStandards();
  fetchTahunAkademiks();
  fetchUnitKerjas();
  if (isEditMode.value) {
    fetchMonitoring();
  }
});
</script>
