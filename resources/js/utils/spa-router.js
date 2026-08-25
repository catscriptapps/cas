// /resources/js/utils/spa-router.js

/**
 * Partial SPA Router — handles navigation, history, and content loading.
 * Links with [data-partial] trigger partial loads.
 * Pages are PHP partials returning valid HTML <title> + content.
 */

import { showSpinner, hideSpinner } from './../ui/spinner.js';

/**
 * Updates active state of navigation links to match the sleek theme.
 */
export function updateActiveLink(url) {
  // Active CSS matching rules here
}

/**
 * Updated loadPartial for spa-router.js
 * Handles content injection and Document Title sync.
 */
export async function loadPartial(url, pushState = true, clickedLink = null) {
  try {
    showSpinner();

    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!response.ok) throw new Error(`Failed to load ${url}`);

    const html = await response.text();
    const appName = window.APP_CONFIG?.appName || 'Catscript';

    // 1. Title & Meta Summary extraction logic
    const urlPath = new URL(url, window.location.origin).pathname;
    // Strip the app base path (e.g. when hosted in a subfolder) before comparing,
    // matching server-side normalizePath() in server/helpers.php — the canonical
    // home route is '/home', not just '/'.
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    let relativePath = urlPath;
    if (baseUrl !== '/' && relativePath.startsWith(baseUrl)) {
      relativePath = '/' + relativePath.slice(baseUrl.length);
    }
    const isHome = relativePath === '/' || relativePath === '/index.php' || relativePath === '/home';

    const trigger = clickedLink || document.querySelector(`a[data-partial][href*="${urlPath.split('/').pop()}"]`);

    // Fall back to the server-supplied X-Page-Title/X-Page-Summary headers when this
    // load wasn't triggered by a clicked <a data-partial> (e.g. a programmatic redirect
    // after a form submission) — otherwise the header title/summary would go blank.
    let pageTitle = trigger?.getAttribute('data-title') || '';
    let pageSummary = trigger?.getAttribute('data-summary') || '';

    if (!pageTitle) {
      const headerTitle = response.headers.get('X-Page-Title');
      if (headerTitle) pageTitle = decodeURIComponent(headerTitle);
    }
    if (!pageSummary) {
      const headerSummary = response.headers.get('X-Page-Summary');
      if (headerSummary) pageSummary = decodeURIComponent(headerSummary);
    }

    if (pageTitle) {
      document.title = `${pageTitle} | ${appName}`;
    }

    // Dispatch global event caught by Alpine context within layout-header.php
    window.dispatchEvent(new CustomEvent('spa-navigation', {
      detail: { isHome, path: relativePath, title: pageTitle, summary: pageSummary }
    }));

    // 2. Style Cleanup
    document.querySelectorAll('style[data-page-style]').forEach(tag => tag.remove());

    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');

    doc.querySelectorAll('style').forEach(style => {
      style.setAttribute('data-page-style', 'true');
      document.head.appendChild(style);
    });

    // 3. Content Injection
    const masterContainer = document.querySelector('#main-content');
    if (masterContainer) {
      masterContainer.style.display = 'none';
      masterContainer.innerHTML = html.replace(/<style\b[^>]*>([\s\S]*?)<\/style>/gim, "");
      masterContainer.style.display = 'block';
    }

    // 4. State Management
    updateActiveLink(url);
    if (pushState) history.pushState({ url }, '', url);

    hideSpinner();
    window.scrollTo(0, 0);
    document.body.dispatchEvent(new CustomEvent('partial-load', { detail: { url } }));

  } catch (err) {
    console.error('SPA Load Error:', err);
    hideSpinner();
    // The caller already called e.preventDefault() on the clicked link before
    // we got here, so the browser's own navigation was cancelled -- if we
    // just stop here, the click silently does nothing. Fall back to a real
    // navigation so a transient fetch failure never looks like a dead link.
    window.location.href = url;
  }
}

export function bindPartialLinks() {
  document.body.addEventListener('click', (e) => {
    const link = e.target.closest('a[data-partial]');
    if (!link || link.target === '_blank' || e.metaKey || e.ctrlKey) return;

    e.preventDefault();
    loadPartial(link.href, true, link);
  });

  window.addEventListener('popstate', (e) => {
    const url = e.state?.url || window.location.href;
    loadPartial(url, false);
  });

  updateActiveLink(window.location.href);
}

window.loadPartial = loadPartial;