<?php
// /src/Controller/MessagesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;
use Src\Service\MailService; // Added MailService

class MessagesController
{
    use RecentActivityLogger;

    /**
     * Create a message. 
     * Handles incoming contact forms and Admin replies.
     */
    public static function create(array $data): Message
    {
        $parentId = $data['parent_id'] ?? null;
        $conversationId = null;

        if ($parentId) {
            // Find the original message to get its conversation_id
            $parent = Message::find($parentId);
            if ($parent) {
                $conversationId = $parent->conversation_id;

                // If the parent didn't have a conversation_id yet, 
                // it means this is the FIRST reply. We should give the parent one now.
                if (!$conversationId) {
                    $conversationId = bin2hex(random_bytes(8));
                    $parent->update(['conversation_id' => $conversationId]);
                }
            }
        }

        $isSent = $data['is_sent'] ?? false;

        // Create the new message
        $newMessage = Message::create([
            'conversation_id' => $conversationId ?: bin2hex(random_bytes(8)),
            'parent_id'       => $parentId,
            'full_name'       => $data['full_name'] ?? 'Guest',
            'email'           => $data['email'] ?? '',
            'subject'         => $data['subject'] ?? 'No Subject',
            'message'         => $data['message'],
            'is_sent'         => $isSent,

            // FIX: Only mark as read if WE are the ones sending it (is_sent = true)
            'is_read'         => $isSent
        ]);

        // --- NEW: Trigger Email if this is a REPLY ---
        if ($isSent && $parentId) {
            self::sendReplyEmail($newMessage, (int)$parentId);
        }

        return $newMessage;
    }

    /**
     * Helper to notify the original sender via email
     */
    private static function sendReplyEmail(Message $reply, int $parentId): void
    {
        $parent = Message::find($parentId);

        // Only send if the parent exists and has an email address
        if ($parent && !empty($parent->email)) {
            $toEmail = $parent->email;
            $subject = "RE: " . ($parent->subject ?: "Inquiry Response");

            // Format body with Quicksand styling and primary colors
            $bodyContent = nl2br(htmlspecialchars($reply->message));
            $body = "
                <div style='font-family: \"Quicksand\", sans-serif; color: #000000; line-height: 1.6;'>
                    <h2 style='color: #ea580c;'>Hello, {$parent->full_name}</h2>
                    <p>You have received a new response regarding your message:</p>
                    
                    <div style='background-color: #f3f4f6; border-left: 4px solid #ea580c; padding: 20px; margin: 20px 0; border-radius: 8px;'>
                        <p style='margin: 0; font-style: italic; color: #374151;'>\"{$bodyContent}\"</p>
                    </div>

                    <p style='font-size: 0.875rem; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 15px;'>
                        This is an automated notification. To reply, please use our contact form.
                    </p>
                </div>
            ";

            MailService::send($toEmail, $subject, $body);
        }
    }

    /**
     * Renders a single table row for AJAX updates 💎
     */
    public static function renderRow(Message $message): string
    {
        $item = $message;
        ob_start();
        include __DIR__ . '/../../resources/components/messages/table-row.php';
        return ob_get_clean();
    }

    /**
     * Paginate messages for the Admin Inbox.
     */
    public static function paginate(string $folder = 'inbox', int $perPage = 50): LengthAwarePaginator
    {
        $query = Message::orderBy('created_at', 'desc');

        if (!AuthService::isAdmin()) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        switch ($folder) {
            case 'archived':
                $query->where('is_archived', true);
                break;
            case 'sent':
                $query->where('is_sent', true)->where('is_archived', false);
                break;
            case 'unread':
                $query->where('is_read', false)->where('is_archived', false);
                break;
            default:
                $query->where('is_draft', false)
                    ->where('is_sent', false)
                    ->where('is_archived', false);
                break;
        }

        return $query->paginate($perPage);
    }

    /**
     * Get message thread details for the drawer view.
     */
    public static function getForView(int $id): ?array
    {
        $msg = Message::find($id);
        if (!$msg || !AuthService::isAdmin()) return null;

        if ($msg->conversation_id) {
            $thread = Message::where('conversation_id', $msg->conversation_id)
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $thread = collect([$msg]);
        }

        // Mark all incoming messages in this thread as read
        Message::where('conversation_id', $msg->conversation_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return [
            'subject' => $msg->subject,
            'conversation_id' => $msg->conversation_id,
            'messages' => $thread->map(fn($m) => [
                'id'      => $m->id,
                'name'    => $m->full_name,
                'email'   => $m->email,
                'body'    => trim($m->message), // Added trim for safety
                'date'    => $m->created_at ? $m->created_at->format('M d, Y g:i A') : '',
                'is_sent' => $m->is_sent,
            ])->toArray()
        ];
    }

    public static function archive(int $id): bool
    {
        $msg = Message::find($id);
        if (!$msg) return false;

        $msg->is_archived = true;
        $saved = $msg->save();
        if ($saved) static::logActivity('Archived message', 'Message', $id);

        return $saved;
    }

    public static function delete(int $id): bool
    {
        $msg = Message::find($id);
        if (!$msg) return false;
        $msg->delete();
        static::logActivity('Deleted message', 'Message', $id);
        return true;
    }

    public static function markAsRead(int $id): bool
    {
        $msg = Message::find($id);
        if (!$msg) return false;
        $msg->is_read = true;
        return $msg->save();
    }

    public static function getUnreadCount(): int
    {
        if (!AuthService::isAdmin()) return 0;

        return (int) Message::where('is_read', false)
            ->where('is_sent', false)
            ->where('is_archived', false)
            ->count();
    }
}
