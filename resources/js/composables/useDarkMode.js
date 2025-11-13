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
