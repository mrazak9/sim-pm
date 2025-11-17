<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Survey Info -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ survey.title }}</h2>
            <p v-if="survey.description" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              {{ survey.description }}
            </p>
            <div class="mt-3 flex gap-4">
              <span :class="getStatusBadgeClass(survey.status)" class="inline-flex rounded-full px-3 py-1 text-xs font-medium">
                {{ getStatusLabel(survey.status) }}
              </span>
              <span :class="getTypeBadgeClass(survey.type)" class="inline-flex rounded-full px-3 py-1 text-xs font-medium">
                {{ getTypeLabel(survey.type) }}
              </span>
            </div>
          </div>
          <button
            @click="$router.push('/surveys')"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Questions Section -->
      <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Pertanyaan ({{ questions.length }})
            </h3>
            <button
              @click="openAddQuestionModal"
              class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
            >
              <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Tambah Pertanyaan
            </button>
          </div>
        </div>

        <div class="p-6">
          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center py-12">
            <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>

          <!-- Empty State -->
          <div v-else-if="questions.length === 0" class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <p class="mt-4 text-gray-600 dark:text-gray-400">Belum ada pertanyaan</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">Klik tombol "Tambah Pertanyaan" untuk memulai</p>
          </div>

          <!-- Questions List -->
          <div v-else class="space-y-4">
            <div
              v-for="(question, index) in questions"
              :key="question.id"
              class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-start gap-3">
                    <span class="flex-shrink-0 font-medium text-gray-500 dark:text-gray-400">{{ index + 1 }}.</span>
                    <div class="flex-1">
                      <div class="flex items-start gap-2">
                        <p class="font-medium text-gray-900 dark:text-white">{{ question.question_text }}</p>
                        <span v-if="question.is_required" class="text-red-500">*</span>
                      </div>
                      <div class="mt-2 flex items-center gap-2">
                        <span :class="getQuestionTypeBadgeClass(question.question_type)" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium">
                          {{ getQuestionTypeLabel(question.question_type) }}
                        </span>
                      </div>
                      <p v-if="question.description" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ question.description }}
                      </p>

                      <!-- Display Options for Choice Questions -->
                      <div v-if="question.options && question.options.length > 0" class="mt-3 ml-6 space-y-1">
                        <div v-for="(option, idx) in question.options" :key="idx" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                          <svg v-if="question.question_type === 'radio'" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="2" />
                          </svg>
                          <svg v-else-if="question.question_type === 'checkbox'" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="4" y="4" width="16" height="16" rx="2" stroke-width="2" />
                          </svg>
                          <span>{{ option }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="ml-4 flex flex-col gap-2 sm:flex-row">
                  <button
                    @click="moveUp(index)"
                    :disabled="index === 0"
                    class="text-gray-600 hover:text-gray-700 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-400"
                    title="Pindah ke atas"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </button>
                  <button
                    @click="moveDown(index)"
                    :disabled="index === questions.length - 1"
                    class="text-gray-600 hover:text-gray-700 disabled:cursor-not-allowed disabled:opacity-30 dark:text-gray-400"
                    title="Pindah ke bawah"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                  <button
                    @click="editQuestion(question)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="handleDuplicateQuestion(question.id)"
                    class="text-cyan-600 hover:text-cyan-700 dark:text-cyan-400"
                    title="Duplikat"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </button>
                  <button
                    @click="handleDeleteQuestion(question.id)"
                    class="text-red-600 hover:text-red-700 dark:text-red-400"
                    title="Hapus"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Question Modal -->
    <div v-if="showQuestionModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-4">
      <div class="relative mx-auto w-full max-w-2xl rounded-lg bg-white shadow dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ editingQuestion ? 'Edit Pertanyaan' : 'Tambah Pertanyaan' }}
            </h3>
            <button
              @click="closeModal"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <form @submit.prevent="saveQuestion" class="p-6">
          <div class="space-y-4">
            <!-- Question Type -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Tipe Pertanyaan <span class="text-red-500">*</span>
              </label>
              <select
                v-model="questionForm.question_type"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                required
              >
                <option value="">Pilih Tipe</option>
                <option value="text">Teks Pendek</option>
                <option value="textarea">Teks Panjang</option>
                <option value="radio">Pilihan Tunggal (Radio)</option>
                <option value="checkbox">Pilihan Ganda (Checkbox)</option>
                <option value="dropdown">Dropdown</option>
                <option value="rating">Rating</option>
              </select>
            </div>

            <!-- Question Text -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Pertanyaan <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="questionForm.question_text"
                rows="3"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Masukkan pertanyaan"
                required
              ></textarea>
            </div>

            <!-- Description -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Deskripsi / Teks Bantuan (Opsional)
              </label>
              <input
                type="text"
                v-model="questionForm.description"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Tambahkan deskripsi jika diperlukan"
              />
            </div>

            <!-- Options (for radio, checkbox, dropdown) -->
            <div v-if="['radio', 'checkbox', 'dropdown'].includes(questionForm.question_type)">
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Opsi Jawaban <span class="text-red-500">*</span>
              </label>
              <div class="space-y-2">
                <div v-for="(option, idx) in questionForm.options" :key="idx" class="flex gap-2">
                  <input
                    type="text"
                    v-model="questionForm.options[idx]"
                    class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    :placeholder="`Opsi ${idx + 1}`"
                  />
                  <button
                    type="button"
                    @click="removeOption(idx)"
                    class="text-red-600 hover:text-red-700"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <button
                  type="button"
                  @click="addOption"
                  class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400"
                >
                  + Tambah Opsi
                </button>
              </div>
            </div>

            <!-- Is Required -->
            <div class="flex items-center">
              <input
                type="checkbox"
                id="is_required"
                v-model="questionForm.is_required"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
              />
              <label for="is_required" class="ml-2 text-sm text-gray-900 dark:text-white">
                Pertanyaan Wajib Diisi
              </label>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button
              type="button"
              @click="closeModal"
              class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ editingQuestion ? 'Update' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useSurveyApi } from '@/composables/useSurveyApi';

const router = useRouter();
const route = useRoute();

const { getSurvey, getSurveyQuestions, createQuestion, updateQuestion, deleteQuestion, reorderQuestions, duplicateQuestion, loading } = useSurveyApi();

const survey = ref({});
const questions = ref([]);
const showQuestionModal = ref(false);
const editingQuestion = ref(null);

const questionForm = ref({
  question_type: '',
  question_text: '',
  description: '',
  is_required: false,
  options: [],
});

const fetchSurvey = async () => {
  try {
    const response = await getSurvey(route.params.id);
    if (response.success) {
      survey.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch survey:', error);
    alert('Gagal memuat data kuesioner');
    router.push('/surveys');
  }
};

const fetchQuestions = async () => {
  try {
    const response = await getSurveyQuestions(route.params.id);
    if (response.success) {
      questions.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch questions:', error);
    alert('Gagal memuat pertanyaan');
  }
};

const openAddQuestionModal = () => {
  editingQuestion.value = null;
  questionForm.value = {
    question_type: '',
    question_text: '',
    description: '',
    is_required: false,
    options: [],
  };
  showQuestionModal.value = true;
};

const editQuestion = (question) => {
  editingQuestion.value = question;
  questionForm.value = {
    question_type: question.question_type || '',
    question_text: question.question_text || '',
    description: question.description || '',
    is_required: question.is_required || false,
    options: question.options ? [...question.options] : [],
  };
  showQuestionModal.value = true;
};

const closeModal = () => {
  showQuestionModal.value = false;
  editingQuestion.value = null;
};

const saveQuestion = async () => {
  try {
    let response;
    if (editingQuestion.value) {
      response = await updateQuestion(editingQuestion.value.id, questionForm.value);
    } else {
      response = await createQuestion(route.params.id, questionForm.value);
    }

    if (response.success) {
      alert(editingQuestion.value ? 'Pertanyaan berhasil diupdate' : 'Pertanyaan berhasil ditambahkan');
      closeModal();
      fetchQuestions();
    }
  } catch (error) {
    alert('Gagal menyimpan pertanyaan: ' + (error.response?.data?.message || error.message));
  }
};

const handleDeleteQuestion = async (questionId) => {
  if (confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?')) {
    try {
      const response = await deleteQuestion(questionId);
      if (response.success) {
        alert('Pertanyaan berhasil dihapus');
        fetchQuestions();
      }
    } catch (error) {
      alert('Gagal menghapus pertanyaan: ' + (error.response?.data?.message || error.message));
    }
  }
};

const handleDuplicateQuestion = async (questionId) => {
  try {
    const response = await duplicateQuestion(questionId);
    if (response.success) {
      alert('Pertanyaan berhasil diduplikat');
      fetchQuestions();
    }
  } catch (error) {
    alert('Gagal menduplikat pertanyaan: ' + (error.response?.data?.message || error.message));
  }
};

const moveUp = async (index) => {
  if (index === 0) return;

  const newQuestions = [...questions.value];
  [newQuestions[index], newQuestions[index - 1]] = [newQuestions[index - 1], newQuestions[index]];
  questions.value = newQuestions;

  await saveOrder();
};

const moveDown = async (index) => {
  if (index === questions.value.length - 1) return;

  const newQuestions = [...questions.value];
  [newQuestions[index], newQuestions[index + 1]] = [newQuestions[index + 1], newQuestions[index]];
  questions.value = newQuestions;

  await saveOrder();
};

const saveOrder = async () => {
  try {
    const orderData = {
      questions: questions.value.map((q, idx) => ({
        id: q.id,
        order: idx + 1
      }))
    };
    await reorderQuestions(route.params.id, orderData);
  } catch (error) {
    console.error('Failed to save order:', error);
    alert('Gagal menyimpan urutan pertanyaan');
    fetchQuestions();
  }
};

const addOption = () => {
  questionForm.value.options.push('');
};

const removeOption = (index) => {
  questionForm.value.options.splice(index, 1);
};

const getQuestionTypeLabel = (type) => {
  const labels = {
    text: 'Teks Pendek',
    textarea: 'Teks Panjang',
    radio: 'Pilihan Tunggal',
    checkbox: 'Pilihan Ganda',
    dropdown: 'Dropdown',
    rating: 'Rating'
  };
  return labels[type] || type;
};

const getQuestionTypeBadgeClass = (type) => {
  const classes = {
    text: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    textarea: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    radio: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    checkbox: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    dropdown: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    rating: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
  };
  return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
};

const getTypeLabel = (type) => {
  const labels = {
    internal: 'Internal',
    external: 'Eksternal',
    public: 'Publik'
  };
  return labels[type] || type;
};

const getTypeBadgeClass = (type) => {
  const classes = {
    internal: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    external: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    public: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
  };
  return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
};

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    published: 'Terbit',
    closed: 'Ditutup'
  };
  return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    published: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    closed: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'
  };
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
};

onMounted(() => {
  fetchSurvey();
  fetchQuestions();
});
</script>
