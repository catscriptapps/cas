<?php
// /src/Controller/InspectionLibraryController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Inspection;
use App\Models\Section;
use App\Models\InspectionPicture;
use App\Models\InspectionPictureSection;
use App\Models\InspectionPictureContract;
use App\Models\InspectionVideo;
use App\Models\InspectionVideoSection;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;
use Src\Service\ImageUploadService;
use Src\Service\VideoUploadService;
use Illuminate\Support\Collection;

/**
 * Manages the inspection-scoped Photo Library, Video Library, and Contracts
 * page: upload happens here (not per-section any more), then a photo/video
 * is made to appear in a section's grid -- or, for photos, the Contracts
 * page -- by toggling an assignment checkbox. Mirrors legacy's
 * hit_inspections_pics (library) + hit_inspections_pics_sections
 * (assignment) split; see InspectionPicture/InspectionPictureSection/
 * InspectionPictureContract and their video equivalents.
 *
 * A section's own photo/video grid (rendered inline in the fill-in screen)
 * reads from the junction tables too -- renderSectionPictureCard()/
 * renderSectionVideoCard() below are called from
 * InspectionDetailController::renderSectionContent().
 */
class InspectionLibraryController
{
    use RecentActivityLogger;

    private const LOCKED_MESSAGE = 'This report has been finalized and is locked. Reopen it for editing to make changes.';

    // ================================================================
    // Section-scoped rendering (consumed by InspectionDetailController)
    // ================================================================

    public static function picturesForSection(Inspection $inspection, Section $section): Collection
    {
        return InspectionPictureSection::with('picture')
            ->where('inspection_id', $inspection->id)
            ->where('section_id', $section->id)
            ->orderBy('pos_index')
            ->get()
            ->filter(fn($link) => $link->picture !== null)
            ->values();
    }

    public static function videosForSection(Inspection $inspection, Section $section): Collection
    {
        return InspectionVideoSection::with('video')
            ->where('inspection_id', $inspection->id)
            ->where('section_id', $section->id)
            ->orderBy('pos_index')
            ->get()
            ->filter(fn($link) => $link->video !== null)
            ->values();
    }

    public static function renderSectionPictureCard(InspectionPictureSection $link, bool $isLocked = false): string
    {
        return self::renderPictureCardPartial($link->picture, 'section', $isLocked, $link->description);
    }

    public static function renderSectionVideoCard(InspectionVideoSection $link, bool $isLocked = false): string
    {
        return self::renderVideoCardPartial($link->video, 'section', $isLocked, $link->description);
    }

    public static function renderContractPictureCard(InspectionPictureContract $link, bool $isLocked = false): string
    {
        return self::renderPictureCardPartial($link->picture, 'contract', $isLocked, $link->description);
    }

    /**
     * Total photo/video counts for the tab bar's live badges -- keyed to
     * match InspectionDetailController::VIRTUAL_TABS exactly.
     */
    public static function libraryCounts(Inspection $inspection): array
    {
        return [
            'library-photos' => InspectionPicture::where('inspection_id', $inspection->id)->count(),
            'library-videos' => InspectionVideo::where('inspection_id', $inspection->id)->count(),
        ];
    }

    // ================================================================
    // Virtual-tab content dispatch (Photo Library / Video Library / Contracts)
    // ================================================================

    public function content(Inspection $inspection, string $tab): array
    {
        $html = match ($tab) {
            'library-photos' => self::renderPhotoLibrary($inspection),
            'library-videos' => self::renderVideoLibrary($inspection),
            'contracts' => self::renderContracts($inspection),
            default => null,
        };

        if ($html === null) {
            return ['success' => false, 'messages' => ['Unknown tab.']];
        }

        $label = match ($tab) {
            'library-photos' => 'Photo Library',
            'library-videos' => 'Video Library',
            default => 'Contracts',
        };

        return ['success' => true, 'html' => $html, 'sectionName' => $label, 'counts' => self::libraryCounts($inspection)];
    }

    public static function renderPhotoLibrary(Inspection $inspection): string
    {
        $pictures = InspectionPicture::where('inspection_id', $inspection->id)
            ->orderByDesc('id')->get();

        $sections = InspectionDetailController::sectionsForInspection($inspection);

        $assignments = InspectionPictureSection::where('inspection_id', $inspection->id)
            ->get()->groupBy('picture_id');
        $contractIds = InspectionPictureContract::where('inspection_id', $inspection->id)
            ->pluck('picture_id')->all();

        $isLocked = $inspection->hasReport();
        $assetBase = getAssetBase();
        $encodedInspectionId = IdEncoder::encode((int)$inspection->id);

        ob_start();
        include __DIR__ . '/../../resources/views/components/inspections/photo-library.php';
        return ob_get_clean();
    }

    public static function renderVideoLibrary(Inspection $inspection): string
    {
        $videos = InspectionVideo::where('inspection_id', $inspection->id)
            ->orderByDesc('id')->get();

        $sections = InspectionDetailController::sectionsForInspection($inspection);

        $assignments = InspectionVideoSection::where('inspection_id', $inspection->id)
            ->get()->groupBy('video_id');

        $isLocked = $inspection->hasReport();
        $assetBase = getAssetBase();
        $encodedInspectionId = IdEncoder::encode((int)$inspection->id);

        ob_start();
        include __DIR__ . '/../../resources/views/components/inspections/video-library.php';
        return ob_get_clean();
    }

    public static function renderContracts(Inspection $inspection): string
    {
        $links = InspectionPictureContract::with('picture')
            ->where('inspection_id', $inspection->id)
            ->orderBy('pos_index')
            ->get()
            ->filter(fn($link) => $link->picture !== null)
            ->values();

        $isLocked = $inspection->hasReport();
        $assetBase = getAssetBase();
        $encodedInspectionId = IdEncoder::encode((int)$inspection->id);

        ob_start();
        include __DIR__ . '/../../resources/views/components/inspections/contracts-content.php';
        return ob_get_clean();
    }

    // ---- Card + checkbox-pill rendering helpers ----

    private static function renderPictureCardPartial(InspectionPicture $picture, string $mode, bool $isLocked, ?string $caption = null, string $sectionCheckboxesHtml = ''): string
    {
        $assetBase = getAssetBase();
        ob_start();
        try {
            include __DIR__ . '/../../resources/views/components/inspections/photo-card.php';
        } catch (\Throwable $e) {
            ob_end_clean();
            return '';
        }
        return ob_get_clean();
    }

    private static function renderVideoCardPartial(InspectionVideo $video, string $mode, bool $isLocked, ?string $caption = null, string $sectionCheckboxesHtml = ''): string
    {
        $assetBase = getAssetBase();
        ob_start();
        try {
            include __DIR__ . '/../../resources/views/components/inspections/video-card.php';
        } catch (\Throwable $e) {
            ob_end_clean();
            return '';
        }
        return ob_get_clean();
    }

    public static function renderLibraryPictureCard(InspectionPicture $picture, Collection $sections, Collection $assignedSectionLinks, bool $isContractAssigned, bool $isLocked): string
    {
        $checkboxes = self::buildPictureCheckboxPills($picture, $sections, $assignedSectionLinks, $isContractAssigned);
        return self::renderPictureCardPartial($picture, 'library', $isLocked, null, $checkboxes);
    }

    public static function renderLibraryVideoCard(InspectionVideo $video, Collection $sections, Collection $assignedSectionLinks, bool $isLocked): string
    {
        $checkboxes = self::buildVideoCheckboxPills($video, $sections, $assignedSectionLinks);
        return self::renderVideoCardPartial($video, 'library', $isLocked, null, $checkboxes);
    }

    private static function buildPictureCheckboxPills(InspectionPicture $picture, Collection $sections, Collection $assignedSectionLinks, bool $isContractAssigned): string
    {
        $encodedPictureId = IdEncoder::encode((int)$picture->id);
        $assignedSectionIds = $assignedSectionLinks->pluck('section_id')->map(fn($id) => (int)$id)->all();

        $html = '';
        foreach ($sections as $section) {
            $checked = in_array((int)$section->id, $assignedSectionIds, true);
            $encodedSectionId = IdEncoder::encode((int)$section->id);
            $name = htmlspecialchars($section->name);
            $html .= '<label class="inline-flex items-center gap-1 text-white text-[10px] font-semibold cursor-pointer bg-white/10 hover:bg-white/20 rounded px-1.5 py-0.5">'
                . '<input type="checkbox" data-action="toggle-picture-section" data-picture-id="' . $encodedPictureId . '" data-section-id="' . $encodedSectionId . '" class="w-3 h-3 rounded" ' . ($checked ? 'checked' : '') . '>'
                . $name . '</label>';
        }

        $html .= '<label class="inline-flex items-center gap-1 text-amber-200 text-[10px] font-black cursor-pointer bg-amber-500/20 hover:bg-amber-500/30 rounded px-1.5 py-0.5">'
            . '<input type="checkbox" data-action="toggle-picture-contract" data-picture-id="' . $encodedPictureId . '" class="w-3 h-3 rounded" ' . ($isContractAssigned ? 'checked' : '') . '>'
            . '&#128196; Contract</label>';

        return $html;
    }

    private static function buildVideoCheckboxPills(InspectionVideo $video, Collection $sections, Collection $assignedSectionLinks): string
    {
        $encodedVideoId = IdEncoder::encode((int)$video->id);
        $assignedSectionIds = $assignedSectionLinks->pluck('section_id')->map(fn($id) => (int)$id)->all();

        $html = '';
        foreach ($sections as $section) {
            $checked = in_array((int)$section->id, $assignedSectionIds, true);
            $encodedSectionId = IdEncoder::encode((int)$section->id);
            $name = htmlspecialchars($section->name);
            $html .= '<label class="inline-flex items-center gap-1 text-white text-[10px] font-semibold cursor-pointer bg-white/10 hover:bg-white/20 rounded px-1.5 py-0.5">'
                . '<input type="checkbox" data-action="toggle-video-section" data-video-id="' . $encodedVideoId . '" data-section-id="' . $encodedSectionId . '" class="w-3 h-3 rounded" ' . ($checked ? 'checked' : '') . '>'
                . $name . '</label>';
        }

        return $html;
    }

    // ================================================================
    // Photo library mutations
    // ================================================================

    public function uploadPictures(Inspection $inspection, array $files): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/');
            if (!$baseUploadDir) throw new \Exception('Base upload directory not found.');

            $targetDir = $baseUploadDir . '/inspections/' . $inspection->id . '/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $service = new ImageUploadService($targetDir, 2400, 88);
            $uploaded = $service->upload($files, fn(array $f) => $f);

            if (empty($uploaded) || (isset($uploaded['success']) && $uploaded['success'] === false)) {
                throw new \Exception($uploaded['message'] ?? 'Upload failed.');
            }

            $sections = InspectionDetailController::sectionsForInspection($inspection);
            $cardsHtml = '';

            foreach ($uploaded as $fileInfo) {
                $picture = InspectionPicture::create([
                    'inspection_id' => $inspection->id,
                    'filename' => $fileInfo['fileName'],
                    'date_created' => date('Y-m-d H:i:s'),
                ]);
                $cardsHtml .= self::renderLibraryPictureCard($picture, $sections, collect(), false, false);
            }

            static::logActivity("Added " . count($uploaded) . " photo(s) to the library for inspection: {$inspection->property_address}", 'Inspections');

            return ['success' => true, 'cardsHtml' => $cardsHtml, 'messages' => ['Photos uploaded to the library.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function deletePictureFromLibrary(Inspection $inspection, ?string $id): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $rawId = $id ? IdEncoder::decode($id) : null;
            $picture = $rawId ? InspectionPicture::where('id', $rawId)->where('inspection_id', $inspection->id)->first() : null;
            if (!$picture) throw new \Exception('Photo not found.');

            $baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/inspections/' . $inspection->id . '/');
            if ($baseUploadDir) {
                $absolute = realpath($baseUploadDir . '/' . $picture->filename);
                if ($absolute && strpos($absolute, $baseUploadDir) === 0 && file_exists($absolute)) {
                    @unlink($absolute);
                }
            }

            InspectionPictureSection::where('picture_id', $picture->id)->delete();
            InspectionPictureContract::where('picture_id', $picture->id)->delete();
            $picture->delete();

            return ['success' => true, 'messages' => ['Photo deleted from the library.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Deletes several library photos in one request (the library's "Delete
     * Selected" bulk action) -- same per-file disk cleanup + cascade
     * unassign as deletePictureFromLibrary(), just looped.
     */
    public function deletePicturesBulk(Inspection $inspection, array $ids): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/inspections/' . $inspection->id . '/');
            $deletedCount = 0;

            foreach ($ids as $encodedId) {
                $rawId = IdEncoder::decode((string)$encodedId);
                $picture = $rawId ? InspectionPicture::where('id', $rawId)->where('inspection_id', $inspection->id)->first() : null;
                if (!$picture) continue;

                if ($baseUploadDir) {
                    $absolute = realpath($baseUploadDir . '/' . $picture->filename);
                    if ($absolute && strpos($absolute, $baseUploadDir) === 0 && file_exists($absolute)) {
                        @unlink($absolute);
                    }
                }

                InspectionPictureSection::where('picture_id', $picture->id)->delete();
                InspectionPictureContract::where('picture_id', $picture->id)->delete();
                $picture->delete();
                $deletedCount++;
            }

            if ($deletedCount === 0) {
                return ['success' => false, 'messages' => ['No matching photo(s) found to delete.']];
            }

            return ['success' => true, 'deleted' => $deletedCount, 'messages' => [$deletedCount === 1 ? 'Photo deleted.' : "{$deletedCount} photos deleted."]];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function togglePictureSection(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $pictureId = IdEncoder::decode((string)($data['picture_id'] ?? ''));
            $sectionId = IdEncoder::decode((string)($data['section_id'] ?? ''));
            $picture = $pictureId ? InspectionPicture::where('id', $pictureId)->where('inspection_id', $inspection->id)->first() : null;
            $section = $sectionId ? Section::where('id', $sectionId)->where('company_id', $inspection->company_id)->first() : null;
            if (!$picture || !$section) throw new \Exception('Photo or section not found.');

            InspectionPictureSection::where('picture_id', $picture->id)->where('section_id', $section->id)->delete();

            if (!empty($data['value'])) {
                $nextPos = (int)(InspectionPictureSection::where('inspection_id', $inspection->id)->where('section_id', $section->id)->max('pos_index') ?? -1) + 1;
                InspectionPictureSection::create([
                    'inspection_id' => $inspection->id,
                    'picture_id' => $picture->id,
                    'section_id' => $section->id,
                    'pos_index' => $nextPos,
                    'date_created' => date('Y-m-d H:i:s'),
                ]);
            }

            return ['success' => true, 'messages' => ['Saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function togglePictureContract(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $pictureId = IdEncoder::decode((string)($data['picture_id'] ?? ''));
            $picture = $pictureId ? InspectionPicture::where('id', $pictureId)->where('inspection_id', $inspection->id)->first() : null;
            if (!$picture) throw new \Exception('Photo not found.');

            InspectionPictureContract::where('picture_id', $picture->id)->delete();

            if (!empty($data['value'])) {
                $nextPos = (int)(InspectionPictureContract::where('inspection_id', $inspection->id)->max('pos_index') ?? -1) + 1;
                InspectionPictureContract::create([
                    'inspection_id' => $inspection->id,
                    'picture_id' => $picture->id,
                    'pos_index' => $nextPos,
                    'date_created' => date('Y-m-d H:i:s'),
                ]);
            }

            return ['success' => true, 'messages' => ['Saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    // ---- Section-scoped photo mutations ----

    public function savePictureSectionCaption(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $pictureId = IdEncoder::decode((string)($data['picture_id'] ?? ''));
            $sectionId = IdEncoder::decode((string)($data['section_id'] ?? ''));
            $link = ($pictureId && $sectionId)
                ? InspectionPictureSection::where('inspection_id', $inspection->id)->where('picture_id', $pictureId)->where('section_id', $sectionId)->first()
                : null;
            if (!$link) throw new \Exception('Photo assignment not found.');

            $link->description = trim((string)($data['description'] ?? '')) ?: null;
            $link->save();

            return ['success' => true, 'messages' => ['Saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function reorderPictureSection(Inspection $inspection, string $encodedSectionId, array $encodedIds): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $sectionId = IdEncoder::decode($encodedSectionId);
            if (!$sectionId) throw new \Exception('Section not found.');

            foreach ($encodedIds as $index => $encodedId) {
                $rawId = IdEncoder::decode((string)$encodedId);
                if (!$rawId) continue;
                InspectionPictureSection::where('inspection_id', $inspection->id)
                    ->where('picture_id', $rawId)->where('section_id', $sectionId)
                    ->update(['pos_index' => $index]);
            }

            return ['success' => true, 'messages' => ['Photo order updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    // ---- Contract-scoped photo mutations ----

    public function saveContractCaption(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $pictureId = IdEncoder::decode((string)($data['picture_id'] ?? ''));
            $link = $pictureId ? InspectionPictureContract::where('inspection_id', $inspection->id)->where('picture_id', $pictureId)->first() : null;
            if (!$link) throw new \Exception('Contract photo not found.');

            $link->description = trim((string)($data['description'] ?? '')) ?: null;
            $link->save();

            return ['success' => true, 'messages' => ['Saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function reorderContracts(Inspection $inspection, array $encodedIds): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            foreach ($encodedIds as $index => $encodedId) {
                $rawId = IdEncoder::decode((string)$encodedId);
                if (!$rawId) continue;
                InspectionPictureContract::where('inspection_id', $inspection->id)
                    ->where('picture_id', $rawId)
                    ->update(['pos_index' => $index]);
            }

            return ['success' => true, 'messages' => ['Order updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    // ================================================================
    // Video library mutations
    // ================================================================

    public function uploadVideoChunk(Inspection $inspection, array $chunk): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/');
            if (!$baseUploadDir) throw new \Exception('Base upload directory not found.');

            $targetDir = $baseUploadDir . '/inspections/' . $inspection->id . '/videos/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $service = new VideoUploadService($targetDir, 200);

            $result = $service->handleChunk(
                $chunk['file'],
                $chunk['uuid'],
                $chunk['index'],
                $chunk['total'],
                $chunk['originalName']
            );

            if (($result['status'] ?? '') !== 'completed') {
                return ['success' => true, 'status' => 'uploading'];
            }

            $video = InspectionVideo::create([
                'inspection_id' => $inspection->id,
                'filename' => $result['fileName'],
                'date_created' => date('Y-m-d H:i:s'),
            ]);

            $sections = InspectionDetailController::sectionsForInspection($inspection);

            static::logActivity("Added a video to the library for inspection: {$inspection->property_address}", 'Inspections');

            return [
                'success' => true,
                'status' => 'completed',
                'files' => [['fileName' => $result['fileName']]],
                'cardHtml' => self::renderLibraryVideoCard($video, $sections, collect(), false),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function deleteVideoFromLibrary(Inspection $inspection, ?string $id): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $rawId = $id ? IdEncoder::decode($id) : null;
            $video = $rawId ? InspectionVideo::where('id', $rawId)->where('inspection_id', $inspection->id)->first() : null;
            if (!$video) throw new \Exception('Video not found.');

            $baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/inspections/' . $inspection->id . '/videos/');
            if ($baseUploadDir) {
                $absolute = realpath($baseUploadDir . '/' . $video->filename);
                if ($absolute && strpos($absolute, $baseUploadDir) === 0 && file_exists($absolute)) {
                    @unlink($absolute);
                }
            }

            InspectionVideoSection::where('video_id', $video->id)->delete();
            $video->delete();

            return ['success' => true, 'messages' => ['Video deleted from the library.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Deletes several library videos in one request -- same per-file disk
     * cleanup + cascade unassign as deleteVideoFromLibrary(), just looped.
     */
    public function deleteVideosBulk(Inspection $inspection, array $ids): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/inspections/' . $inspection->id . '/videos/');
            $deletedCount = 0;

            foreach ($ids as $encodedId) {
                $rawId = IdEncoder::decode((string)$encodedId);
                $video = $rawId ? InspectionVideo::where('id', $rawId)->where('inspection_id', $inspection->id)->first() : null;
                if (!$video) continue;

                if ($baseUploadDir) {
                    $absolute = realpath($baseUploadDir . '/' . $video->filename);
                    if ($absolute && strpos($absolute, $baseUploadDir) === 0 && file_exists($absolute)) {
                        @unlink($absolute);
                    }
                }

                InspectionVideoSection::where('video_id', $video->id)->delete();
                $video->delete();
                $deletedCount++;
            }

            if ($deletedCount === 0) {
                return ['success' => false, 'messages' => ['No matching video(s) found to delete.']];
            }

            return ['success' => true, 'deleted' => $deletedCount, 'messages' => [$deletedCount === 1 ? 'Video deleted.' : "{$deletedCount} videos deleted."]];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function toggleVideoSection(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $videoId = IdEncoder::decode((string)($data['video_id'] ?? ''));
            $sectionId = IdEncoder::decode((string)($data['section_id'] ?? ''));
            $video = $videoId ? InspectionVideo::where('id', $videoId)->where('inspection_id', $inspection->id)->first() : null;
            $section = $sectionId ? Section::where('id', $sectionId)->where('company_id', $inspection->company_id)->first() : null;
            if (!$video || !$section) throw new \Exception('Video or section not found.');

            InspectionVideoSection::where('video_id', $video->id)->where('section_id', $section->id)->delete();

            if (!empty($data['value'])) {
                $nextPos = (int)(InspectionVideoSection::where('inspection_id', $inspection->id)->where('section_id', $section->id)->max('pos_index') ?? -1) + 1;
                InspectionVideoSection::create([
                    'inspection_id' => $inspection->id,
                    'video_id' => $video->id,
                    'section_id' => $section->id,
                    'pos_index' => $nextPos,
                    'date_created' => date('Y-m-d H:i:s'),
                ]);
            }

            return ['success' => true, 'messages' => ['Saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function saveVideoSectionCaption(Inspection $inspection, array $data): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $videoId = IdEncoder::decode((string)($data['video_id'] ?? ''));
            $sectionId = IdEncoder::decode((string)($data['section_id'] ?? ''));
            $link = ($videoId && $sectionId)
                ? InspectionVideoSection::where('inspection_id', $inspection->id)->where('video_id', $videoId)->where('section_id', $sectionId)->first()
                : null;
            if (!$link) throw new \Exception('Video assignment not found.');

            $link->description = trim((string)($data['description'] ?? '')) ?: null;
            $link->save();

            return ['success' => true, 'messages' => ['Saved.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function reorderVideoSection(Inspection $inspection, string $encodedSectionId, array $encodedIds): array
    {
        try {
            if ($inspection->hasReport()) throw new \Exception(self::LOCKED_MESSAGE);

            $sectionId = IdEncoder::decode($encodedSectionId);
            if (!$sectionId) throw new \Exception('Section not found.');

            foreach ($encodedIds as $index => $encodedId) {
                $rawId = IdEncoder::decode((string)$encodedId);
                if (!$rawId) continue;
                InspectionVideoSection::where('inspection_id', $inspection->id)
                    ->where('video_id', $rawId)->where('section_id', $sectionId)
                    ->update(['pos_index' => $index]);
            }

            return ['success' => true, 'messages' => ['Video order updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
