// /resources/js/utils/questions/load-questions.js

/**
 * Fetches and repaints the question list for a given (encoded) section id --
 * used both on tab switch and after add/edit/delete/reorder settle.
 */
export async function loadQuestionsForSection(encodedSectionId) {
    const list = document.getElementById('questions-list');
    const emptyState = document.getElementById('questions-empty-state');
    const countLabel = document.getElementById('questions-count');
    if (!list || !encodedSectionId) return;

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/questions?section_id=${encodeURIComponent(encodedSectionId)}`);
        const result = await response.json();

        if (!result.success) return;

        const rows = result.data || [];
        list.innerHTML = rows.map((r) => r.rowHtml).join('');
        if (countLabel) countLabel.textContent = String(rows.length);

        emptyState?.classList.toggle('hidden', rows.length > 0);
    } catch (error) {
        console.error('Failed to load questions:', error);
    }
}
