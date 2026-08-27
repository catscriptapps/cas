// /resources/js/modals/mission-editor-modal.js

import { Modal } from '../factories/modal-factory.js';
import { showToast } from '../ui/toast.js';
import { showSpinner, hideSpinner } from '../ui/spinner.js';

/**
 * Admin-only editor for the home page's "Our Mission" content block (see
 * HomePageTextController). Quill is loaded lazily and only for an admin who
 * actually clicks "Edit This Section" -- guests never fetch it, and even an
 * admin who never opens the editor doesn't pay for it. Loaded via a real
 * injected <script> tag (not server-rendered markup) since this page can be
 * reached by a full page load OR by an SPA partial swap that only replaces
 * #main-content's innerHTML -- a <script> tag set via innerHTML never
 * executes, so a static <script src> in the PHP template would silently
 * fail to load Quill on the second and later visits in the same session.
 */

const QUILL_JS = 'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js';
const QUILL_CSS = 'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css';

// Quill's built-in size format defaults to CSS classes (ql-size-*), which
// only render correctly inside Quill's own .ql-editor stylesheet scope --
// the public home page renders this HTML in a plain div, not a Quill
// container, so those classes would silently do nothing there. Switching to
// the style-based attributor bakes a real `font-size` inline style into the
// saved HTML instead, which renders identically everywhere.
const SIZES = ['12px', '14px', '16px', '18px', '24px', '32px'];

let quillLoadPromise = null;

function loadQuill() {
    if (window.Quill) {
        registerQuillFormats();
        return Promise.resolve();
    }
    if (quillLoadPromise) return quillLoadPromise;

    quillLoadPromise = new Promise((resolve, reject) => {
        if (!document.querySelector('link[data-quill-css]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = QUILL_CSS;
            link.setAttribute('data-quill-css', 'true');
            document.head.appendChild(link);
        }

        const script = document.createElement('script');
        script.src = QUILL_JS;
        script.onload = () => {
            registerQuillFormats();
            resolve();
        };
        script.onerror = () => {
            quillLoadPromise = null; // allow a retry on the next click
            reject(new Error('Failed to load the editor. Check your connection and try again.'));
        };
        document.head.appendChild(script);
    });

    return quillLoadPromise;
}

function registerQuillFormats() {
    if (window.Quill.__missionFormatsRegistered) return;
    const SizeStyle = window.Quill.import('attributors/style/size');
    SizeStyle.whitelist = SIZES;
    window.Quill.register(SizeStyle, true);
    window.Quill.__missionFormatsRegistered = true;
}

let modalInstance = null;
let quillInstance = null;

async function openEditModal() {
    const contentEl = document.getElementById('mission-content');
    if (!contentEl) return;

    showSpinner();
    try {
        await loadQuill();
    } catch (err) {
        hideSpinner();
        showToast(err.message, 'error');
        return;
    }
    hideSpinner();

    if (modalInstance) modalInstance.destroy();

    // Quill's stock CSS only labels its own default size keywords
    // (small/large/huge) -- since SIZES uses arbitrary pixel values instead,
    // every option would otherwise fall back to showing "Normal" in both the
    // dropdown list and the toolbar button itself, even though the applied
    // size is correct. These rules give each pixel value its own label.
    // Scoped to the modal, not #mission-quill-editor -- Quill inserts the
    // toolbar as a PRECEDING SIBLING of the container element you pass in,
    // not as a descendant of it, so a selector rooted at the editor's own
    // id would never match the toolbar's picker items.
    const sizeLabelCss = SIZES.map((s) => `
        #edit-mission-modal .ql-picker.ql-size .ql-picker-label[data-value="${s}"]::before,
        #edit-mission-modal .ql-picker.ql-size .ql-picker-item[data-value="${s}"]::before {
            content: "${s}";
        }
    `).join('\n');

    modalInstance = new Modal({
        id: 'edit-mission-modal',
        title: 'Edit Home Page Section',
        content: `
            <div class="space-y-4 font-sans">
                <style>
                    /* Scoped to this modal instance -- keeps pasted screenshots
                       from blowing out the editor/toolbar width. */
                    #mission-quill-editor .ql-editor img { max-width: 100%; height: auto; border-radius: 0.5rem; }
                    ${sizeLabelCss}
                </style>
                <div id="mission-quill-editor" style="height: 280px;"></div>
                <div class="api-message"></div>
                <div class="flex justify-end pt-2">
                    <button type="button" id="save-mission-btn"
                        class="inline-flex items-center gap-2 py-2.5 px-6 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-[0.98]">
                        Save Changes
                    </button>
                </div>
            </div>
        `,
        size: 'lg',
        showFooter: false,
    });

    // Modal's constructor inserts its markup into the DOM synchronously, so
    // #mission-quill-editor already exists here -- no need to wait on open().
    quillInstance = new window.Quill('#mission-quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                [{ size: SIZES }],
                [{ color: [] }, { background: [] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'image'],
                ['clean'],
            ],
        },
    });
    quillInstance.clipboard.dangerouslyPasteHTML(contentEl.innerHTML.trim());

    // Quill's built-in "image" toolbar button normally just embeds a raw
    // base64 blob -- override it to upload the file to our own server
    // instead, so save() has a real URL it can track and later clean up.
    quillInstance.getModule('toolbar').addHandler('image', () => {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/png, image/jpeg, image/gif, image/webp';
        input.onchange = () => {
            const file = input.files?.[0];
            if (file) uploadAndInsertImage(file);
        };
        input.click();
    });

    // Pasting a screenshot (e.g. from the clipboard after a Snipping Tool
    // capture) fires this before Quill's own paste handling -- intercepting
    // it here stops Quill from embedding a base64 copy itself.
    quillInstance.root.addEventListener('paste', (e) => {
        const items = e.clipboardData?.items || [];
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                e.preventDefault();
                const file = item.getAsFile();
                if (file) uploadAndInsertImage(file);
                return;
            }
        }
    });

    document.getElementById('save-mission-btn')?.addEventListener('click', saveMission);

    modalInstance.open();
}

async function uploadAndInsertImage(file) {
    if (!quillInstance) return;

    const range = quillInstance.getSelection(true) || { index: quillInstance.getLength() };
    const placeholder = 'Uploading image...';
    quillInstance.insertText(range.index, placeholder, { italic: true });
    quillInstance.setSelection(range.index + placeholder.length);

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const formData = new FormData();
        formData.append('image', file);

        const response = await fetch(`${baseUrl}api/home-page-image-upload`, {
            method: 'POST',
            body: formData,
        });
        const result = await response.json();

        quillInstance.deleteText(range.index, placeholder.length);

        if (result.success) {
            const assetBase = window.APP_CONFIG?.assetBase || '/';
            quillInstance.insertEmbed(range.index, 'image', `${assetBase}${result.url}`);
            quillInstance.setSelection(range.index + 1);
        } else {
            showToast(result.message || 'Image upload failed.', 'error');
        }
    } catch (err) {
        console.error('Mission image upload error:', err);
        quillInstance.deleteText(range.index, placeholder.length);
        showToast('Image upload failed.', 'error');
    }
}

async function saveMission() {
    const btn = document.getElementById('save-mission-btn');
    const apiMsg = document.querySelector('#edit-mission-modal .api-message');
    if (!btn || !quillInstance) return;

    btn.disabled = true;
    const originalLabel = btn.innerHTML;
    btn.innerHTML = 'Saving...';
    if (apiMsg) apiMsg.innerHTML = '';

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const response = await fetch(`${baseUrl}api/home-page-text`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text_content: quillInstance.root.innerHTML }),
        });
        const result = await response.json();

        if (result.success) {
            const contentEl = document.getElementById('mission-content');
            if (contentEl) contentEl.innerHTML = result.html;

            showToast('Home page content updated.', 'success');
            setTimeout(() => modalInstance?.close(), 400);
        } else {
            if (apiMsg) {
                apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">${result.messages?.[0] || 'Save failed.'}</div>`;
            }
        }
    } catch (err) {
        console.error('Mission save error:', err);
        if (apiMsg) {
            apiMsg.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">Unexpected error. Please try again.</div>`;
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalLabel;
    }
}

let listenersAttached = false;

export function initMissionEditor() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#edit-mission-btn');
        if (!btn) return;
        e.preventDefault();
        openEditModal();
    });

    listenersAttached = true;
}
