<template>
  <MainLayout>
    <div>
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit Instrumen Akreditasi' : 'Tambah Instrumen Akreditasi' }}
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Isi form berikut untuk {{ isEdit ? 'mengupdate' : 'membuat' }} instrumen akreditasi
        </p>
      </div>

      <!-- Form -->
      <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Kode -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Kode Instrumen <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.kode"
              type="text"
              required
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Contoh: IAPT 9.0"
            />
            <p v-if="errors.kode" class="mt-1 text-sm text-red-600">{{ errors.kode[0] }}</p>
          </div>

          <!-- Nama -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Nama Instrumen <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.nama"
              type="text"
              required
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Contoh: Instrumen Akreditasi Program Studi 9.0"
            />
            <p v-if="errors.nama" class="mt-1 text-sm text-red-600">{{ errors.nama[0] }}</p>
          </div>

          <!-- Deskripsi -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi
            </label>
            <textarea
              v-model="form.deskripsi"
              rows="3"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Deskripsi instrumen..."
            ></textarea>
            <p v-if="errors.deskripsi" class="mt-1 text-sm text-red-600">{{ errors.deskripsi[0] }}</p>
          </div>

          <!-- Jenis & Lembaga -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Jenis Akreditasi <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.jenis"
                required
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Jenis</option>
                <option value="program_studi">Program Studi</option>
                <option value="institusi">Institusi</option>
                <option value="both">Program Studi & Institusi</option>
              </select>
              <p v-if="errors.jenis" class="mt-1 text-sm text-red-600">{{ errors.jenis[0] }}</p>
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
              <p v-if="errors.lembaga" class="mt-1 text-sm text-red-600">{{ errors.lembaga[0] }}</p>
            </div>
          </div>

          <!-- Tahun & Status -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Tahun Berlaku <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="form.tahun_berlaku"
                type="number"
                required
                min="2000"
                max="2100"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
              <p v-if="errors.tahun_berlaku" class="mt-1 text-sm text-red-600">{{ errors.tahun_berlaku[0] }}</p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Status
              </label>
              <div class="flex items-center space-x-4 pt-2">
                <label class="inline-flex items-center">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-900 dark:text-white">Aktif</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
            <p class="text-sm font-medium text-red-800 dark:text-red-400">
              {{ errorMessage }}
            </p>
          </div>

          <!-- Success Message -->
          <div v-if="successMessage" class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
            <p class="text-sm font-medium text-green-800 dark:text-green-400">
              {{ successMessage }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3">
            <button
              type="submit"
              :disabled="loading"
              class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50"
            >
              <span v-if="loading">Menyimpan...</span>
              <span v-else>{{ isEdit ? 'Update' : 'Simpan' }}</span>
            </button>
            <router-link
              to="/akreditasi/instrumen"
              class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
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
import MainLayout from '@/layouts/MainLayout.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const errors = ref({})

const form = ref({
  kode: '',
  nama: '',
  deskripsi: '',
  jenis: '',
  lembaga: '',
  tahun_berlaku: new Date().getFullYear(),
  is_active: true,
})

const fetchInstrumen = async () => {
  if (!isEdit.value) return

  try {
    const response = await axios.get(`/api/instrumen-akreditasi/${route.params.id}`)
    if (response.data.success) {
      form.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch instrumen:', error)
    errorMessage.value = 'Gagal memuat data instrumen'
  }
}

const handleSubmit = async () => {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  errors.value = {}

  try {
    const url = isEdit.value
      ? `/api/instrumen-akreditasi/${route.params.id}`
      : '/api/instrumen-akreditasi'

    const method = isEdit.value ? 'put' : 'post'
    const response = await axios[method](url, form.value)

    if (response.data.success) {
      successMessage.value = response.data.message
      setTimeout(() => {
        router.push('/akreditasi/instrumen')
      }, 1500)
    }
  } catch (error) {
    console.error('Failed to save instrumen:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      errorMessage.value = error.response?.data?.message || 'Gagal menyimpan instrumen'
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (isEdit.value) {
    fetchInstrumen()
  }
})
</script>
