<template>
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Mobile Overlay -->
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarMobileOpen"
        @click="closeMobileSidebar"
        class="fixed inset-0 z-9998 bg-black/50 lg:hidden"
      ></div>
    </transition>

    <!-- Main Content Area -->
    <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden" :class="[sidebarExpanded ? 'lg:ml-[290px]' : 'lg:ml-[90px]']">
      <!-- Header -->
      <Header />

      <!-- Main Content -->
      <main class="flex-1 bg-gray-50 dark:bg-gray-900">
        <div class="mx-auto max-w-screen-2xl p-4 md:p-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/components/layout/Sidebar.vue';
import Header from '@/components/layout/Header.vue';
import { useSidebar } from '@/composables/useSidebar';

const { sidebarExpanded, sidebarMobileOpen, closeMobileSidebar } = useSidebar();
</script>
