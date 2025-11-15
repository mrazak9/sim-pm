<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        Diskusi & Komentar
      </h3>
      <div v-if="stats" class="flex gap-3 text-sm">
        <span class="text-gray-600 dark:text-gray-400">
          <strong>{{ stats.threads }}</strong> Thread
        </span>
        <span class="text-green-600 dark:text-green-400">
          <strong>{{ stats.resolved }}</strong> Selesai
        </span>
        <span class="text-orange-600 dark:text-orange-400">
          <strong>{{ stats.unresolved }}</strong> Pending
        </span>
      </div>
    </div>

    <!-- New Comment Form -->
    <div class="mb-6">
      <textarea
        v-model="newComment"
        rows="3"
        placeholder="Tulis komentar atau diskusi... (gunakan @nama untuk mention)"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm text-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500"
      ></textarea>
      <div class="mt-2 flex justify-end">
        <button
          @click="submitComment"
          :disabled="!newComment.trim() || submitting"
          class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ submitting ? 'Mengirim...' : 'Kirim Komentar' }}
        </button>
      </div>
    </div>

    <!-- Comments List -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="comments.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      Belum ada komentar. Jadilah yang pertama berkomentar!
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="comment in comments"
        :key="comment.id"
        class="border-l-2 pl-4"
        :class="comment.is_resolved ? 'border-green-500' : 'border-gray-300 dark:border-gray-600'"
      >
        <!-- Main Comment -->
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="font-semibold text-gray-900 dark:text-white text-sm">
                  {{ comment.user.name }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatDate(comment.created_at) }}
                </span>
                <span v-if="comment.is_resolved" class="text-xs px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded">
                  Selesai
                </span>
              </div>
              <p v-if="editingId !== comment.id" class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                {{ comment.comment }}
              </p>
              <div v-else>
                <textarea
                  v-model="editText"
                  rows="2"
                  class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-2 text-sm"
                ></textarea>
                <div class="mt-2 flex gap-2">
                  <button @click="saveEdit(comment.id)" class="text-xs px-3 py-1 bg-blue-600 text-white rounded">Simpan</button>
                  <button @click="cancelEdit" class="text-xs px-3 py-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded">Batal</button>
                </div>
              </div>
            </div>
            <div class="flex gap-2 ml-4">
              <button
                v-if="canModify(comment)"
                @click="startEdit(comment)"
                class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
              >
                Edit
              </button>
              <button
                @click="toggleResolve(comment)"
                class="text-xs text-gray-600 dark:text-gray-400 hover:underline"
              >
                {{ comment.is_resolved ? 'Buka Kembali' : 'Tandai Selesai' }}
              </button>
            </div>
          </div>

          <!-- Reply Button -->
          <button
            @click="toggleReply(comment.id)"
            class="mt-2 text-xs text-gray-600 dark:text-gray-400 hover:text-blue-600"
          >
            {{ replyingTo === comment.id ? 'Batal Reply' : `Reply (${comment.replies?.length || 0})` }}
          </button>

          <!-- Reply Form -->
          <div v-if="replyingTo === comment.id" class="mt-3">
            <textarea
              v-model="replyText"
              rows="2"
              placeholder="Tulis balasan..."
              class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-2 text-sm"
            ></textarea>
            <button
              @click="submitReply(comment.id)"
              :disabled="!replyText.trim()"
              class="mt-2 text-xs px-3 py-1 bg-blue-600 text-white rounded disabled:opacity-50"
            >
              Kirim Balasan
            </button>
          </div>
        </div>

        <!-- Replies -->
        <div v-if="comment.replies?.length > 0" class="mt-3 ml-6 space-y-2">
          <div
            v-for="reply in comment.replies"
            :key="reply.id"
            class="bg-gray-100 dark:bg-gray-700/30 rounded-lg p-3"
          >
            <div class="flex items-center gap-2 mb-1">
              <span class="font-medium text-gray-900 dark:text-white text-xs">
                {{ reply.user.name }}
              </span>
              <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ formatDate(reply.created_at) }}
              </span>
            </div>
            <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ reply.comment }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  pengisianButirId: {
    type: Number,
    required: true
  }
})

const comments = ref([])
const stats = ref(null)
const loading = ref(false)
const submitting = ref(false)
const newComment = ref('')
const replyingTo = ref(null)
const replyText = ref('')
const editingId = ref(null)
const editText = ref('')

const loadComments = async () => {
  loading.value = true
  try {
    const response = await axios.get(`/api/pengisian-butir/${props.pengisianButirId}/comments`)
    if (response.data.success) {
      comments.value = response.data.data
      stats.value = response.data.statistics
    }
  } catch (error) {
    console.error('Failed to load comments:', error)
  } finally {
    loading.value = false
  }
}

const submitComment = async () => {
  submitting.value = true
  try {
    const response = await axios.post(`/api/pengisian-butir/${props.pengisianButirId}/comments`, {
      comment: newComment.value
    })
    if (response.data.success) {
      newComment.value = ''
      await loadComments()
    }
  } catch (error) {
    console.error('Failed to submit comment:', error)
    alert('Gagal mengirim komentar')
  } finally {
    submitting.value = false
  }
}

const submitReply = async (parentId) => {
  try {
    await axios.post(`/api/pengisian-butir/${props.pengisianButirId}/comments`, {
      comment: replyText.value,
      parent_id: parentId
    })
    replyText.value = ''
    replyingTo.value = null
    await loadComments()
  } catch (error) {
    console.error('Failed to submit reply:', error)
    alert('Gagal mengirim balasan')
  }
}

const toggleResolve = async (comment) => {
  try {
    const endpoint = comment.is_resolved ? 'unresolve' : 'resolve'
    await axios.post(`/api/butir-comments/${comment.id}/${endpoint}`)
    await loadComments()
  } catch (error) {
    console.error('Failed to toggle resolve:', error)
    alert('Gagal mengubah status')
  }
}

const toggleReply = (id) => {
  replyingTo.value = replyingTo.value === id ? null : id
  replyText.value = ''
}

const startEdit = (comment) => {
  editingId.value = comment.id
  editText.value = comment.comment
}

const cancelEdit = () => {
  editingId.value = null
  editText.value = ''
}

const saveEdit = async (id) => {
  try {
    await axios.put(`/api/butir-comments/${id}`, {
      comment: editText.value
    })
    editingId.value = null
    await loadComments()
  } catch (error) {
    console.error('Failed to update comment:', error)
    alert('Gagal mengupdate komentar')
  }
}

const canModify = (comment) => {
  // Simple check - in real app, check against current user ID
  return comment.user?.id !== null
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)

  if (diffMins < 1) return 'Baru saja'
  if (diffMins < 60) return `${diffMins} menit lalu`
  if (diffMins < 1440) return `${Math.floor(diffMins / 60)} jam lalu`
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(() => {
  loadComments()
})

defineExpose({ loadComments })
</script>
