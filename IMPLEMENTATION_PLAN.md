# 📋 Rencana Implementasi Template TailAdmin HTML5 ke Laravel + Vue 3

**Project:** SIM Penjaminan Mutu
**Template Source:** tailadmin-free-tailwind-dashboard-template-main
**Target Framework:** Laravel 10 + Vue 3 + Vite
**Tanggal Dibuat:** 2025-11-13

---

## 🎯 Tujuan

Mengintegrasikan template TailAdmin HTML5 yang profesional ke dalam project Laravel + Vue 3 yang sudah ada, menggantikan UI sederhana yang ada sekarang dengan desain yang lebih modern dan lengkap.

---

## 📦 Analisis Template

### Template yang Digunakan
- **Nama:** TailAdmin Free v2.0.1
- **Format:** HTML5 + Alpine.js
- **CSS Framework:** Tailwind CSS v4
- **Dependencies:**
  - Alpine.js v3.14.1 (untuk interactivity)
  - ApexCharts (untuk charts)
  - Flatpickr (untuk date picker)
  - FullCalendar (untuk calendar)

### Struktur Template
```
tailadmin-free-tailwind-dashboard-template-main/
├── src/
│   ├── index.html              # Dashboard utama
│   ├── signin.html             # Login page
│   ├── signup.html             # Register page
│   ├── profile.html            # Profile page
│   ├── form-elements.html      # Form components
│   ├── basic-tables.html       # Table components
│   ├── partials/
│   │   ├── sidebar.html        # Sidebar component
│   │   ├── header.html         # Header component
│   │   ├── breadcrumb.html     # Breadcrumb
│   │   ├── chart/              # Chart components
│   │   ├── table/              # Table components
│   │   └── metric-group/       # Metric cards
│   ├── images/                 # Images & icons
│   ├── css/                    # Compiled CSS
│   └── js/                     # JavaScript files
├── package.json
└── webpack.config.js
```

---

## 🔄 Strategi Konversi: Alpine.js → Vue 3

### Mapping Syntax

| Alpine.js | Vue 3 Equivalent | Keterangan |
|-----------|------------------|------------|
| `x-data="{ foo: 'bar' }"` | `const foo = ref('bar')` | Reactive state |
| `x-init="..."` | `onMounted(() => {...})` | Lifecycle hook |
| `x-show="isOpen"` | `v-show="isOpen"` | Conditional display |
| `x-if="condition"` | `v-if="condition"` | Conditional rendering |
| `@click="toggle()"` | `@click="toggle()"` | Event binding (sama) |
| `:class="{ 'active': isActive }"` | `:class="{ 'active': isActive }"` | Class binding (sama) |
| `x-model="value"` | `v-model="value"` | Two-way binding |
| `$watch('var', fn)` | `watch(() => var, fn)` | Watcher |
| `$persist('key')` | `localStorage` + `watch` | Persistence |

### Contoh Konversi

**Alpine.js (Template):**
```html
<body x-data="{ sidebarOpen: false, darkMode: false }"
      :class="{ 'dark': darkMode }">
  <button @click="sidebarOpen = !sidebarOpen">Toggle</button>
  <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <!-- sidebar content -->
  </aside>
</body>
```

**Vue 3 (Target):**
```vue
<template>
  <div :class="{ 'dark': darkMode }">
    <button @click="sidebarOpen = !sidebarOpen">Toggle</button>
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
      <!-- sidebar content -->
    </aside>
  </div>
</template>

<script setup>
import { ref } from 'vue';
const sidebarOpen = ref(false);
const darkMode = ref(false);
</script>
```

---

## 📝 Tahapan Implementasi

### **FASE 1: Persiapan & Setup** ⏱️ ~30 menit

#### 1.1. Backup Project Existing
```bash
# Backup folder resources/js
cp -r resources/js resources/js.backup

# Atau commit dulu ke git
git add .
git commit -m "backup: Before implementing TailAdmin template"
```

#### 1.2. Copy Assets dari Template
```bash
# Copy images
cp -r tailadmin-free-tailwind-dashboard-template-main/src/images/* public/images/

# Copy icons jika ada
mkdir -p public/icons
```

#### 1.3. Update Tailwind Configuration
File: `tailwind.config.js`

```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class', // Enable dark mode dengan class
  theme: {
    extend: {
      colors: {
        // Tambahkan color palette dari template
        'gray': {
          50: '#f9fafb',
          100: '#f3f4f6',
          200: '#e5e7eb',
          300: '#d1d5db',
          400: '#9ca3af',
          500: '#6b7280',
          600: '#4b5563',
          700: '#374151',
          800: '#1f2937',
          900: '#111827',
        },
      },
      fontSize: {
        'title-2xl': '72px',
        'title-xl': '60px',
        'title-lg': '48px',
        'title-md': '36px',
        'title-sm': '30px',
      },
      zIndex: {
        '9999': '9999',
        '99999': '99999',
      },
      screens: {
        '2xsm': '375px',
        'xsm': '425px',
        '3xl': '2000px',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
```

#### 1.4. Update CSS
File: `resources/css/app.css`

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom scrollbar styles dari template */
@layer utilities {
  .no-scrollbar::-webkit-scrollbar {
    display: none;
  }
  .no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
}

/* Dark mode variables */
:root {
  --color-gray-900: #111827;
  --color-gray-800: #1f2937;
}
```

---

### **FASE 2: Konversi Login Page** ⏱️ ~45 menit

#### 2.1. Analisis File Sumber
File: `tailadmin-free-tailwind-dashboard-template-main/src/signin.html`

**Struktur HTML yang akan diconvert:**
- Layout split (form di kiri, image/pattern di kanan)
- Form fields (email, password)
- Social login buttons (Google, GitHub)
- Dark mode toggle
- Responsive design

#### 2.2. Buat Login.vue Baru
File: `resources/js/views/auth/Login.vue`

**Template Structure:**
```vue
<template>
  <div class="relative bg-white dark:bg-gray-900">
    <!-- Left Side: Form -->
    <div class="flex flex-col lg:flex-row min-h-screen">
      <!-- Form Section -->
      <div class="flex-1 flex flex-col">
        <!-- Back button -->
        <div class="max-w-md mx-auto w-full pt-10 px-6">
          <router-link to="/" class="inline-flex items-center text-sm">
            <!-- Back icon & text -->
          </router-link>
        </div>

        <!-- Main Form -->
        <div class="flex-1 flex items-center justify-center px-6">
          <div class="w-full max-w-md">
            <!-- Title -->
            <div class="mb-8">
              <h1 class="text-title-md font-semibold text-gray-800 dark:text-white mb-2">
                Sign In
              </h1>
              <p class="text-sm text-gray-500">
                Enter your email and password to sign in!
              </p>
            </div>

            <!-- Social Login Buttons (Optional) -->
            <div class="grid grid-cols-2 gap-3 mb-6">
              <!-- Google & GitHub buttons -->
            </div>

            <!-- Divider -->
            <div class="relative mb-6">
              <hr class="border-gray-200 dark:border-gray-800" />
              <span class="absolute left-1/2 -translate-x-1/2 -top-3 bg-white dark:bg-gray-900 px-4 text-sm text-gray-500">
                or
              </span>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="handleLogin" class="space-y-6">
              <!-- Email Input -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Email
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                  placeholder="Enter your email"
                />
              </div>

              <!-- Password Input -->
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Password
                </label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  required
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white"
                  placeholder="Enter your password"
                />
              </div>

              <!-- Error Message -->
              <div v-if="error" class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                <p class="text-sm text-red-800 dark:text-red-200">{{ error }}</p>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="loading"
                class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="loading">Signing in...</span>
                <span v-else>Sign In</span>
              </button>
            </form>

            <!-- Demo Credentials -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
              <p class="text-xs text-gray-500 text-center mb-2">Demo Credentials:</p>
              <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                <p class="text-center"><strong>Super Admin:</strong> admin@sim-pm.test / password</p>
                <p class="text-center"><strong>Admin:</strong> demo@sim-pm.test / password</p>
                <p class="text-center"><strong>User:</strong> user@sim-pm.test / password</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side: Pattern/Image -->
      <div class="hidden lg:flex lg:flex-1 bg-blue-600 dark:bg-gray-800 items-center justify-center p-12">
        <!-- Decorative pattern or image -->
        <div class="text-center text-white">
          <h2 class="text-4xl font-bold mb-4">SIM Penjaminan Mutu</h2>
          <p class="text-lg opacity-90">Sistem Informasi Manajemen Penjaminan Mutu</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';

  try {
    const result = await authStore.login(form);

    if (result.success) {
      router.push('/');
    } else {
      error.value = result.message || 'Login gagal. Periksa email dan password Anda.';
    }
  } catch (err) {
    error.value = 'Terjadi kesalahan saat login';
    console.error('Login error:', err);
  } finally {
    loading.value = false;
  }
};
</script>
```

#### 2.3. Testing Login Page
```bash
npm run dev

# Test di browser:
# - Akses http://localhost:5173/login
# - Test login functionality
# - Test dark mode toggle
# - Test responsive design
```

---

### **FASE 3: Konversi Main Layout (Sidebar + Header)** ⏱️ ~1.5 jam

#### 3.1. Analisis Files Sumber
- `tailadmin-free-tailwind-dashboard-template-main/src/partials/sidebar.html`
- `tailadmin-free-tailwind-dashboard-template-main/src/partials/header.html`

**Features yang akan diimplementasi:**
- Collapsible sidebar (expand/collapse)
- Dark mode toggle
- User dropdown menu
- Notifications dropdown
- Mobile responsive (hamburger menu)
- Sticky header

#### 3.2. Buat Composable untuk Sidebar
File: `resources/js/composables/useSidebar.js`

```javascript
import { ref, computed } from 'vue';

const sidebarExpanded = ref(true);
const sidebarMobileOpen = ref(false);

export function useSidebar() {
  const toggleSidebar = () => {
    sidebarExpanded.value = !sidebarExpanded.value;
  };

  const toggleMobileSidebar = () => {
    sidebarMobileOpen.value = !sidebarMobileOpen.value;
  };

  const closeMobileSidebar = () => {
    sidebarMobileOpen.value = false;
  };

  return {
    sidebarExpanded,
    sidebarMobileOpen,
    toggleSidebar,
    toggleMobileSidebar,
    closeMobileSidebar,
  };
}
```

#### 3.3. Buat Composable untuk Dark Mode
File: `resources/js/composables/useDarkMode.js`

```javascript
import { ref, watch, onMounted } from 'vue';

export function useDarkMode() {
  const darkMode = ref(false);

  // Load from localStorage on mount
  onMounted(() => {
    const stored = localStorage.getItem('darkMode');
    if (stored !== null) {
      darkMode.value = JSON.parse(stored);
    } else {
      // Check system preference
      darkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    applyDarkMode();
  });

  // Watch for changes and save to localStorage
  watch(darkMode, (newValue) => {
    localStorage.setItem('darkMode', JSON.stringify(newValue));
    applyDarkMode();
  });

  const applyDarkMode = () => {
    if (darkMode.value) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  };

  const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
  };

  return {
    darkMode,
    toggleDarkMode,
  };
}
```

#### 3.4. Buat Sidebar Component
File: `resources/js/components/layout/Sidebar.vue`

```vue
<template>
  <aside
    :class="[
      'fixed left-0 top-0 z-9999 flex h-screen flex-col overflow-y-hidden border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-all duration-300',
      sidebarExpanded ? 'w-[290px]' : 'lg:w-[90px]',
      sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]"
  >
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between gap-2 px-5 py-8">
      <router-link to="/" class="flex items-center">
        <!-- Logo -->
        <img
          v-if="sidebarExpanded || sidebarMobileOpen"
          src="/images/logo/logo.svg"
          alt="Logo"
          class="dark:hidden"
        />
        <img
          v-if="sidebarExpanded || sidebarMobileOpen"
          src="/images/logo/logo-dark.svg"
          alt="Logo"
          class="hidden dark:block"
        />
        <img
          v-else
          src="/images/logo/logo-icon.svg"
          alt="Logo"
          class="w-8 h-8"
        />
      </router-link>

      <!-- Toggle button (desktop only) -->
      <button
        @click="toggleSidebar"
        class="hidden lg:block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
      </button>
    </div>

    <!-- Sidebar Menu -->
    <nav class="flex-1 overflow-y-auto no-scrollbar px-5">
      <!-- Menu Group: Main -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">MENU</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <!-- Dashboard -->
          <li>
            <router-link
              to="/"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path === '/'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Dashboard</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Menu Group: Master Data -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">MASTER DATA</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <!-- Unit Kerja -->
          <li>
            <router-link
              to="/unit-kerja"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path === '/unit-kerja'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Unit Kerja</span>
            </router-link>
          </li>

          <!-- Program Studi -->
          <li>
            <router-link
              to="/program-studi"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path === '/program-studi'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Program Studi</span>
            </router-link>
          </li>

          <!-- User Management -->
          <li>
            <router-link
              to="/users"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path === '/users'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">User Management</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Menu Group: Dokumen -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">DOKUMEN</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <li>
            <router-link
              to="/dokumen"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path === '/dokumen'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Dokumen Mutu</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Menu Group: Audit -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">AUDIT</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <li>
            <router-link
              to="/audit"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path === '/audit'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Audit Internal</span>
            </router-link>
          </li>
        </ul>
      </div>
    </nav>

    <!-- User Profile (at bottom) -->
    <div class="border-t border-gray-200 dark:border-gray-800 px-5 py-4">
      <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-800">
        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
          {{ userInitials }}
        </div>
        <div v-if="sidebarExpanded || sidebarMobileOpen" class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ userName }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ userRole }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useSidebar } from '@/composables/useSidebar';
import { useAuthStore } from '@/stores/auth';

const { sidebarExpanded, sidebarMobileOpen, toggleSidebar, closeMobileSidebar } = useSidebar();
const authStore = useAuthStore();

const userName = computed(() => authStore.user?.name || 'User');
const userRole = computed(() => authStore.user?.roles?.[0]?.name || 'User');
const userInitials = computed(() => {
  const name = authStore.user?.name || 'U';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});
</script>
```

#### 3.5. Buat Header Component
File: `resources/js/components/layout/Header.vue`

```vue
<template>
  <header class="sticky top-0 z-999 flex w-full bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
    <div class="flex flex-grow items-center justify-between px-4 py-4 shadow-sm md:px-6">
      <!-- Left: Mobile menu button + Search -->
      <div class="flex items-center gap-4">
        <!-- Mobile menu button -->
        <button
          @click="toggleMobileSidebar"
          class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <!-- Page Title -->
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white">
          {{ pageTitle }}
        </h1>
      </div>

      <!-- Right: Dark mode toggle + Notifications + User menu -->
      <div class="flex items-center gap-3">
        <!-- Dark Mode Toggle -->
        <button
          @click="toggleDarkMode"
          class="relative flex h-10 w-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"
        >
          <!-- Sun icon (show in dark mode) -->
          <svg v-if="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <!-- Moon icon (show in light mode) -->
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
          </svg>
        </button>

        <!-- Notifications Dropdown -->
        <div class="relative" v-click-outside="closeNotifications">
          <button
            @click="notificationsOpen = !notificationsOpen"
            class="relative flex h-10 w-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <!-- Badge -->
            <span class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-red-500">
              <span class="absolute -z-1 inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
            </span>
          </button>

          <!-- Dropdown -->
          <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div
              v-if="notificationsOpen"
              class="absolute right-0 mt-2 w-80 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-lg"
            >
              <!-- Header -->
              <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-4 py-3">
                <h5 class="text-sm font-semibold text-gray-800 dark:text-white">Notifications</h5>
                <span class="rounded bg-blue-100 dark:bg-blue-900 px-2 py-0.5 text-xs font-medium text-blue-600 dark:text-blue-400">
                  3 New
                </span>
              </div>

              <!-- Notification items -->
              <div class="max-h-96 overflow-y-auto">
                <a
                  href="#"
                  class="flex gap-3 border-b border-gray-200 dark:border-gray-800 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                  <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800 dark:text-white">Edit your information</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Lorem ipsum dolor sit amet</p>
                    <p class="text-xs text-gray-400 mt-1">12 May, 2025</p>
                  </div>
                </a>
              </div>
            </div>
          </transition>
        </div>

        <!-- User Dropdown -->
        <div class="relative" v-click-outside="closeUserMenu">
          <button
            @click="userMenuOpen = !userMenuOpen"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
          >
            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold text-sm">
              {{ userInitials }}
            </div>
            <div class="hidden md:block text-left">
              <p class="text-sm font-medium text-gray-800 dark:text-white">{{ userName }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ userRole }}</p>
            </div>
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown -->
          <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div
              v-if="userMenuOpen"
              class="absolute right-0 mt-2 w-56 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-lg"
            >
              <div class="p-3 border-b border-gray-200 dark:border-gray-800">
                <p class="text-sm font-medium text-gray-800 dark:text-white">{{ userName }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ userEmail }}</p>
              </div>

              <ul class="p-2">
                <li>
                  <router-link
                    to="/profile"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    My Profile
                  </router-link>
                </li>
                <li>
                  <router-link
                    to="/settings"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                  </router-link>
                </li>
              </ul>

              <div class="p-2 border-t border-gray-200 dark:border-gray-800">
                <button
                  @click="handleLogout"
                  class="flex w-full items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Log Out
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useSidebar } from '@/composables/useSidebar';
import { useDarkMode } from '@/composables/useDarkMode';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { toggleMobileSidebar } = useSidebar();
const { darkMode, toggleDarkMode } = useDarkMode();

const notificationsOpen = ref(false);
const userMenuOpen = ref(false);

const userName = computed(() => authStore.user?.name || 'User');
const userEmail = computed(() => authStore.user?.email || '');
const userRole = computed(() => authStore.user?.roles?.[0]?.name || 'User');
const userInitials = computed(() => {
  const name = authStore.user?.name || 'U';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const pageTitle = computed(() => {
  const titles = {
    '/': 'Dashboard',
    '/unit-kerja': 'Unit Kerja',
    '/program-studi': 'Program Studi',
    '/users': 'User Management',
    '/dokumen': 'Dokumen Mutu',
    '/audit': 'Audit Internal',
    '/profile': 'Profile',
    '/settings': 'Settings',
  };
  return titles[route.path] || 'Dashboard';
});

const closeNotifications = () => {
  notificationsOpen.value = false;
};

const closeUserMenu = () => {
  userMenuOpen.value = false;
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
```

#### 3.6. Buat Click Outside Directive
File: `resources/js/directives/clickOutside.js`

```javascript
export const clickOutside = {
  beforeMount(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event);
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  },
};
```

Register directive di `resources/js/app.js`:
```javascript
import { clickOutside } from './directives/clickOutside';

app.directive('click-outside', clickOutside);
```

#### 3.7. Update MainLayout
File: `resources/js/layouts/MainLayout.vue`

```vue
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
    <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
      <!-- Header -->
      <Header />

      <!-- Main Content -->
      <main class="flex-1">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
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

const { sidebarMobileOpen, closeMobileSidebar } = useSidebar();
</script>
```

---

### **FASE 4: Konversi Dashboard/Home** ⏱️ ~1 jam

#### 4.1. Analisis File Sumber
- `tailadmin-free-tailwind-dashboard-template-main/src/index.html`
- `tailadmin-free-tailwind-dashboard-template-main/src/partials/metric-group/metric-group-01.html`
- `tailadmin-free-tailwind-dashboard-template-main/src/partials/chart/chart-01.html`
- `tailadmin-free-tailwind-dashboard-template-main/src/partials/table/table-01.html`

#### 4.2. Buat Metric Card Component
File: `resources/js/components/dashboard/MetricCard.vue`

```vue
<template>
  <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-1">
          <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
            {{ title }}
          </h4>
        </div>
        <div class="flex items-baseline gap-2">
          <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ value }}
          </h3>
          <span
            v-if="change"
            :class="[
              'text-xs font-medium px-2 py-0.5 rounded',
              changeType === 'increase'
                ? 'bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400'
                : 'bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400'
            ]"
          >
            {{ changeType === 'increase' ? '↑' : '↓' }} {{ change }}%
          </span>
        </div>
        <p v-if="subtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
          {{ subtitle }}
        </p>
      </div>

      <!-- Icon -->
      <div
        :class="[
          'flex h-12 w-12 items-center justify-center rounded-full flex-shrink-0',
          iconBgColor
        ]"
      >
        <component :is="iconComponent" class="w-6 h-6" :class="iconColor" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  change: {
    type: [String, Number],
    default: null,
  },
  changeType: {
    type: String,
    default: 'increase', // 'increase' or 'decrease'
  },
  subtitle: {
    type: String,
    default: '',
  },
  iconComponent: {
    type: Object,
    required: true,
  },
  iconColor: {
    type: String,
    default: 'text-blue-600 dark:text-blue-400',
  },
  iconBgColor: {
    type: String,
    default: 'bg-blue-100 dark:bg-blue-900/20',
  },
});
</script>
```

#### 4.3. Buat Home.vue Baru
File: `resources/js/views/Home.vue`

```vue
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
      <!-- Chart Section -->
      <div class="col-span-12 xl:col-span-7">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
          <div class="mb-6 flex items-center justify-between">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
              Statistik Dokumen
            </h4>
            <div class="flex gap-2">
              <button class="rounded bg-gray-100 dark:bg-gray-800 px-3 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                Week
              </button>
              <button class="rounded bg-blue-600 px-3 py-1 text-xs font-medium text-white">
                Month
              </button>
              <button class="rounded bg-gray-100 dark:bg-gray-800 px-3 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                Year
              </button>
            </div>
          </div>
          <!-- Chart placeholder -->
          <div class="h-80 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-gray-500 dark:text-gray-400">Chart will be here</p>
          </div>
        </div>
      </div>

      <!-- Activities Section -->
      <div class="col-span-12 xl:col-span-5">
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

      <!-- Quick Actions -->
      <div class="col-span-12 lg:col-span-6 xl:col-span-4">
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
      </div>

      <!-- Schedule -->
      <div class="col-span-12 lg:col-span-6 xl:col-span-4">
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

      <!-- Recent Documents Table -->
      <div class="col-span-12 xl:col-span-8">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
          <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
            Dokumen Terbaru
          </h4>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                  <th class="pb-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nama Dokumen</th>
                  <th class="pb-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kategori</th>
                  <th class="pb-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                  <th class="pb-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                <tr>
                  <td class="py-4 text-sm font-medium text-gray-900 dark:text-white">SOP Audit Internal</td>
                  <td class="py-4 text-sm text-gray-500 dark:text-gray-400">Prosedur</td>
                  <td class="py-4 text-sm text-gray-500 dark:text-gray-400">13 Nov 2025</td>
                  <td class="py-4">
                    <span class="inline-flex rounded-full bg-green-100 dark:bg-green-900/20 px-3 py-1 text-xs font-medium text-green-600 dark:text-green-400">
                      Aktif
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="py-4 text-sm font-medium text-gray-900 dark:text-white">Manual Mutu</td>
                  <td class="py-4 text-sm text-gray-500 dark:text-gray-400">Pedoman</td>
                  <td class="py-4 text-sm text-gray-500 dark:text-gray-400">10 Nov 2025</td>
                  <td class="py-4">
                    <span class="inline-flex rounded-full bg-yellow-100 dark:bg-yellow-900/20 px-3 py-1 text-xs font-medium text-yellow-600 dark:text-yellow-400">
                      Review
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="py-4 text-sm font-medium text-gray-900 dark:text-white">Kebijakan Akademik</td>
                  <td class="py-4 text-sm text-gray-500 dark:text-gray-400">Kebijakan</td>
                  <td class="py-4 text-sm text-gray-500 dark:text-gray-400">08 Nov 2025</td>
                  <td class="py-4">
                    <span class="inline-flex rounded-full bg-green-100 dark:bg-green-900/20 px-3 py-1 text-xs font-medium text-green-600 dark:text-green-400">
                      Aktif
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
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
  class: 'w-6 h-6',
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
  class: 'w-6 h-6',
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
  class: 'w-6 h-6',
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
  class: 'w-6 h-6',
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
```

---

### **FASE 5: Testing & Fine Tuning** ⏱️ ~30 menit

#### 5.1. Testing Checklist

```bash
# Start dev server
npm run dev
```

**Test List:**
- [ ] Login page responsive di mobile/tablet/desktop
- [ ] Login functionality bekerja dengan benar
- [ ] Dark mode toggle bekerja dan tersimpan di localStorage
- [ ] Sidebar collapse/expand bekerja
- [ ] Mobile menu (hamburger) bekerja
- [ ] Routing semua menu bekerja
- [ ] User dropdown bekerja
- [ ] Notifications dropdown bekerja
- [ ] Logout functionality bekerja
- [ ] Dashboard metrics tampil dengan benar
- [ ] All components responsive
- [ ] Dark mode konsisten di semua pages

#### 5.2. Build Production

```bash
# Build for production
npm run build

# Test production build
php artisan serve
```

---

### **FASE 6: Dokumentasi & Commit** ⏱️ ~15 menit

#### 6.1. Update README

Tambahkan informasi di README tentang template yang digunakan:

```markdown
## UI Template

Project ini menggunakan template **TailAdmin Free v2.0.1** yang telah diadaptasi dari HTML5 + Alpine.js ke Vue 3.

Template features:
- Responsive design
- Dark mode support
- Modern and clean UI
- Tailwind CSS v4
- Collapsible sidebar
```

#### 6.2. Git Commit

```bash
# Add all changes
git add .

# Commit dengan pesan yang jelas
git commit -m "feat: Implement TailAdmin template

- Convert TailAdmin HTML5 template to Vue 3
- Add collapsible sidebar with dark mode
- Implement responsive header with notifications
- Create metric cards component for dashboard
- Add dark mode composable with localStorage persistence
- Implement click-outside directive
- Update Tailwind config with template theme
- All pages are now responsive and support dark mode"

# Push ke repository
git push origin master
```

---

## 📋 Checklist Implementasi

Gunakan checklist ini saat eksekusi:

### Persiapan
- [ ] Backup project existing (`git commit` atau copy folder)
- [ ] Copy assets (images) dari template ke `public/`
- [ ] Update `tailwind.config.js`
- [ ] Update `resources/css/app.css`

### Login Page
- [ ] Buat `Login.vue` baru dengan template dari signin.html
- [ ] Convert Alpine.js syntax ke Vue 3
- [ ] Test login functionality
- [ ] Test responsive design

### Layout Components
- [ ] Buat `useSidebar.js` composable
- [ ] Buat `useDarkMode.js` composable
- [ ] Buat `clickOutside.js` directive
- [ ] Buat `Sidebar.vue` component
- [ ] Buat `Header.vue` component
- [ ] Update `MainLayout.vue`
- [ ] Test sidebar collapse/expand
- [ ] Test mobile menu
- [ ] Test dark mode toggle

### Dashboard
- [ ] Buat `MetricCard.vue` component
- [ ] Update `Home.vue` dengan design baru
- [ ] Test all dashboard features
- [ ] Test responsive layout

### Testing
- [ ] Test semua pages di mobile view
- [ ] Test semua pages di tablet view
- [ ] Test semua pages di desktop view
- [ ] Test dark mode di semua pages
- [ ] Test navigation dan routing
- [ ] Test logout functionality

### Finalisasi
- [ ] Build production (`npm run build`)
- [ ] Test production build
- [ ] Update README
- [ ] Git commit dengan pesan yang jelas
- [ ] Push ke repository

---

## 🔧 Troubleshooting

### Issue: Tailwind classes tidak bekerja
**Solusi:**
```bash
# Clear cache dan rebuild
rm -rf node_modules/.vite
npm run build
```

### Issue: Dark mode tidak tersimpan
**Solusi:**
Pastikan `useDarkMode` composable sudah diimport dan digunakan dengan benar:
```javascript
const { darkMode, toggleDarkMode } = useDarkMode();
```

### Issue: Sidebar tidak collapse di desktop
**Solusi:**
Cek apakah `useSidebar` composable sudah di-share dengan benar. Pastikan menggunakan singleton pattern.

### Issue: Icon tidak muncul
**Solusi:**
Pastikan SVG path sudah benar dan SVG component sudah di-render dengan benar menggunakan `h()` function.

---

## 📦 Dependencies yang Dibutuhkan

Dependencies sudah tersedia di project Laravel + Vue 3:
- ✅ Vue 3
- ✅ Vue Router
- ✅ Pinia (state management)
- ✅ Vite
- ✅ Tailwind CSS v4
- ✅ @tailwindcss/forms

Tidak perlu install tambahan!

---

## ⏱️ Estimasi Waktu Total: 4-5 jam

| Fase | Estimasi |
|------|----------|
| Fase 1: Persiapan & Setup | 30 menit |
| Fase 2: Login Page | 45 menit |
| Fase 3: Layout (Sidebar + Header) | 1.5 jam |
| Fase 4: Dashboard/Home | 1 jam |
| Fase 5: Testing & Fine Tuning | 30 menit |
| Fase 6: Dokumentasi & Commit | 15 menit |
| **TOTAL** | **4.5 jam** |

---

## 🎯 Expected Result

Setelah implementasi selesai, project akan memiliki:

1. ✅ Login page yang modern dan responsive
2. ✅ Sidebar yang collapsible dengan menu terstruktur
3. ✅ Header dengan dark mode toggle, notifications, dan user menu
4. ✅ Dashboard dengan metric cards, charts, activities
5. ✅ Dark mode support di semua pages
6. ✅ Fully responsive (mobile, tablet, desktop)
7. ✅ Clean code structure dengan composables
8. ✅ Reusable components (MetricCard, Sidebar, Header)

---

## 📝 Notes

1. **Jangan hapus file backup** sampai yakin implementasi berhasil
2. **Test setiap fase** sebelum lanjut ke fase berikutnya
3. **Commit setelah selesai** untuk memudahkan rollback jika ada masalah
4. **Screenshot before/after** untuk dokumentasi

---

## 🚀 Ready to Execute!

Rencana ini bisa dieksekusi kapan saja dengan mengikuti tahapan di atas secara berurutan. Semua file path, code snippets, dan instruksi sudah lengkap dan siap digunakan.

**Good luck with the implementation! 🎉**
