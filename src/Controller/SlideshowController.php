<?php
// /src/Controller/SlideshowController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Slide;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin CRUD for the home page hero's rotating background images. Ported
 * from a hardcoded array in layout-header.php that had no admin module at
 * all -- see that file and Slide's own docblock for the full context.
 */
class SlideshowController
{
    use RecentActivityLogger;

    /**
     * @return array{encoded_id: string, filename: string}[]
     */
    public function getAll(): array
    {
        return Slide::orderBy('sort_order')->orderBy('slide_id')->get()
            ->map(fn($s) => [
                'encoded_id' => IdEncoder::encode($s->slide_id),
                'filename' => $s->filename,
            ])->all();
    }

    /**
     * Plain ordered filename list -- what the public hero itself renders.
     * Never throws: a DB hiccup here must not take down every page's hero.
     */
    public function getFilenames(): array
    {
        try {
            $filenames = Slide::orderBy('sort_order')->orderBy('slide_id')->pluck('filename')->all();
            return $filenames ?: $this->fallbackFilenames();
        } catch (\Throwable) {
            return $this->fallbackFilenames();
        }
    }

    private function fallbackFilenames(): array
    {
        return array_map(
            fn($f) => 'images/home/' . $f,
            ['hero-1.png', 'hero-2.png', 'hero-4.png', 'hero-7.png', 'hero-9.png', 'hero-11.png', 'hero-13.png', 'hero-17.png']
        );
    }

    /**
     * Uploads one or more images and creates a slideshow row for each in
     * one call -- unlike Sponsorship (which needs a name typed in for every
     * logo), a slide has no metadata beyond the image itself, so there's no
     * need for a separate "create" step the way sponsors has one.
     *
     * @param array $files The raw $_FILES['images'] multi-file array
     */
    public function uploadAndCreate(array $files): array
    {
        if (!AuthService::isAdmin()) {
            return ['success' => false, 'message' => "You don't have permission to do that."];
        }

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $maxBytes = 8 * 1024 * 1024;

        $uploadDir = __DIR__ . '/../../public/images/uploads/slideshow/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'message' => 'Could not create the upload directory.'];
        }

        $count = is_array($files['tmp_name'] ?? null) ? count($files['tmp_name']) : 0;
        $maxOrder = (int)(Slide::max('sort_order') ?? -1);

        $created = [];
        $errors = [];

        for ($i = 0; $i < $count; $i++) {
            $error = $files['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            $tmpName = $files['tmp_name'][$i] ?? null;
            $size = $files['size'][$i] ?? 0;

            if ($error !== UPLOAD_ERR_OK || !$tmpName || !is_uploaded_file($tmpName)) {
                $errors[] = 'One image failed to upload.';
                continue;
            }
            if ($size > $maxBytes) {
                $errors[] = 'One image was too large (8MB max) and was skipped.';
                continue;
            }

            $imageInfo = @getimagesize($tmpName);
            $mime = $imageInfo['mime'] ?? null;
            if (!$mime || !isset($allowedMimes[$mime])) {
                $errors[] = 'One image was an unsupported type and was skipped.';
                continue;
            }

            $filename = 'slide-' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mime];
            if (!move_uploaded_file($tmpName, $uploadDir . $filename)) {
                $errors[] = 'One image could not be saved and was skipped.';
                continue;
            }

            $maxOrder++;
            $slide = Slide::create([
                'filename' => 'images/uploads/slideshow/' . $filename,
                'sort_order' => $maxOrder,
                'date_created' => date('Y-m-d'),
            ]);

            $created[] = ['encoded_id' => IdEncoder::encode($slide->slide_id), 'filename' => $slide->filename];
        }

        if (!empty($created)) {
            static::logActivity(count($created) . ' slideshow image(s) added', 'Slideshow');
        }

        return [
            // 'files', not 'slides' -- the shared uploader (upload-modal.js)
            // collects whatever comes back under data.files/data.uploadedFiles
            // and hands that straight to onComplete(); anything else key
            // name would silently vanish before this page's JS ever sees it.
            'success' => !empty($created),
            'files' => $created,
            'message' => implode(' ', array_unique($errors)) ?: null,
        ];
    }

    /**
     * @param string[] $encodedIds Ordered list of slide ids in their new order
     */
    public function reorder(array $encodedIds): array
    {
        try {
            if (!AuthService::isAdmin()) {
                throw new \Exception("You don't have permission to do that.");
            }

            foreach ($encodedIds as $position => $encodedId) {
                $id = IdEncoder::decode((string)$encodedId);
                if ($id) {
                    Slide::where('slide_id', $id)->update(['sort_order' => $position]);
                }
            }

            return ['success' => true, 'messages' => ['Order updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * @param string[] $encodedIds One id for a single delete, several for the
     *                             "select many for delete" bulk flow -- one
     *                             code path handles both.
     */
    public function delete(array $encodedIds): array
    {
        try {
            if (!AuthService::isAdmin()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $ids = array_filter(array_map(fn($e) => IdEncoder::decode((string)$e), $encodedIds));
            if (empty($ids)) {
                throw new \Exception('No slides selected.');
            }

            $slides = Slide::whereIn('slide_id', $ids)->get();
            if ($slides->isEmpty()) {
                throw new \Exception('Slide(s) not found.');
            }

            $uploadsDir = realpath(__DIR__ . '/../../public/images/uploads/slideshow');

            foreach ($slides as $slide) {
                $slide->delete();

                // Only ever unlink a file that lives under the uploads
                // folder -- the permanent defaults under images/home/ are
                // never deleted even if an admin removes them from rotation.
                if ($uploadsDir && str_starts_with($slide->filename, 'images/uploads/slideshow/')) {
                    $path = realpath(__DIR__ . '/../../public/' . $slide->filename);
                    if ($path && str_starts_with($path, $uploadsDir) && file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            static::logActivity(count($slides) . ' slideshow image(s) removed', 'Slideshow');

            return ['success' => true, 'messages' => [count($slides) . ' image(s) removed.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
