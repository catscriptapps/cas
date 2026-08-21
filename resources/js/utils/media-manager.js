// /resources/js/utils/media-manager.js

import { createUploadHandler, uploadModal } from '../modals/upload-modal.js';
import { showToast } from '../ui/toast.js';
import { registerImagePreview } from '../utils/globals/preview.js';
import { confirmDialog } from '../ui/confirm.js';

/**
 * Reusable delete handler for view modal pictures
 * Matches the unified media-delete.php endpoint
 */
export async function deleteMedia(config) {
    let btnClass = 'bg-red-600 hover:bg-red-700';
    const confirmed = await confirmDialog("This photo will be permanently removed. Continue?", "Delete", "Cancel", btnClass);
    if (!confirmed) return;

    const baseUrl = window.APP_CONFIG.baseUrl;
    
    // We use FormData to match the avatar-delete style (POST)
    const formData = new FormData();
    formData.append('id', config.picId);
    formData.append('type', config.type);

    try {
        const response = await fetch(`${baseUrl}api/media-delete`, { 
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        });
        
        const result = await response.json();

        if (result.success) {
            showToast("🗑️ Photo deleted.", "success");
            
            /**
             * Trigger Refresh:
             * The view-media.js files should be listening for this event.
             * When triggered, it calls viewMedia(), which clears the grid,
             * re-fetches the list, and automatically shows the 'No photos' 
             * state if the length is 0.
             */
            document.dispatchEvent(new CustomEvent(`${config.type}:pics-updated`, { 
                detail: { id: config.parentId } 
            }));
        } else {
            showToast(result.message || "Could not delete photo.", "error");
        }
    } catch (err) {
        showToast("Server error. Please try again.", "error");
        console.error(err);
    }
}

/**
 * Reusable handler for Ad/Quotation pictures, for example
 */
export async function openMediaUpload(config) {
    // Ensure the previewer is registered when someone opens the upload/view UI
    registerImagePreview(); 

    const baseUrl = window.APP_CONFIG.baseUrl;
    const endpoint = `${baseUrl}api/${config.type}-upload-pics?id=${config.id}`;
    const fetchEndpoint = `${baseUrl}api/${config.type}-pictures?id=${config.id}`;

    try {
        const response = await fetch(fetchEndpoint);
        const result = await response.json();
        const currentCount = result.success ? result.pictures.length : 0;
        
        const maxLimit = window.APP_CONFIG.mediaLimit || 4;
        const remainingSlots = maxLimit - currentCount;

        if (remainingSlots <= 0) {
            showToast(`⚠️ You have reached the limit of ${maxLimit} pictures.`, 'error');
            return;
        }

        uploadModal.open();

        setTimeout(() => {
            createUploadHandler(
                endpoint,
                'images', 
                () => {
                    showToast(`📸 ${config.type} gallery updated!`, 'success');
                    document.dispatchEvent(new CustomEvent(`${config.type}:pics-updated`, { 
                        detail: { id: config.id } 
                    }));
                },
                4,
                false,
                { 
                    autoProcess: true, 
                    skipOptimization: true,
                    maxFiles: remainingSlots 
                }
            );
        }, 50);

    } catch (err) {
        showToast("Could not verify current picture count.", "error");
    }
}