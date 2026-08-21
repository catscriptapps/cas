// /resources/js/pages/standards-page.js

import { AnimationEngine } from '../utils/animations';
import { initStandardsModal } from '../modals/standards-modal.js';
import { initStandardsList } from '../utils/standards/list.js';

export function init() {
    AnimationEngine.refresh();
    initStandardsModal();
    initStandardsList();
}
