// /resources/js/pages/about-page.js

import { AnimationEngine } from '../utils/animations';

/**
 * Initialize the About page events
 */
export function init() {
    AnimationEngine.refresh();
    handleAboutScroll();
}

/**
 * Custom scroll effects for the Orange/Navy hero blur
 */
function handleAboutScroll() {
    const heroGradient = document.querySelector('.bg-gradient-to-b');

    window.addEventListener('scroll', () => {
        if (!heroGradient) return;
        const scroll = window.scrollY;
        heroGradient.style.transform = `translateY(${scroll * 0.15}px)`;
    });
}