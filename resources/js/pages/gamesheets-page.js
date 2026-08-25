// /resources/js/pages/gamesheets-page.js

import { initSeasonsModule } from '../utils/seasons/seasons-init.js';
import { initGamesheetDetail } from '../utils/gamesheets/gamesheet-detail.js';

export function init() {
    initSeasonsModule('gamesheets');
    initGamesheetDetail();
}
