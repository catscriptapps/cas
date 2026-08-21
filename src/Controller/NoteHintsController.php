<?php
// /src/Controller/NoteHintsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\NoteHint;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;

/**
 * Manages the "Hints" pools that back every notes-type field on the
 * inspection detail page (Inspection Summary, per-section Question Notes,
 * Section Comments). Anyone who can fill out an inspection (Inspector,
 * Company Admin, platform Admin) can view/use a pool's hints; only a
 * Company Admin or platform Admin can add to or delete from it, mirroring
 * legacy's own gating.
 */
class NoteHintsController
{
    use RecentActivityLogger;

    private static function canManage(): bool
    {
        return AuthService::isCompanyAdmin() || AuthService::isAdmin();
    }

    /**
     * Inspection Summary hints are company-wide (no section); the other two
     * types are scoped to one Section, shared by every question/comment box
     * within it.
     */
    private static function resolveSectionId(string $type, $encodedSectionId): ?int
    {
        if ($type === NoteHint::TYPE_INSPECTION_SUMMARY) return null;
        return $encodedSectionId ? IdEncoder::decode((string)$encodedSectionId) : null;
    }

    public function index(?string $type, $encodedSectionId): array
    {
        $companyId = AuthService::currentUser()->company_id ?? 0;

        if (!in_array($type, NoteHint::TYPES, true)) {
            return ['success' => false, 'messages' => ['Invalid hint type.']];
        }

        $sectionId = self::resolveSectionId($type, $encodedSectionId);
        if ($type !== NoteHint::TYPE_INSPECTION_SUMMARY && !$sectionId) {
            return ['success' => false, 'messages' => ['Section not found.']];
        }

        $query = NoteHint::where('company_id', $companyId)->where('type', $type);
        $query = $sectionId ? $query->where('section_id', $sectionId) : $query->whereNull('section_id');

        $hints = $query->orderBy('pos_index', 'desc')->get();

        return [
            'success'   => true,
            'canManage' => self::canManage(),
            'hints'     => $hints->map(fn(NoteHint $h) => [
                'id'      => IdEncoder::encode((int)$h->id),
                'text'    => $h->hint_text,
                'created' => $h->created_at?->diffForHumans() ?? '',
            ])->values(),
        ];
    }

    public function save(array $data): array
    {
        try {
            if (!self::canManage()) throw new \Exception('Only a Company Admin can add hints.');

            $companyId = AuthService::currentUser()->company_id ?? 0;
            if (!$companyId) throw new \Exception('No company associated with this account.');

            $type = $data['type'] ?? '';
            if (!in_array($type, NoteHint::TYPES, true)) throw new \Exception('Invalid hint type.');

            $sectionId = self::resolveSectionId($type, $data['section_id'] ?? null);
            if ($type !== NoteHint::TYPE_INSPECTION_SUMMARY && !$sectionId) throw new \Exception('Section not found.');

            $text = trim((string)($data['text'] ?? ''));
            if ($text === '') throw new \Exception('Hint text is required.');

            $nextPos = (int)(NoteHint::where('company_id', $companyId)
                ->where('type', $type)
                ->where('section_id', $sectionId)
                ->max('pos_index') ?? -1) + 1;

            $hint = NoteHint::create([
                'company_id' => $companyId,
                'section_id' => $sectionId,
                'type'       => $type,
                'hint_text'  => $text,
                'pos_index'  => $nextPos,
            ]);

            static::logActivity("Added a {$type} hint", 'Note Hints');

            return [
                'success' => true,
                'hint' => [
                    'id'      => IdEncoder::encode((int)$hint->id),
                    'text'    => $hint->hint_text,
                    'created' => 'just now',
                ],
                'messages' => ['Hint saved.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function delete(?string $id): array
    {
        try {
            if (!self::canManage()) throw new \Exception('Only a Company Admin can delete hints.');

            $companyId = AuthService::currentUser()->company_id ?? 0;
            $rawId = $id ? IdEncoder::decode($id) : null;
            $hint = $rawId ? NoteHint::where('id', $rawId)->where('company_id', $companyId)->first() : null;
            if (!$hint) throw new \Exception('Hint not found.');

            $hint->delete();

            return ['success' => true, 'messages' => ['Hint deleted.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
