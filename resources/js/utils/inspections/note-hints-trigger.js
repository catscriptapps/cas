// /resources/js/utils/inspections/note-hints-trigger.js
//
// Delegated on document since the three trigger locations span both a
// page-level field (Inspection Summary) and two that live inside
// #inspection-section-content, which gets replaced wholesale on every tab
// switch -- one listener here outlives all of that.

import { openNoteHintsModal } from '../../modals/note-hints-modal.js';

function resolveTargetTextarea(btn, type) {
    if (type === 'inspection_summary') return document.getElementById('inspection-summary-textarea');
    if (type === 'section_comment') return document.getElementById('section-comment-textarea');
    if (type === 'question_notes') return btn.closest('[data-question-id]')?.querySelector('[data-role="notes-textarea"]') ?? null;
    return null;
}

export function initNoteHintsTriggers() {
    if (document._noteHintsTriggersAttached) return;
    document._noteHintsTriggersAttached = true;

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="open-note-hints"]');
        if (!btn) return;

        const type = btn.dataset.hintType;
        const textarea = resolveTargetTextarea(btn, type);
        if (!textarea) return;

        openNoteHintsModal({ type, sectionId: btn.dataset.sectionId || null, textarea });
    });
}
