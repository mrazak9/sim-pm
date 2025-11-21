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
            :class="[
              'block w-full rounded-lg border p-2.5 text-sm focus:ring-2',
              hasFieldError('nama')
                ? 'border-red-500 bg-red-50 text-red-900 placeholder-red-700 focus:border-red-500 focus:ring-red-500 dark:border-red-500 dark:bg-red-900/20 dark:text-red-400'
                : 'border-gray-300 bg-gray-50 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400'
            ]"
            placeholder="Contoh: Akreditasi Institusi 2025"
          />
          <p v-if="hasFieldError('nama')" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ getFieldError('nama') }}
          </p>
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

        <!-- Instrumen -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Instrumen <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.instrumen_id"
            required
            :disabled="loadingInstrumen"
            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="">{{ loadingInstrumen ? 'Memuat...' : 'Pilih Instrumen' }}</option>
            <option v-for="ins in filteredInstrumen" :key="ins.id" :value="ins.id">
              {{ ins.nama }} ({{ ins.kode }})
            </option>
          </select>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Instrumen akan difilter otomatis berdasarkan jenis akreditasi dan lembaga yang dipilih
          </p>
        </div>

        <!-- Program Studi (Only for jenis=program_studi) -->
        <div v-if="form.jenis_akreditasi === 'program_studi'">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Program Studi <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.program_studi_id"
            required
            :disabled="loadingProdi"
            @change="handleProdiChange"
            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="">{{ loadingProdi ? 'Memuat...' : 'Pilih Program Studi' }}</option>
            <option v-for="prodi in prodiList" :key="prodi.id" :value="prodi.id">
              {{ prodi.nama }} ({{ prodi.jenjang }})
            </option>
          </select>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Jenjang akan otomatis terisi dari program studi yang dipilih
          </p>
        </div>

        <!-- Jenjang (Read-only for program_studi, manual for institusi) -->
        <div v-if="form.jenis_akreditasi === 'institusi'">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Jenjang
          </label>
          <select
            v-model="form.jenjang"
            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
          >
            <option value="">Semua Jenjang</option>
            <option value="D3">D3</option>
            <option value="S1">S1</option>
            <option value="S2">S2</option>
            <option value="S3">S3</option>
          </select>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Untuk institusi, jenjang bersifat opsional
          </p>
        </div>
        <div v-else-if="form.jenis_akreditasi === 'program_studi' && form.jenjang">
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Jenjang
          </label>
          <input
            :value="form.jenjang"
            type="text"
            disabled
            class="block w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
          />
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Jenjang otomatis dari program studi yang dipilih
          </p>
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
        <div v-if="localError" class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
          <div class="flex items-start">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800 dark:text-red-400">
                Terjadi kesalahan saat menyimpan data
              </h3>
              <div v-if="typeof localError === 'string'" class="mt-2 text-sm text-red-700 dark:text-red-400">
                {{ localError }}
              </div>
              <div v-else-if="localError.errors" class="mt-2 text-sm text-red-700 dark:text-red-400">
                <ul class="list-disc space-y-1 pl-5">
                  <li v-for="(messages, field) in localError.errors" :key="field">
                    <span class="font-medium">{{ formatFieldName(field) }}:</span>
                    {{ Array.isArray(messages) ? messages[0] : messages }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
          <div class="flex items-start">
            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-400">
              {{ successMessage }}
            </p>
          </div>
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAkreditasiApi } from '@/composables/useAkreditasiApi'
import MainLayout from '@/layouts/MainLayout.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const { loading, error, createPeriode, updatePeriode, getPeriodeDetail } = useAkreditasiApi()

const isEdit = computed(() => !!route.params.id)
const localError = ref(null)
const successMessage = ref(null)

// Master data
const instrumenList = ref([])
const prodiList = ref([])
const loadingInstrumen = ref(false)
const loadingProdi = ref(false)

const form = ref({
  nama: '',
  jenis_akreditasi: '',
  lembaga: '',
  instrumen: null, // Keep for backward compatibility
  instrumen_id: null, // New field for instrumen FK
  jenjang: '',
  program_studi_id: null,
  tanggal_mulai: '',
  deadline_pengumpulan: '',
  jadwal_visitasi: '',
  tanggal_berakhir: '',
  status: 'persiapan',
  keterangan: '',
})

// Filtered instrumen based on jenis and lembaga
const filteredInstrumen = computed(() => {
  if (!form.value.jenis_akreditasi || !form.value.lembaga) {
    return instrumenList.value
  }

  return instrumenList.value.filter(ins => {
    const jenisMatch = ins.jenis === form.value.jenis_akreditasi || ins.jenis === 'both'
    const lembagaMatch = !ins.lembaga || ins.lembaga === form.value.lembaga
    return jenisMatch && lembagaMatch
  })
})

// Load instrumen from API
const loadInstrumen = async () => {
  loadingInstrumen.value = true
  try {
    const response = await axios.get('/api/instrumen-akreditasi')
    if (response.data.success) {
      instrumenList.value = response.data.data
    }
  } catch (err) {
    console.error('Failed to load instrumen:', err)
  } finally {
    loadingInstrumen.value = false
  }
}

// Load program studi from API
const loadProdi = async () => {
  loadingProdi.value = true
  try {
    const response = await axios.get('/api/program-studi')
    if (response.data.success) {
      prodiList.value = response.data.data
    }
  } catch (err) {
    console.error('Failed to load program studi:', err)
  } finally {
    loadingProdi.value = false
  }
}

// Handle prodi change - auto fill jenjang
const handleProdiChange = () => {
  if (form.value.program_studi_id) {
    const selectedProdi = prodiList.value.find(p => p.id === parseInt(form.value.program_studi_id))
    if (selectedProdi) {
      form.value.jenjang = selectedProdi.jenjang
    }
  } else {
    form.value.jenjang = ''
  }
}

// Watch jenis_akreditasi change
watch(() => form.value.jenis_akreditasi, (newVal) => {
  // Clear program_studi_id when switching to institusi
  if (newVal === 'institusi') {
    form.value.program_studi_id = null
  }
  // Clear jenjang when switching
  if (newVal === 'program_studi') {
    form.value.jenjang = ''
  }
})

// Watch jenis or lembaga change - reset instrumen selection if no longer valid
watch([() => form.value.jenis_akreditasi, () => form.value.lembaga], () => {
  if (form.value.instrumen_id) {
    const stillValid = filteredInstrumen.value.find(ins => ins.id === form.value.instrumen_id)
    if (!stillValid) {
      form.value.instrumen_id = null
    }
  }
})

// Clear messages when form changes
watch(form, () => {
  if (localError.value || successMessage.value) {
    localError.value = null
    successMessage.value = null
  }
}, { deep: true })

const formatFieldName = (field) => {
  const fieldNames = {
    nama: 'Nama Periode',
    jenis_akreditasi: 'Jenis Akreditasi',
    lembaga: 'Lembaga',
    instrumen: 'Instrumen',
    jenjang: 'Jenjang',
    tanggal_mulai: 'Tanggal Mulai',
    deadline_pengumpulan: 'Deadline Pengumpulan',
    jadwal_visitasi: 'Jadwal Visitasi',
    tanggal_berakhir: 'Tanggal Berakhir',
    status: 'Status',
    keterangan: 'Keterangan',
  }
  return fieldNames[field] || field
}

const hasFieldError = (field) => {
  return localError.value?.errors && localError.value.errors[field]
}

const getFieldError = (field) => {
  if (!hasFieldError(field)) return null
  const error = localError.value.errors[field]
  return Array.isArray(error) ? error[0] : error
}

const handleSubmit = async () => {
  // Clear previous messages
  localError.value = null
  successMessage.value = null

  try {
    // Prepare data - convert empty strings to null for date fields
    const data = { ...form.value }
    const dateFields = ['tanggal_mulai', 'deadline_pengumpulan', 'jadwal_visitasi', 'tanggal_berakhir']

    dateFields.forEach(field => {
      if (data[field] === '' || data[field] === null) {
        data[field] = null
      }
    })

    // Set instrumen kode from selected instrumen_id
    if (data.instrumen_id) {
      const selectedInstrumen = instrumenList.value.find(ins => ins.id === parseInt(data.instrumen_id))
      data.instrumen = selectedInstrumen ? selectedInstrumen.kode : null
    } else {
      data.instrumen = null
    }

    // Clean up empty strings for other optional fields
    if (data.jenjang === '') data.jenjang = null
    if (data.keterangan === '') data.keterangan = null
    if (data.program_studi_id === '' || data.program_studi_id === null) data.program_studi_id = null

    let response
    if (isEdit.value) {
      response = await updatePeriode(route.params.id, data)
      successMessage.value = 'Periode akreditasi berhasil diupdate!'
    } else {
      response = await createPeriode(data)
      successMessage.value = 'Periode akreditasi berhasil dibuat!'
    }

    // Show success message briefly then redirect
    setTimeout(() => {
      router.push('/akreditasi/periode')
    }, 1500)
  } catch (err) {
    console.error('Error saving periode:', err)

    // Handle different error types
    if (err.response?.data) {
      const errorData = err.response.data

      // Laravel validation errors
      if (errorData.errors) {
        localError.value = {
          message: errorData.message || 'Validasi gagal',
          errors: errorData.errors
        }
      }
      // Simple error message
      else if (errorData.message) {
        localError.value = errorData.message
      }
      // Unknown error format
      else {
        localError.value = 'Terjadi kesalahan saat menyimpan data'
      }
    }
    // Network or other errors
    else if (err.message) {
      localError.value = err.message
    }
    // Fallback error
    else {
      localError.value = 'Terjadi kesalahan yang tidak diketahui'
    }

    // Scroll to top to show error
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

onMounted(async () => {
  // Load master data first
  await Promise.all([loadInstrumen(), loadProdi()])

  if (isEdit.value) {
    localError.value = null
    try {
      const response = await getPeriodeDetail(route.params.id)
      Object.assign(form.value, response.data)

      // Set instrumen_id from instrumen kode for edit mode
      if (form.value.instrumen) {
        const foundInstrumen = instrumenList.value.find(ins => ins.kode === form.value.instrumen)
        if (foundInstrumen) {
          form.value.instrumen_id = foundInstrumen.id
        }
      }
    } catch (err) {
      console.error('Error loading periode:', err)
      localError.value = 'Gagal memuat data periode akreditasi'

      // Scroll to show error
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }
})
</script>
