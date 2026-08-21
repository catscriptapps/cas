// /resources/js/modals/questions-modal.js

import { Modal } from '../factories/modal-factory.js';
import { questionForm } from '../forms/question-form.js';
import { enableQuestionRepeaters } from '../components/repeater-fields.js';
import { handleQuestionFormSubmission } from '../utils/questions/question-form-submit.js';

let questionModal = null;

function initFormFeatures(formId, mode, modalInstance) {
    const form = document.getElementById(formId);
    if (!form) return;

    handleQuestionFormSubmission(form, mode, modalInstance);
    enableQuestionRepeaters(formId);
}

export function openAddQuestionModal() {
    const tabsWrapper = document.getElementById('section-tabs-wrapper');
    const sectionId = tabsWrapper?.dataset.activeSectionId;
    if (!sectionId) return;

    if (questionModal) questionModal.destroy();

    questionModal = new Modal({
        id: 'add-question-modal',
        title: 'New Question',
        content: questionForm({
            mode: 'add',
            formId: 'question-add-form',
            buttonLabel: 'Create Question',
            sectionId,
        }),
        size: 'lg',
        showFooter: false,
    });

    questionModal.open();
    initFormFeatures('question-add-form', 'add', questionModal);
}

export function openEditQuestionModal(trigger) {
    const btn = trigger.closest('.edit-question-btn') || trigger;
    if (!btn?.dataset) return;

    const data = btn.dataset;
    let options = [];
    let fields = [];
    try { options = JSON.parse(data.options || '[]'); } catch (e) { /* ignore malformed data */ }
    try { fields = JSON.parse(data.fields || '[]'); } catch (e) { /* ignore malformed data */ }

    if (questionModal) questionModal.destroy();

    questionModal = new Modal({
        id: 'edit-question-modal',
        title: 'Edit Question',
        content: questionForm({
            mode: 'edit',
            formId: 'question-edit-form',
            questionTitle: data.questionTitle || '',
            answerMode: data.answerMode || 0,
            isCondition: data.isCondition === '1',
            options,
            fields,
            sectionId: data.sectionId,
            buttonLabel: 'Save Changes',
            encodedId: data.encodedId,
        }),
        size: 'lg',
        showFooter: false,
    });

    questionModal.open();
    initFormFeatures('question-edit-form', 'edit', questionModal);
}

let listenersAttached = false;
export function initQuestionsModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        if (e.target.closest('#add-question-btn')) {
            e.preventDefault();
            openAddQuestionModal();
            return;
        }

        const editBtn = e.target.closest('.edit-question-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEditQuestionModal(editBtn);
        }
    });

    listenersAttached = true;
}
