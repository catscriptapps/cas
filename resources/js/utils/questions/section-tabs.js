// /resources/js/utils/questions/section-tabs.js

import { showToast } from '../../ui/toast.js';
import { confirmDialog } from '../../ui/confirm.js';
import { openEditSectionModal } from '../../modals/sections-modal.js';
import { activateSectionTab } from './section-activation.js';

// Click-to-place reorder state, mirroring the Slideshow page's mechanism.
let activeReorderId = null;

function clearReorderHighlight() {
    if (!activeReorderId) return;
    const tab = document.querySelector(`.section-tab[data-section-id="${activeReorderId}"] .section-tab-btn`);
    tab?.classList.remove('ring-4', 'ring-secondary-500', 'ring-offset-2');
}

function handleSectionReorderClick(tabEl) {
    const tabsWrapper = document.getElementById('section-tabs-wrapper');
    if (!tabsWrapper) return;

    const clickedId = tabEl.dataset.sectionId;
    const btn = tabEl.querySelector('.section-tab-btn');

    if (!activeReorderId) {
        activeReorderId = clickedId;
        btn?.classList.add('ring-4', 'ring-secondary-500', 'ring-offset-2');
        return;
    }

    const sourceTab = tabsWrapper.querySelector(`.section-tab[data-section-id="${activeReorderId}"]`);
    clearReorderHighlight();
    activeReorderId = null;

    if (!sourceTab || sourceTab === tabEl) return;

    const allTabs = Array.from(tabsWrapper.querySelectorAll('.section-tab'));
    const sourceIndex = allTabs.indexOf(sourceTab);
    const targetIndex = allTabs.indexOf(tabEl);
    if (sourceIndex === targetIndex) return;

    sourceTab.remove();
    if (sourceIndex > targetIndex) {
        tabEl.before(sourceTab);
    } else {
        tabEl.after(sourceTab);
    }

    persistSectionOrder();
}

async function persistSectionOrder() {
    const tabsWrapper = document.getElementById('section-tabs-wrapper');
    if (!tabsWrapper) return;

    const ids = Array.from(tabsWrapper.querySelectorAll('.section-tab')).map((tab) => tab.dataset.sectionId);
    if (ids.length === 0) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/sections-reorder`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids }),
        });
        const result = await response.json();

        if (result.success) {
            showToast('Section order updated!', 'success');
        } else {
            showToast(result.message || 'Could not save the new order.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

async function deleteActiveSection() {
    const tabsWrapper = document.getElementById('section-tabs-wrapper');
    const activeId = tabsWrapper?.dataset.activeSectionId;
    if (!activeId) return;

    const activeBtn = tabsWrapper.querySelector(`.section-tab-btn[data-id="${activeId}"]`);
    const name = activeBtn?.dataset.name || 'this section';

    const confirmed = await confirmDialog(
        `"${name}" and all of its questions will be permanently removed. Continue?`,
        'Delete',
        'Cancel',
        'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/sections`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ _method: 'DELETE', id: activeId }),
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.messages?.[0] || 'Section deleted.', 'success');
            tabsWrapper.querySelector(`.section-tab[data-section-id="${activeId}"]`)?.remove();

            const remainingTab = tabsWrapper.querySelector('.section-tab');
            if (remainingTab) {
                activateSectionTab(remainingTab.dataset.sectionId);
            } else {
                tabsWrapper.dataset.activeSectionId = '';
                document.getElementById('section-content-header')?.classList.add('hidden');
                document.getElementById('section-content')?.classList.add('hidden');
                document.getElementById('sections-empty-state')?.classList.remove('hidden');
            }
        } else {
            showToast(result.messages?.[0] || 'Could not delete section.', 'error');
        }
    } catch (err) {
        showToast('Server error. Please try again.', 'error');
        console.error(err);
    }
}

export function initSectionTabs() {
    const tabsWrapper = document.getElementById('section-tabs-wrapper');
    if (!tabsWrapper) return;

    tabsWrapper.addEventListener('click', (e) => {
        const reorderBtn = e.target.closest('[data-action="reorder-section"]');
        if (reorderBtn) {
            e.preventDefault();
            e.stopPropagation();
            const tabEl = reorderBtn.closest('.section-tab');
            if (tabEl) handleSectionReorderClick(tabEl);
            return;
        }

        const selectBtn = e.target.closest('[data-action="select-section"]');
        if (selectBtn) {
            e.preventDefault();
            activateSectionTab(selectBtn.dataset.id);
        }
    });

    document.getElementById('edit-active-section-btn')?.addEventListener('click', () => {
        const activeId = tabsWrapper.dataset.activeSectionId;
        const activeBtn = tabsWrapper.querySelector(`.section-tab-btn[data-id="${activeId}"]`);
        if (!activeId || !activeBtn) return;

        openEditSectionModal({
            encodedId: activeId,
            name: activeBtn.dataset.name,
            icon: activeBtn.dataset.icon,
        });
    });

    document.getElementById('delete-active-section-btn')?.addEventListener('click', () => {
        deleteActiveSection();
    });
}
