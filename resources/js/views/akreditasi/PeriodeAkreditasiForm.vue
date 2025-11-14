<template>
  <MainLayout>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ isEdit ? 'Edit Periode Akreditasi' : 'Tambah Periode Akreditasi' }}
      </h1>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Isi form berikut untuk {{ isEdit ? 'mengupdate' : 'membuat' }} periode akreditasi baru
      </p>
    </div>

    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Nama Periode -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Nama Periode <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.nama"
            type="text"
            required
            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
            placeholder="Contoh: Akreditasi Institusi 2025"
          />
        </div>

        <!-- Jenis & Lembaga -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Jenis Akreditasi <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.jenis_akreditasi"
              required
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Pilih Jenis</option>
              <option value="institusi">Institusi</option>
              <option value="program_studi">Program Studi</option>
            </select>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Lembaga <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.lembaga"
              required
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Pilih Lembaga</option>
              <option value="BAN-PT">BAN-PT</option>
              <option value="LAM">LAM</option>
              <option value="Internasional">Internasional</option>
            </select>
          </div>
        </div>

        <!-- Instrumen & Jenjang -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Instrumen
            </label>
            <input
              v-model="form.instrumen"
              type="text"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Contoh: 4.0"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Jenjang
            </label>
            <input
              v-model="form.jenjang"
              type="text"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Contoh: S1, S2, S3, D3"
            />
          </div>
        </div>

        <!-- Timeline -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Tanggal Mulai
            </label>
            <input
              v-model="form.tanggal_mulai"
              type="date"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Deadline Pengumpulan
            </label>
            <input
              v-model="form.deadline_pengumpulan"
              type="date"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Jadwal Visitasi
            </label>
            <input
              v-model="form.jadwal_visitasi"
              type="date"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Tanggal Berakhir
            </label>
            <input
              v-model="form.tanggal_berakhir"
              type="date"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
          </div>
        </div>

        <!-- Status -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Status
          </label>
          <select
            v-model="form.status"
            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="persiapan">Persiapan</option>
            <option value="pengisian">Pengisian</option>
            <option value="review">Review</option>
            <option value="submit">Submit</option>
            <option value="visitasi">Visitasi</option>
            <option value="selesai">Selesai</option>
          </select>
        </div>

        <!-- Keterangan -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Keterangan
          </label>
          <textarea
            v-model="form.keterangan"
            rows="4"
            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
            placeholder="Masukkan keterangan periode akreditasi..."
          ></textarea>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
          {{ error }}
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <button
            type="submit"
            :disabled="loading"
            class="rounded-lg bg-blue-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
          >
            <span v-if="loading">Menyimpan...</span>
            <span v-else>{{ isEdit ? 'Update' : 'Simpan' }}</span>
          </button>
          <router-link
            to="/akreditasi/periode"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-center text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
          >
            Batal
          </router-link>
        </div>
      </form>
    </div>
  </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'
import MainLayout from '@/components/layout/MainLayout.vue'

const route = useRoute()
const router = useRouter()
const { loading, error, createPeriode, updatePeriode, getPeriodeDetail } = useAkreditasiApi()

const isEdit = computed(() => !!route.params.id)

const form = ref({
  nama: '',
  jenis_akreditasi: '',
  lembaga: '',
  instrumen: '4.0',
  jenjang: '',
  tanggal_mulai: '',
  deadline_pengumpulan: '',
  jadwal_visitasi: '',
  tanggal_berakhir: '',
  status: 'persiapan',
  keterangan: '',
})

const handleSubmit = async () => {
  try {
    if (isEdit.value) {
      await updatePeriode(route.params.id, form.value)
    } else {
      await createPeriode(form.value)
    }
    router.push('/akreditasi/periode')
  } catch (err) {
    console.error('Error saving periode:', err)
  }
}

onMounted(async () => {
  if (isEdit.value) {
    try {
      const response = await getPeriodeDetail(route.params.id)
      Object.assign(form.value, response.data)
    } catch (err) {
      console.error('Error loading periode:', err)
    }
  }
})
</script>
