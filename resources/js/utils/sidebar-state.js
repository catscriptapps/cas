// /resources/js/utils/sidebar-state.js
import Alpine from 'alpinejs';

export function initSidebarStore() {
    document.addEventListener('alpine:init', () => {
        Alpine.store('sidebar', {
            expanded: localStorage.getItem('sidebarExpanded') !== 'false',
            toggle() {
                this.expanded = !this.expanded;
                localStorage.setItem('sidebarExpanded', this.expanded);
            }
        });
    });
}