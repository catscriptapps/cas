// /resources/js/utils/messages/unread-badge.js

/**
 * The topbar's Messages icon (admin-only, see layout-topbar.php) shows an
 * unread-count badge whose initial value is rendered server-side into the
 * shared Alpine scope in layout-header.php. That element never re-renders
 * on SPA navigation, so anything that changes how many messages are unread
 * (reading one, archiving/deleting an unread one) has to push the new
 * number to it explicitly -- this is that one shared broadcast point, so
 * every call site does it the same way instead of re-inventing the event
 * name/shape.
 */
export function broadcastUnreadCount(count) {
    if (typeof count !== 'number') return;
    window.dispatchEvent(new CustomEvent('messages-unread-changed', { detail: { count } }));
}
