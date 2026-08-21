// /resources/js/utils/faqs/faq-accordion.js

/**
 * Initializes the smooth accordion toggle logic for FAQ cards.
 * Uses event delegation to support dynamically injected content.
 */
export function initFaqAccordion(containerSelector = '#faqs-container') {
    const container = document.querySelector(containerSelector);
    if (!container || container._accordionInitialized) return;

    container.addEventListener('click', (e) => {
        // 1. GATEKEEPER: Ignore clicks on action buttons (Edit/Delete)
        // If the click is inside a button, let the Modal/Delete JS handle it instead.
        if (e.target.closest('button')) {
            return;
        }

        // 2. Target the toggle button wrapper (the <div> in our case)
        const toggleBtn = e.target.closest('.faq-toggle-btn');
        if (!toggleBtn) return;

        const faqItem = toggleBtn.closest('.faq-item');
        const content = faqItem.querySelector('.faq-answer-container');
        const icon = toggleBtn.querySelector('.faq-arrow');
        const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';

        // 3. Optional: Close other open FAQs ("Single Accordion" feel)
        container.querySelectorAll('.faq-toggle-btn[aria-expanded="true"]').forEach(btn => {
            if (btn !== toggleBtn) {
                const otherItem = btn.closest('.faq-item');
                const otherContent = otherItem.querySelector('.faq-answer-container');
                const otherIcon = btn.querySelector('.faq-arrow');
                
                btn.setAttribute('aria-expanded', 'false');
                otherContent.style.maxHeight = '0px';
                if (otherIcon) otherIcon.classList.remove('rotate-180');
            }
        });

        // 4. Toggle current FAQ
        if (isExpanded) {
            toggleBtn.setAttribute('aria-expanded', 'false');
            content.style.maxHeight = '0px';
            if (icon) icon.classList.remove('rotate-180');
        } else {
            toggleBtn.setAttribute('aria-expanded', 'true');
            // Using scrollHeight allows for smooth expansion to any text length
            content.style.maxHeight = content.scrollHeight + 'px';
            if (icon) icon.classList.add('rotate-180');
        }
    });

    // Mark as initialized so we don't attach multiple listeners (prevents double-toggling)
    container._accordionInitialized = true;
}