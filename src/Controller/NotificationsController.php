<?php
// /src/Controller/NotificationsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Notification;

class NotificationsController
{
    /**
     * Create a notification for a tenant, landlord, or (staff) user.
     */
    public static function create(string $recipientType, int $recipientId, string $type, string $subject, string $message, ?string $link = null): Notification
    {
        return Notification::create([
            'recipient_type' => $recipientType,
            'recipient_id'   => $recipientId,
            'type'           => $type,
            'subject'        => $subject,
            'message'        => $message,
            'link'           => $link,
        ]);
    }

    /**
     * Unread count for the given account — powers the header bell badge.
     */
    public static function getUnreadCount(string $recipientType, int $recipientId): int
    {
        return Notification::where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Most recent notifications for the given account, newest first.
     *
     * @return \Illuminate\Support\Collection<int, Notification>
     */
    public static function getLatest(string $recipientType, int $recipientId, int $limit = 20): \Illuminate\Support\Collection
    {
        return Notification::where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->orderBy('date_created', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * The single most recent unread notification, if any — powers the
     * "new reply" toast (the header poll fetches this only when it sees the
     * unread count go up, so it knows what to say and where it points).
     */
    public static function getLatestUnread(string $recipientType, int $recipientId): ?Notification
    {
        return Notification::where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->orderBy('date_created', 'desc')
            ->first();
    }

    /**
     * Whether the given recipient already has an unread notification pointing
     * at this exact link -- used to throttle duplicate ticket/thread emails
     * while an earlier unread in-app notification for the same link is still
     * sitting in the bell.
     */
    public static function hasUnreadForLink(string $recipientType, int $recipientId, string $link): bool
    {
        return Notification::where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->where('link', $link)
            ->exists();
    }

    /**
     * Mark a single notification read — ownership-checked so one account can't
     * mark another's notification as read.
     */
    public static function markRead(int $id, string $recipientType, int $recipientId): array
    {
        $notification = Notification::where('id', $id)
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->first();

        if (!$notification) {
            return ['success' => false, 'messages' => ['Notification not found.']];
        }

        if (!$notification->is_read) {
            $notification->is_read = true;
            $notification->save();
        }

        return ['success' => true];
    }

    /**
     * Mark every notification for the given account as read.
     */
    public static function markAllRead(string $recipientType, int $recipientId): array
    {
        Notification::where('recipient_type', $recipientType)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return ['success' => true];
    }
}
