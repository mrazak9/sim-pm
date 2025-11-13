import { ref } from 'vue';

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
