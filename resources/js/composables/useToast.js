import { reactive } from 'vue';

// Shared state across all instances
const state = reactive({
  toasts: []
});

let toastId = 0;

export function useToast() {
  const show = (message, type = 'success', duration = 3000) => {
    const id = toastId++;
    const toast = {
      id,
      message,
      type, // 'success', 'error', 'warning', 'info'
      visible: true
    };

    state.toasts.push(toast);

    if (duration > 0) {
      setTimeout(() => {
        remove(id);
      }, duration);
    }

    return id;
  };

  const remove = (id) => {
    const index = state.toasts.findIndex(t => t.id === id);
    if (index > -1) {
      state.toasts.splice(index, 1);
    }
  };

  const success = (message, duration = 3000) => {
    return show(message, 'success', duration);
  };

  const error = (message, duration = 4000) => {
    return show(message, 'error', duration);
  };

  const warning = (message, duration = 3500) => {
    return show(message, 'warning', duration);
  };

  const info = (message, duration = 3000) => {
    return show(message, 'info', duration);
  };

  return {
    toasts: state.toasts,
    show,
    remove,
    success,
    error,
    warning,
    info
  };
}
