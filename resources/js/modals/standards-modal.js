// /resources/js/modals/standards-modal.js

import Quill from 'quill';
// The build's CSS pipeline collapses every entry's stylesheet into a single
// app.min.css that the layout links, but page-script CSS imports land in
// their own separate chunk (app.min2.css, app.min3.css, ...) that nothing
// actually <link>s -- so a plain `import '...quill.snow.css'` would compile
// fine but never reach the browser. `?inline` sidesteps that entirely by
// pulling the CSS in as a string this module injects itself.
import quillSnowCss from 'quill/dist/quill.snow.css?inline';
import { Modal } from '../factories/modal-factory.js';
import { standardForm } from '../forms/standard-form.js';
import { handleStandardFormSubmission } from '../utils/standards/form-submit.js';

let standardModal = null;
let quillCssInjected = false;

function ensureQuillCss() {
    if (quillCssInjected) return;
    quillCssInjected = true;
    const style = document.createElement('style');
    style.setAttribute('data-quill-snow', '');
    style.textContent = quillSnowCss;
    document.head.appendChild(style);
}

function mountEditor(formId, initialContent) {
    const quill = new Quill(`#${formId}-editor`, {
        theme: 'snow',
        placeholder: 'Write this Standards of Practice page…',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'link'],
                ['clean'],
            ],
        },
    });

    if (initialContent) quill.clipboard.dangerouslyPasteHTML(initialContent);

    return quill;
}

function openModal(mode, { encodedId = null, content = '' } = {}) {
    ensureQuillCss();
    if (standardModal) standardModal.destroy();

    const formId = mode === 'edit' ? 'standard-edit-form' : 'standard-add-form';

    standardModal = new Modal({
        id: `${mode}-standard-modal`,
        title: mode === 'edit' ? 'Edit Standards Page' : 'New Standards Page',
        content: standardForm({ mode, encodedId, formId }),
        size: 'xl',
        showFooter: false,
    });
    standardModal.open();

    const quill = mountEditor(formId, content);
    const form = document.getElementById(formId);
    handleStandardFormSubmission(form, mode, standardModal, quill);
}

export function openEditStandardModal(encodedId, content) {
    openModal('edit', { encodedId, content });
}

export function initStandardsModal() {
    const addBtn = document.getElementById('add-standard-page-btn');
    if (addBtn && !addBtn._standardsModalBound) {
        addBtn._standardsModalBound = true;
        addBtn.addEventListener('click', () => openModal('add'));
    }
}
