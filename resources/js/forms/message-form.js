// /resources/js/forms/message-form.js

/**
 * Modern, sleek shared Message form renderer - PURPLE Edition 💜
 */
export function messageForm({
    mode = 'add',
    recipient = '',
    subject = '',
    message = '',
    buttonLabel = 'Send Message',
    formId = 'messages-form',
    encodedId = null
}) {
    const idPrefix = mode === 'reply' ? 'msg-reply' : 'msg-add';
    const dataEncodedIdAttr = encodedId ? `data-encoded-id="${encodedId}"` : '';

    const inputClasses = `
        block w-full rounded-xl 
        border border-gray-400 dark:border-gray-600 
        bg-white dark:bg-gray-900 
        text-gray-900 dark:text-white 
        placeholder:text-gray-400 
        focus:border-primary-500 focus:ring-primary-500 
        sm:text-sm transition-all duration-200 py-2.5 px-4
    `.replace(/\s+/g, ' ').trim();

    const labelClasses = "block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5 ml-1";

    return `
    <form 
        id="${formId}" 
        class="w-full max-w-2xl mx-auto space-y-5 p-1 font-sans"
        novalidate 
        ${dataEncodedIdAttr}>

        <div>
            <label for="${idPrefix}-recipient" class="${labelClasses}">Recipient Email</label>
            <div class="relative group">
                <input type="email" required id="${idPrefix}-recipient" name="email"
                    placeholder="client@example.com" 
                    value="${recipient}" 
                    class="${inputClasses}" 
                    autocomplete="off" />
            </div>
        </div>

        <div>
            <label for="${idPrefix}-subject" class="${labelClasses}">Subject</label>
            <div class="relative group">
                <input type="text" required id="${idPrefix}-subject" name="subject"
                    placeholder="Project Update..." 
                    value="${subject}" 
                    class="${inputClasses}" 
                    autocomplete="off" />
            </div>
        </div>

        <div>
            <label for="${idPrefix}-message" class="${labelClasses}">Message</label>
            <textarea id="${idPrefix}-message" name="message" required rows="6"
                placeholder="Type your message here..." 
                class="${inputClasses} resize-none text-sm">${message}</textarea>
        </div>

        <div class="flex items-center justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" id="${idPrefix}-submit"
                class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-10 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary-500/30 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all active:scale-95">
                ${buttonLabel}
            </button>
        </div>
    </form>
    `;
}