<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Activity Dashboard</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Monitoring aktivitas pengguna sistem
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <select
            v-model="timeRange"
            @change="fetchData"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
          >
            <option value="today">Hari Ini</option>
            <option value="week">7 Hari Terakhir</option>
            <option value="month">30 Hari Terakhir</option>
            <option value="all">Semua</option>
          </select>
          <button
            @click="refreshData"
            :disabled="loading"
            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            <svg :class="['mr-2 h-4 w-4', loading && 'animate-spin']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
          <button
            @click="exportDashboard"
            :disabled="exporting"
            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
          >
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ exporting ? 'Exporting...' : 'Export PDF' }}
          </button>
        </div>
      </div>

      <div ref="dashboardContent">
        <!-- Metric Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <MetricWidget
            label="Total Users"
            :value="statistics.total_users || 0"
            icon="users"
            color="blue"
            :trend="{ direction: 'up', value: '+5', label: 'dari bulan lalu' }"
          />

          <MetricWidget
            label="Active Today"
            :value="statistics.active_today || 0"
            icon="check"
            color="green"
            :subtitle="`${statistics.active_percentage || 0}% dari total`"
          />

          <MetricWidget
            label="Total Sessions"
            :value="statistics.total_sessions || 0"
            icon="clock"
            color="purple"
          />

          <MetricWidget
            label="Avg Session Duration"
            :value="statistics.avg_session_duration || '0m'"
            icon="clock"
            color="orange"
          />
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <!-- Login Activity Chart -->
          <ChartWidget
            title="Aktivitas Login (7 Hari Terakhir)"
            type="line"
            :data="loginActivityData"
            :height="300"
          />

          <!-- User Role Distribution -->
          <ChartWidget
            title="Distribusi User Role"
            type="doughnut"
            :data="roleDistributionData"
            :height="300"
          />
        </div>

        <!-- Activity by Module Chart -->
        <ChartWidget
          title="Aktivitas per Module"
          type="bar"
          :data="moduleActivityData"
          :height="350"
        />

        <!-- Recent Activities & Top Users -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <!-- Recent Activities -->
          <ListWidget
            title="Aktivitas Terbaru"
            :items="recentActivities"
            :loading="loading"
            empty-message="Belum ada aktivitas"
            :items-per-page="10"
            show-pagination
          >
            <template #item="{ item }">
              <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-600">
                <div class="flex items-center space-x-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full" :class="getActivityIconClass(item.type)">
                    <component :is="getActivityIcon(item.type)" class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ item.user_name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ item.description }} • {{ formatTimeAgo(item.created_at) }}
                    </p>
                  </div>
                </div>
                <span :class="getActivityBadgeClass(item.type)" class="rounded-full px-2 py-1 text-xs font-medium">
                  {{ item.type }}
                </span>
              </div>
            </template>
          </ListWidget>

          <!-- Top Active Users -->
          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Active Users</h3>
            </div>
            <div class="space-y-3">
              <div
                v-for="(user, index) in topUsers"
                :key="user.id"
                class="flex items-center justify-between"
              >
                <div class="flex items-center space-x-3">
                  <div class="flex h-8 w-8 items-center justify-center rounded-full" :class="getRankClass(index)">
                    <span class="text-xs font-bold">{{ index + 1 }}</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-gray-900 dark:text-white">{{ user.activity_count }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">aktivitas</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Activity Heatmap -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Activity Heatmap (Jam per Hari)</h3>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr>
                  <th class="p-2 text-left text-xs font-medium text-gray-500">Day</th>
                  <th v-for="hour in 24" :key="hour" class="p-1 text-center text-xs font-medium text-gray-500">
                    {{ hour - 1 }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="day">
                  <td class="p-2 text-xs font-medium text-gray-700 dark:text-gray-300">{{ day }}</td>
                  <td v-for="hour in 24" :key="hour" class="p-1">
                    <div
                      class="h-6 w-6 rounded"
                      :class="getHeatmapColor(getHeatmapValue(day, hour))"
                      :title="`${day} ${hour-1}:00 - ${getHeatmapValue(day, hour)} activities`"
                    ></div>
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="mt-4 flex items-center justify-end space-x-2 text-xs text-gray-500">
              <span>Less</span>
              <div class="h-4 w-4 rounded bg-gray-100 dark:bg-gray-700"></div>
              <div class="h-4 w-4 rounded bg-blue-200 dark:bg-blue-900"></div>
              <div class="h-4 w-4 rounded bg-blue-400 dark:bg-blue-700"></div>
              <div class="h-4 w-4 rounded bg-blue-600 dark:bg-blue-500"></div>
              <div class="h-4 w-4 rounded bg-blue-800 dark:bg-blue-400"></div>
              <span>More</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import MetricWidget from '@/components/dashboard/MetricWidget.vue';
import ChartWidget from '@/components/dashboard/ChartWidget.vue';
import ListWidget from '@/components/dashboard/ListWidget.vue';
import { useDashboardExport } from '@/composables/useDashboardExport';
import axios from 'axios';

const { exporting, exportToPDF } = useDashboardExport();

const loading = ref(false);
const timeRange = ref('week');
const dashboardContent = ref(null);

const statistics = ref({});
const recentActivities = ref([]);
const topUsers = ref([]);

const loginActivityData = ref({
  labels: [],
  datasets: [{
    label: 'Logins',
    data: [],
    borderColor: '#3B82F6',
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    fill: true,
    tension: 0.4
  }]
});

const roleDistributionData = ref({
  labels: ['Admin', 'Manager', 'Staff', 'User'],
  datasets: [{
    data: [5, 12, 25, 45],
    backgroundColor: ['#EF4444', '#F59E0B', '#10B981', '#3B82F6']
  }]
});

const moduleActivityData = ref({
  labels: ['IKU', 'Akreditasi', 'Dokumen', 'Audit', 'Master Data'],
  datasets: [{
    label: 'Aktivitas',
    data: [125, 98, 76, 45, 89],
    backgroundColor: '#3B82F6'
  }]
});

const fetchData = async () => {
  loading.value = true;
  try {
    // Fetch statistics
    const statsResponse = await axios.get('/api/dashboard/user-activity/statistics', {
      params: { range: timeRange.value }
    });
    if (statsResponse.data.success) {
      statistics.value = statsResponse.data.data;
    }

    // Mock data for now
    statistics.value = {
      total_users: 87,
      active_today: 45,
      active_percentage: 52,
      total_sessions: 234,
      avg_session_duration: '15m'
    };

    // Generate login activity data (last 7 days)
    const labels = [];
    const data = [];
    for (let i = 6; i >= 0; i--) {
      const date = new Date();
      date.setDate(date.getDate() - i);
      labels.push(date.toLocaleDateString('id-ID', { weekday: 'short', month: 'short', day: 'numeric' }));
      data.push(Math.floor(Math.random() * 30) + 20);
    }
    loginActivityData.value.labels = labels;
    loginActivityData.value.datasets[0].data = data;

    // Fetch recent activities
    recentActivities.value = generateMockActivities();

    // Fetch top users
    topUsers.value = [
      { id: 1, name: 'John Doe', email: 'john@example.com', activity_count: 156 },
      { id: 2, name: 'Jane Smith', email: 'jane@example.com', activity_count: 142 },
      { id: 3, name: 'Bob Johnson', email: 'bob@example.com', activity_count: 128 },
      { id: 4, name: 'Alice Brown', email: 'alice@example.com', activity_count: 115 },
      { id: 5, name: 'Charlie Wilson', email: 'charlie@example.com', activity_count: 98 }
    ];
  } catch (error) {
    console.error('Failed to fetch activity data:', error);
  } finally {
    loading.value = false;
  }
};

const generateMockActivities = () => {
  const types = ['login', 'create', 'update', 'delete', 'view'];
  const users = ['John Doe', 'Jane Smith', 'Bob Johnson', 'Alice Brown', 'Charlie Wilson'];
  const actions = [
    'logged in to system',
    'created new document',
    'updated IKU target',
    'deleted old file',
    'viewed akreditasi report',
    'uploaded document',
    'approved submission',
    'commented on butir'
  ];

  const activities = [];
  for (let i = 0; i < 20; i++) {
    const date = new Date();
    date.setMinutes(date.getMinutes() - Math.random() * 1440); // Random time in last 24 hours

    activities.push({
      id: i + 1,
      user_name: users[Math.floor(Math.random() * users.length)],
      type: types[Math.floor(Math.random() * types.length)],
      description: actions[Math.floor(Math.random() * actions.length)],
      created_at: date.toISOString()
    });
  }

  return activities;
};

const refreshData = () => {
  fetchData();
};

const exportDashboard = async () => {
  if (!dashboardContent.value) return;

  const result = await exportToPDF(dashboardContent.value, {
    filename: `user-activity-dashboard-${new Date().toISOString().split('T')[0]}.pdf`,
    title: 'User Activity Dashboard',
    orientation: 'portrait'
  });

  if (result.success) {
    alert('Dashboard berhasil di-export!');
  } else {
    alert('Gagal export dashboard: ' + result.error);
  }
};

const getActivityIcon = (type) => {
  const icons = {
    login: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1' })
    ]),
    create: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4v16m8-8H4' })
    ]),
    update: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' })
    ]),
    delete: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' })
    ]),
    view: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' }),
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' })
    ])
  };
  return icons[type] || icons.view;
};

const getActivityIconClass = (type) => {
  const classes = {
    login: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400',
    create: 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400',
    update: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400',
    delete: 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400',
    view: 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400'
  };
  return classes[type] || classes.view;
};

const getActivityBadgeClass = (type) => {
  const classes = {
    login: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    create: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    update: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    delete: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    view: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
  };
  return classes[type] || classes.view;
};

const getRankClass = (index) => {
  if (index === 0) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
  if (index === 1) return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
  if (index === 2) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
  return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
};

const getHeatmapValue = (day, hour) => {
  // Mock heatmap data
  return Math.floor(Math.random() * 20);
};

const getHeatmapColor = (value) => {
  if (value === 0) return 'bg-gray-100 dark:bg-gray-700';
  if (value < 5) return 'bg-blue-200 dark:bg-blue-900';
  if (value < 10) return 'bg-blue-400 dark:bg-blue-700';
  if (value < 15) return 'bg-blue-600 dark:bg-blue-500';
  return 'bg-blue-800 dark:bg-blue-400';
};

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);

  if (diffInSeconds < 60) return 'Baru saja';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} menit lalu`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} jam lalu`;
  return `${Math.floor(diffInSeconds / 86400)} hari lalu`;
};

onMounted(() => {
  fetchData();
});
</script>
