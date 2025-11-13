<template>
  <MainLayout>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
        Selamat Datang di SIM Penjaminan Mutu
      </h1>
      <p class="text-gray-500 dark:text-gray-400">
        Dashboard overview sistem informasi manajemen penjaminan mutu
      </p>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 mb-6">
      <!-- Metric Card: Documents -->
      <MetricCard
        title="Total Dokumen"
        :value="24"
        :change="12"
        changeType="increase"
        subtitle="dari bulan lalu"
        :iconComponent="DocumentIcon"
        iconColor="text-blue-600 dark:text-blue-400"
        iconBgColor="bg-blue-100 dark:bg-blue-900/20"
      />

      <!-- Metric Card: Units -->
      <MetricCard
        title="Unit Kerja"
        :value="12"
        subtitle="Total unit aktif"
        :iconComponent="BuildingIcon"
        iconColor="text-green-600 dark:text-green-400"
        iconBgColor="bg-green-100 dark:bg-green-900/20"
      />

      <!-- Metric Card: Programs -->
      <MetricCard
        title="Program Studi"
        :value="18"
        subtitle="Terakreditasi"
        :iconComponent="BookIcon"
        iconColor="text-yellow-600 dark:text-yellow-400"
        iconBgColor="bg-yellow-100 dark:bg-yellow-900/20"
      />

      <!-- Metric Card: Users -->
      <MetricCard
        title="Pengguna"
        :value="156"
        :change="8"
        changeType="increase"
        subtitle="pengguna baru"
        :iconComponent="UsersIcon"
        iconColor="text-purple-600 dark:text-purple-400"
        iconBgColor="bg-purple-100 dark:bg-purple-900/20"
      />
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-12 gap-4 md:gap-6">
      <!-- Activities Section -->
      <div class="col-span-12 xl:col-span-7">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
          <div class="mb-6 flex items-center justify-between">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
              Aktivitas Terbaru
            </h4>
            <span class="rounded bg-blue-100 dark:bg-blue-900/20 px-3 py-1 text-xs font-medium text-blue-600 dark:text-blue-400">
              Hari Ini
            </span>
          </div>

          <div class="space-y-4">
            <!-- Activity Item 1 -->
            <div class="flex gap-4">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20 flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="flex-1">
                <h5 class="text-sm font-medium text-gray-900 dark:text-white">
                  Dokumen Baru Ditambahkan
                </h5>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Standar Operasional Prosedur Audit Internal
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                  2 jam yang lalu
                </p>
              </div>
            </div>

            <!-- Activity Item 2 -->
            <div class="flex gap-4">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20 flex-shrink-0">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <h5 class="text-sm font-medium text-gray-900 dark:text-white">
                  Audit Selesai
                </h5>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Audit mutu internal Fakultas Teknik
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                  5 jam yang lalu
                </p>
              </div>
            </div>

            <!-- Activity Item 3 -->
            <div class="flex gap-4">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/20 flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div class="flex-1">
                <h5 class="text-sm font-medium text-gray-900 dark:text-white">
                  Pengguna Baru Terdaftar
                </h5>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Dr. Ahmad Hidayat telah bergabung
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                  Kemarin
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="col-span-12 xl:col-span-5 space-y-6">
        <!-- Quick Actions -->
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
          <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
            Quick Actions
          </h4>

          <div class="space-y-3">
            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Tambah Dokumen
            </button>

            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Buat Audit
            </button>

            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Lihat Laporan
            </button>
          </div>
        </div>

        <!-- Schedule -->
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
          <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
            Jadwal Mendatang
          </h4>

          <div class="space-y-4">
            <div class="flex gap-4">
              <div class="flex flex-col items-center justify-center w-14 flex-shrink-0">
                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">15</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Nov</span>
              </div>
              <div class="flex-1">
                <h5 class="text-sm font-medium text-gray-900 dark:text-white">
                  Rapat Koordinasi
                </h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  10:00 - 12:00 WIB
                </p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex flex-col items-center justify-center w-14 flex-shrink-0">
                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">18</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Nov</span>
              </div>
              <div class="flex-1">
                <h5 class="text-sm font-medium text-gray-900 dark:text-white">
                  Audit Internal
                </h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  09:00 - 15:00 WIB
                </p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex flex-col items-center justify-center w-14 flex-shrink-0">
                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">20</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Nov</span>
              </div>
              <div class="flex-1">
                <h5 class="text-sm font-medium text-gray-900 dark:text-white">
                  Review Dokumen
                </h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  13:00 - 14:30 WIB
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { h } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import MetricCard from '@/components/dashboard/MetricCard.vue';

// Icon components as functions
const DocumentIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
  })
]);

const BuildingIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
  })
]);

const BookIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'
  })
]);

const UsersIcon = () => h('svg', {
  fill: 'none',
  stroke: 'currentColor',
  viewBox: '0 0 24 24',
}, [
  h('path', {
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round',
    'stroke-width': '2',
    d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
  })
]);
</script>
