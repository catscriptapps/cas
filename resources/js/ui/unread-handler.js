// /resources/js/ui/unread-handler.js

import { showToast } from './toast.js';

/**
 * One request to rule them all.
 * Updates every badge in the header in a single heartbeat, and pops a toast
 * when a brand new notification arrives while the tab is open (e.g. a
 * landlord/tenant replies to a maintenance ticket you're currently viewing).
 */
export function initUnreadPolling() {
  const badges = {
    messages: document.getElementById('messages-badge'),
    chats: document.getElementById('chats-badge'),
    notifications: document.getElementById('notifications-badge')
  };

  // null until the first successful poll, so we never toast for unread
  // notifications that were already sitting there before this page load.
  let previousNotificationsCount = null;

  async function announceNewNotification() {
    try {
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const res = await fetch(`${baseUrl}api/notifications`, { cache: 'no-store' });
      const data = await res.json();

      if (data.success && data.latest) {
        showToast(data.latest.message || data.latest.subject || 'You have a new notification.', 'success');
      }
    } catch (err) {
      console.error('Failed to fetch latest notification for toast:', err);
    }
  }

  async function updateAllBadges() {
    try {
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const res = await fetch(`${baseUrl}api/global-unread`, { cache: 'no-store' });
      const data = await res.json();

      if (data.success) {
        Object.keys(badges).forEach(key => {
          const el = badges[key];
          const count = data.counts[key];

          if (el && count > 0) {
            el.textContent = count > 99 ? '99+' : count;
            el.classList.remove('hidden');
          } else if (el) {
            el.classList.add('hidden');
            el.textContent = '';
          }
        });

        const newNotificationsCount = data.counts.notifications;
        if (previousNotificationsCount !== null && newNotificationsCount > previousNotificationsCount) {
          announceNewNotification();
        }
        previousNotificationsCount = newNotificationsCount;
      }
    } catch (err) {
      console.error('Heartbeat failed:', err);
    }
  }

  // Shorter than the app's other 30s heartbeat -- this one specifically
  // backs "is the other person in this conversation replying right now"
  // (e.g. a tenant/landlord both looking at the same maintenance ticket),
  // so it's worth polling noticeably faster.
  updateAllBadges();
  setInterval(updateAllBadges, 10000);
}
