<template>
  <div class="rich-text-editor">
    <!-- Toolbar -->
    <div class="toolbar mb-2 flex flex-wrap items-center gap-1 rounded-t-lg border border-b-0 border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700">
      <!-- Text Formatting -->
      <button
        type="button"
        @click.prevent="execCommand('bold')"
        class="rounded p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
        title="Bold"
      >
        <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z" />
        </svg>
      </button>
      <button
        type="button"
        @click.prevent="execCommand('italic')"
        class="rounded p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
        title="Italic"
      >
        <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m-4 16h8M6 4h8" />
        </svg>
      </button>
      <button
        type="button"
        @click.prevent="execCommand('underline')"
        class="rounded p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
        title="Underline"
      >
        <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 5v8a5 5 0 0010 0V5M5 19h14" />
        </svg>
      </button>

      <div class="mx-2 h-6 w-px bg-gray-300 dark:bg-gray-600"></div>

      <!-- Lists -->
      <button
        type="button"
        @click.prevent="execCommand('insertUnorderedList')"
        class="rounded p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
        title="Bullet List"
      >
        <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <button
        type="button"
        @click.prevent="execCommand('insertOrderedList')"
        class="rounded p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
        title="Numbered List"
      >
        <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
      </button>

      <div class="mx-2 h-6 w-px bg-gray-300 dark:bg-gray-600"></div>

      <!-- Alignment -->
      <button
        type="button"
        @click.prevent="execCommand('justifyLeft')"
        class="rounded p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
        title="Align Left"
      >
        <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16" />
        </svg>
      </button>
      <button
        type="button"
        @click.prevent="execCommand('justifyCenter')"
        class="rounded p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
        title="Align Center"
      >
        <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M8 12h8M4 18h16" />
        </svg>
      </button>
      <button
        type="button"
        @click.prevent="execCommand('justifyRight')"
        class="rounded p-1.5 hover:bg-gray-200 dark:hover:bg-gray-600"
        title="Align Right"
      >
        <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M4 18h16" />
        </svg>
      </button>

      <div class="mx-2 h-6 w-px bg-gray-300 dark:bg-gray-600"></div>

      <!-- Heading -->
      <select
        @change="applyHeading"
        class="rounded border border-gray-300 bg-white px-2 py-1 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
      >
        <option value="">Normal</option>
        <option value="h1">Heading 1</option>
        <option value="h2">Heading 2</option>
        <option value="h3">Heading 3</option>
        <option value="h4">Heading 4</option>
      </select>
    </div>

    <!-- Editor Content -->
    <div
      ref="editorRef"
      contenteditable="true"
      @input="onInput"
      @blur="onBlur"
      :class="[
        'editor-content min-h-[200px] rounded-b-lg border border-gray-300 bg-white p-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white',
        error ? 'border-red-500 dark:border-red-500' : ''
      ]"
      v-html="content"
    ></div>

    <!-- Error Message -->
    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>

    <!-- Character Count (Optional) -->
    <p v-if="showCharCount" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      {{ characterCount }} karakter
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  showCharCount: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const editorRef = ref(null)
const content = ref(props.modelValue || '')

const characterCount = computed(() => {
  const text = editorRef.value?.innerText || ''
  return text.length
})

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue !== content.value) {
      content.value = newValue || ''
      if (editorRef.value && editorRef.value.innerHTML !== newValue) {
        editorRef.value.innerHTML = newValue || ''
      }
    }
  }
)

const execCommand = (command, value = null) => {
  document.execCommand(command, false, value)
  editorRef.value.focus()
  onInput()
}

const applyHeading = (event) => {
  const value = event.target.value
  if (value) {
    execCommand('formatBlock', value)
  } else {
    execCommand('formatBlock', 'p')
  }
  event.target.value = ''
}

const onInput = () => {
  nextTick(() => {
    const html = editorRef.value?.innerHTML || ''
    content.value = html
    emit('update:modelValue', html)
  })
}

const onBlur = () => {
  const html = editorRef.value?.innerHTML || ''
  emit('update:modelValue', html)
}
</script>

<style scoped>
.editor-content:focus {
  outline: none;
}

.editor-content :deep(h1) {
  font-size: 2em;
  font-weight: bold;
  margin: 0.67em 0;
}

.editor-content :deep(h2) {
  font-size: 1.5em;
  font-weight: bold;
  margin: 0.75em 0;
}

.editor-content :deep(h3) {
  font-size: 1.17em;
  font-weight: bold;
  margin: 0.83em 0;
}

.editor-content :deep(h4) {
  font-size: 1em;
  font-weight: bold;
  margin: 1em 0;
}

.editor-content :deep(ul),
.editor-content :deep(ol) {
  margin-left: 1.5em;
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.editor-content :deep(p) {
  margin: 0.5em 0;
}
</style>
