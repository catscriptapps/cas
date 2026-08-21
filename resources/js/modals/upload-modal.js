// /resources/js/modals/upload-modal.js

import { showToast } from "../ui/toast";

// -------------------------------
// Modal controller (singleton)
// -------------------------------
export const uploadModal = (() => {
  let modalEl = null;

  function ensureModal() {
    if (modalEl) return modalEl;

    modalEl = document.createElement('div');
    modalEl.id = 'upload-modal';
    modalEl.className = 'fixed inset-0 z-[10010] hidden';

    modalEl.innerHTML = `
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-backdrop></div>
      <div class="relative mx-auto mt-10 w-full max-w-4xl px-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl flex flex-col max-h-[85vh] overflow-hidden">
          <header class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 font-sans">Upload Media</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-sans">HEIC Support + Auto-Optimization (< 50KB)</p>
            </div>
            <div class="flex items-center gap-3">
              <button class="text-xs font-medium text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 transition-colors font-sans" data-clear>Clear All</button>
              <button class="px-5 py-2 text-sm font-bold rounded-lg bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-primary-500/30 transition-all font-sans" data-final-upload disabled>Upload (0)</button>
              <button class="p-2 text-gray-400 hover:text-red-500 transition-colors" data-close>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          </header>

          <section class="p-6 space-y-6 overflow-y-auto">
            <div data-drop-zone
                 class="group flex flex-col items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-10 cursor-pointer transition-all hover:border-primary-500 hover:bg-primary-50/50 dark:hover:bg-primary-900/10">
              <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 text-primary-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0l-4 4m4-4l4 4M17 8v12m0 0l-4-4m4 4l4-4" /></svg>
              </div>
              <p class="text-gray-700 dark:text-gray-200 font-medium text-center font-sans">Drag files here or click to browse</p>
              <input type="file" multiple accept="image/*,.heic" data-file-input class="hidden" />
            </div>
            <div data-preview-grid class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"></div>
          </section>
        </div>
      </div>
    `;

    document.body.appendChild(modalEl);
    return modalEl;
  }

  return {
    open: () => ensureModal().classList.remove('hidden'),
    close: () => {
      if (!modalEl) return;
      modalEl.classList.add('hidden');
      // 🍊 Dispatch a custom event so others can listen
      modalEl.dispatchEvent(new CustomEvent('modal:closed'));
    }
  };
})();

// -------------------------------
// Worker Pool (The Engine)
// -------------------------------
class WorkerPool {
  constructor(size = 6) {
    this.size = size;
    this.workers = [];
    this.queue = [];
    this.active = new Map();
    this.ready = false;
    this.init();
  }

  async init() {
    try {
      const response = await fetch(`${window.APP_CONFIG.baseUrl}js/heic2any.min.js`);
      const libContent = await response.text();
      const workerSource = `
        self.window = self; // CRITICAL POLYFILL FROM ROOFING REPORTS
        ${libContent}

        // HEIC LIBRARY POLYFILL
        if (!self.document) {
            self.document = { createElement: (tag) => {
                if (tag === 'canvas') {
                    const c = new OffscreenCanvas(1, 1);
                    c.toBlob = (cb, type, q) => c.convertToBlob({type, quality: q}).then(cb);
                    return c;
                }
                return {};
            }};
        }

        async function forceCompress(blob, maxDim, quality) {
            const bitmap = await createImageBitmap(blob);
            let w = bitmap.width, h = bitmap.height;
            const ratio = Math.min(maxDim / w, maxDim / h, 1);
            w = Math.round(w * ratio); h = Math.round(h * ratio);

            const canvas = new OffscreenCanvas(w, h);
            canvas.getContext('2d').drawImage(bitmap, 0, 0, w, h);
            bitmap.close();

            let finalBlob = await canvas.convertToBlob({type: 'image/jpeg', quality: quality});
            
            // RECURSIVE CHECK: Target < 55KB
            if (finalBlob.size > 56320 && maxDim > 400) {
               return await forceCompress(finalBlob, maxDim * 0.8, quality * 0.8);
            }
            return finalBlob;
        }

        self.onmessage = async (e) => {
          const { id, file } = e.data;
          let blob = new Blob([file.buffer], { type: file.type });
          try {
            // HEIC CONVERSION
            if (file.name.match(/\\.heic$/i)) {
              const converted = await heic2any({ blob, toType: 'image/jpeg', quality: 0.7 });
              blob = Array.isArray(converted) ? converted[0] : converted;
            }
            
            const finalBlob = await forceCompress(blob, 1200, 0.6);
            const previewBlob = await forceCompress(finalBlob, 300, 0.4);
            
            const fBuf = await finalBlob.arrayBuffer();
            const pBuf = await previewBlob.arrayBuffer();
            self.postMessage({ id, buffer: fBuf, previewBuffer: pBuf, size: finalBlob.size }, [fBuf, pBuf]);
          } catch (err) {
            self.postMessage({ id, error: err.message });
          }
        };
      `;
      this.workerUrl = URL.createObjectURL(new Blob([workerSource], { type: 'application/javascript' }));

      for (let i = 0; i < this.size; i++) {
        const w = new Worker(this.workerUrl);
        w.onmessage = (e) => {
          const { id, error, buffer, previewBuffer, size } = e.data;
          const callback = this.active.get(id);
          if (callback) callback({
            blob: buffer ? new Blob([buffer], { type: 'image/jpeg' }) : null,
            previewBlob: previewBuffer ? new Blob([previewBuffer], { type: 'image/jpeg' }) : null,
            error, size
          });
          this.active.delete(id);
          w.busy = false;
          this.next();
        };
        this.workers.push(w);
      }
      this.ready = true;
      this.next();
    } catch (err) { console.error(err); }
  }

  enqueue(file, id) {
    return new Promise(async (resolve) => {
      const buffer = await file.arrayBuffer();
      this.queue.push({ file: { buffer, name: file.name, type: file.type }, id, resolve });
      this.next();
    });
  }

  next() {
    if (!this.ready) return;
    const job = this.queue.shift();
    if (!job) return;
    const idle = this.workers.find(w => !w.busy);
    if (!idle) { this.queue.unshift(job); return; }
    idle.busy = true;
    this.active.set(job.id, (res) => job.resolve(res));
    idle.postMessage({ id: job.id, file: job.file }, [job.file.buffer]);
  }

  terminate() {
    this.workers.forEach(w => w.terminate());
    if (this.workerUrl) URL.revokeObjectURL(this.workerUrl);
  }
}

// -------------------------------
// Main Controller (Batching + Logic)
// -------------------------------
export function createUploadHandler(endpointUrl, context, onComplete, concurrency = 6, autoProcess = true, options = {}) {
  uploadModal.open();
  const modalEl = document.getElementById('upload-modal');
  let items = [];
  const pool = new WorkerPool(concurrency);
  const BATCH_SIZE = 20; // Server Safety Limit
  const MAX_ALLOWED = options.maxFiles || 999; // CatScript Limit Enforcement

  const fileInput = modalEl.querySelector('[data-file-input]');
  const dropZone = modalEl.querySelector('[data-drop-zone]');
  const previewGrid = modalEl.querySelector('[data-preview-grid]');
  const finalBtn = modalEl.querySelector('[data-final-upload]');

  if (options.single) fileInput.removeAttribute('multiple');
  else fileInput.setAttribute('multiple', '');

  const closeHandler = () => { pool.terminate(); uploadModal.close(); };
  modalEl.querySelector('[data-close]').onclick = closeHandler;
  modalEl.querySelector('[data-backdrop]').onclick = closeHandler;

  const updateUI = () => {
    const ready = items.filter(i => i.processed && !i.error).length;
    finalBtn.textContent = `Upload (${ready})`;
    finalBtn.disabled = ready === 0 || items.some(i => !i.processed && !i.error);
  };

  const handleFiles = async (fileList) => {
    let incoming = Array.from(fileList);

    // ENFORCE LIMIT: If we already have items or incoming exceeds max, slice it.
    const currentCount = items.length;
    if (currentCount + incoming.length > MAX_ALLOWED) {
      const allowedCount = MAX_ALLOWED - currentCount;
      if (allowedCount <= 0) {
        showToast(`Limit reached. You can only upload ${MAX_ALLOWED} images in total.`, 'error');
        return;
      }
      showToast(`Limit is ${MAX_ALLOWED}. Only the first ${allowedCount} files from your selection were added.`, 'error');
      incoming = incoming.slice(0, allowedCount);
    }

    if (options.single) {
      items.forEach(i => i.tile.remove());
      items = [];
    }
    for (const file of incoming) {
      const id = crypto.randomUUID();
      const tile = document.createElement('div');
      tile.className = 'bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden relative';
      tile.innerHTML = `
        <div class="h-28 bg-gray-200 dark:bg-gray-700 preview-img flex items-center justify-center relative">
          <div class="animate-spin rounded-full h-5 w-5 border-2 border-primary-500 border-t-transparent"></div>
        </div>
        <div class="p-2">
          <p class="text-[10px] truncate font-medium dark:text-gray-300 font-sans">${file.name}</p>
          <div class="w-full bg-gray-200 dark:bg-gray-600 h-1 mt-1 rounded-full overflow-hidden"><div class="bg-primary-500 h-full w-0 transition-all" data-bar></div></div>
          <p class="text-[9px] text-gray-500 mt-1 font-sans" data-status>Processing...</p>
        </div>
      `;
      previewGrid.appendChild(tile);

      const item = { id, tile, processed: false, blob: null };
      items.push(item);

      pool.enqueue(file, id).then(res => {
        if (res.error) {
          tile.querySelector('[data-status]').textContent = 'Error';
          tile.querySelector('[data-status]').classList.add('text-red-500');
        } else {
          item.blob = res.blob;
          item.processed = true;
          const url = URL.createObjectURL(res.previewBlob);
          tile.querySelector('.preview-img').innerHTML = `
                <img src="${url}" class="w-full h-full object-cover">
                <button data-del class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-xl z-10">✕</button>
            `;
          tile.querySelector('[data-del]').onclick = () => {
            tile.remove();
            items = items.filter(i => i.id !== id);
            updateUI();
          };
          tile.querySelector('[data-status]').textContent = (res.size / 1024).toFixed(1) + ' KB';
          tile.querySelector('[data-bar]').style.width = '100%';
        }
        updateUI();
      });
    }
    fileInput.value = '';
  };

  finalBtn.onclick = async () => {
    const readyItems = items.filter(i => i.processed && !i.error);
    if (!readyItems.length) return;

    finalBtn.disabled = true;
    const allUploadedFiles = [];

    // BATCH PROCESSING LOOP
    for (let i = 0; i < readyItems.length; i += BATCH_SIZE) {
      const chunk = readyItems.slice(i, i + BATCH_SIZE);
      finalBtn.textContent = `Batch ${Math.floor(i / BATCH_SIZE) + 1}...`;

      const fd = new FormData();
      chunk.forEach(item => fd.append('images[]', item.blob, 'img.jpg'));

      try {
        const response = await fetch(endpointUrl, {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const text = await response.text();
        const jsonStart = text.indexOf('{');
        const data = JSON.parse(text.substring(jsonStart));

        if (data.success) {
          // 🍊 FIX: Check for 'files' OR 'uploadedFiles' to match your PHP response
          const newFiles = data.files || data.uploadedFiles || [];
          allUploadedFiles.push(...newFiles);

          chunk.forEach(item => item.tile.remove());
        }
      } catch (e) {
        finalBtn.textContent = 'Retry Upload';
        finalBtn.disabled = false;
        return;
      }
    }

    items.forEach(i => i.tile.remove()); // Physically remove tiles
    items = [];
    updateUI();
    onComplete(allUploadedFiles);
    closeHandler();
  };

  dropZone.onclick = () => fileInput.click();
  fileInput.onchange = (e) => handleFiles(e.target.files);
  modalEl.querySelector('[data-clear]').onclick = () => {
    items.forEach(i => i.tile.remove());
    items = [];
    updateUI();
  };
}