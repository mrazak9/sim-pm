<template>
  <MainLayout>
    <!-- Welcome Banner -->
    <div class="rounded-xl shadow-lg p-8 mb-8" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <h1 class="text-3xl font-bold text-white mb-2">Selamat Datang di SIM Penjaminan Mutu!</h1>
      <p class="text-white" style="opacity: 0.9;">Sistem Informasi Manajemen Penjaminan Mutu Universitas</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Stat Card 1: Documents -->
      <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" style="color: #6b7280;">Total Dokumen</p>
            <p class="text-3xl font-bold mt-2" style="color: #1f2937;">24</p>
            <p class="text-xs mt-2" style="color: #10b981;">↑ 12% dari bulan lalu</p>
          </div>
          <div class="rounded-full flex items-center justify-center" style="width: 48px; height: 48px; background-color: #dbeafe;">
            <svg style="width: 24px; height: 24px; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Stat Card 2: Units -->
      <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" style="color: #6b7280;">Unit Kerja</p>
            <p class="text-3xl font-bold mt-2" style="color: #1f2937;">12</p>
            <p class="text-xs mt-2" style="color: #6b7280;">Total unit aktif</p>
          </div>
          <div class="rounded-full flex items-center justify-center" style="width: 48px; height: 48px; background-color: #dcfce7;">
            <svg style="width: 24px; height: 24px; color: #16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Stat Card 3: Programs -->
      <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" style="color: #6b7280;">Program Studi</p>
            <p class="text-3xl font-bold mt-2" style="color: #1f2937;">18</p>
            <p class="text-xs mt-2" style="color: #6b7280;">Terakreditasi</p>
          </div>
          <div class="rounded-full flex items-center justify-center" style="width: 48px; height: 48px; background-color: #fef3c7;">
            <svg style="width: 24px; height: 24px; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Stat Card 4: Users -->
      <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium" style="color: #6b7280;">Pengguna</p>
            <p class="text-3xl font-bold mt-2" style="color: #1f2937;">156</p>
            <p class="text-xs mt-2" style="color: #10b981;">↑ 8 pengguna baru</p>
          </div>
          <div class="rounded-full flex items-center justify-center" style="width: 48px; height: 48px; background-color: #fce7f3;">
            <svg style="width: 24px; height: 24px; color: #ec4899;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Activities -->
      <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-bold" style="color: #1f2937;">Aktivitas Terbaru</h2>
          <span class="px-3 py-1 rounded-full text-xs font-medium" style="background-color: #dbeafe; color: #1e40af;">Hari Ini</span>
        </div>
        <div class="space-y-4">
          <!-- Activity Item 1 -->
          <div class="flex items-start" style="gap: 1rem; padding: 1rem; background-color: #f9fafb; border-radius: 0.5rem;">
            <div class="rounded-full flex items-center justify-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #dbeafe;">
              <svg style="width: 20px; height: 20px; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium" style="color: #1f2937;">Dokumen Baru Ditambahkan</p>
              <p class="text-sm" style="color: #6b7280;">Standar Operasional Prosedur Audit Internal</p>
              <p class="text-xs mt-1" style="color: #9ca3af;">2 jam yang lalu</p>
            </div>
          </div>

          <!-- Activity Item 2 -->
          <div class="flex items-start" style="gap: 1rem; padding: 1rem; background-color: #f9fafb; border-radius: 0.5rem;">
            <div class="rounded-full flex items-center justify-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #dcfce7;">
              <svg style="width: 20px; height: 20px; color: #16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium" style="color: #1f2937;">Audit Selesai</p>
              <p class="text-sm" style="color: #6b7280;">Audit mutu internal Fakultas Teknik</p>
              <p class="text-xs mt-1" style="color: #9ca3af;">5 jam yang lalu</p>
            </div>
          </div>

          <!-- Activity Item 3 -->
          <div class="flex items-start" style="gap: 1rem; padding: 1rem; background-color: #f9fafb; border-radius: 0.5rem;">
            <div class="rounded-full flex items-center justify-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #fce7f3;">
              <svg style="width: 20px; height: 20px; color: #ec4899;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium" style="color: #1f2937;">Pengguna Baru Terdaftar</p>
              <p class="text-sm" style="color: #6b7280;">Dr. Ahmad Hidayat telah bergabung</p>
              <p class="text-xs mt-1" style="color: #9ca3af;">Kemarin</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
          <h2 class="text-lg font-bold mb-4" style="color: #1f2937;">Quick Actions</h2>
          <div class="space-y-3">
            <button class="w-full flex items-center px-4 py-3 rounded-lg text-white font-medium transition-all hover:shadow-md" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <svg style="width: 20px; height: 20px; margin-right: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Tambah Dokumen
            </button>
            <button class="w-full flex items-center px-4 py-3 rounded-lg border border-gray-200 font-medium transition-all hover:border-gray-300 hover:bg-gray-50" style="color: #374151;">
              <svg style="width: 20px; height: 20px; margin-right: 0.75rem; color: #667eea;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Buat Audit
            </button>
            <button class="w-full flex items-center px-4 py-3 rounded-lg border border-gray-200 font-medium transition-all hover:border-gray-300 hover:bg-gray-50" style="color: #374151;">
              <svg style="width: 20px; height: 20px; margin-right: 0.75rem; color: #667eea;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Lihat Laporan
            </button>
          </div>
        </div>

        <!-- Schedule -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
          <h2 class="text-lg font-bold mb-4" style="color: #1f2937;">Jadwal Mendatang</h2>
          <div class="space-y-4">
            <div class="flex items-start" style="gap: 0.75rem;">
              <div class="text-center flex-shrink-0" style="width: 48px;">
                <div class="text-2xl font-bold" style="color: #667eea;">15</div>
                <div class="text-xs" style="color: #6b7280;">Nov</div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium" style="color: #1f2937;">Rapat Koordinasi</p>
                <p class="text-xs" style="color: #6b7280;">10:00 - 12:00 WIB</p>
              </div>
            </div>
            <div class="flex items-start" style="gap: 0.75rem;">
              <div class="text-center flex-shrink-0" style="width: 48px;">
                <div class="text-2xl font-bold" style="color: #667eea;">18</div>
                <div class="text-xs" style="color: #6b7280;">Nov</div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium" style="color: #1f2937;">Audit Internal</p>
                <p class="text-xs" style="color: #6b7280;">09:00 - 15:00 WIB</p>
              </div>
            </div>
            <div class="flex items-start" style="gap: 0.75rem;">
              <div class="text-center flex-shrink-0" style="width: 48px;">
                <div class="text-2xl font-bold" style="color: #667eea;">20</div>
                <div class="text-xs" style="color: #6b7280;">Nov</div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium" style="color: #1f2937;">Review Dokumen</p>
                <p class="text-xs" style="color: #6b7280;">13:00 - 14:30 WIB</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue';
</script>
