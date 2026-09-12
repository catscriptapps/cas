<?php
// /src/Controller/VenuesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Venue;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin CRUD for the public "Locations" page's venues (see Venue model).
 *
 * Adding/editing a venue's photo goes through the app's shared drag-drop
 * uploader (resources/js/modals/upload-modal.js, same one Sponsorship and
 * Slideshow use) rather than a plain file input: uploadImage() saves the
 * file and hands back a filename, and the venue form (name/address/
 * directions/sport) is a separate JSON create()/update() call that just
 * references that filename -- the same two-step shape Sponsorship uses,
 * for the same reason (the shared uploader only ever posts a raw file, with
 * no room for accompanying form fields in the same request).
 */
class VenuesController
{
    use RecentActivityLogger;

    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    private const MAX_BYTES = 8 * 1024 * 1024;

    /**
     * @return array{ball: array, ice: array}
     */
    public function getAll(): array
    {
        $venues = Venue::orderBy('sport')->orderBy('sort_order')->orderBy('venue_id')->get();

        $format = fn($v) => [
            'encoded_id' => IdEncoder::encode($v->venue_id),
            'name' => $v->name,
            'address' => $v->address,
            'directions' => $v->directions,
            'sport' => $v->sport,
            'image' => $v->image,
        ];

        return [
            'ball' => $venues->where('sport', Venue::SPORT_BALL)->values()->map($format)->all(),
            'ice' => $venues->where('sport', Venue::SPORT_ICE)->values()->map($format)->all(),
        ];
    }

    /**
     * @param array|null $file A single $_FILES-style entry (tmp_name/error/size/type)
     */
    public function uploadImage(?array $file): array
    {
        if (!AuthService::isAdmin()) {
            return ['success' => false, 'message' => "You don't have permission to do that."];
        }

        try {
            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                throw new \Exception('No image was received.');
            }
            if ($file['size'] > self::MAX_BYTES) {
                throw new \Exception('Image is too large (8MB max).');
            }

            $imageInfo = @getimagesize($file['tmp_name']);
            $mime = $imageInfo['mime'] ?? null;
            if (!$mime || !isset(self::ALLOWED_MIMES[$mime])) {
                throw new \Exception('Unsupported image type. Use JPEG, PNG, GIF, or WebP.');
            }

            $uploadDir = __DIR__ . '/../../public/images/uploads/venues/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new \Exception('Could not create the upload directory.');
            }

            $filename = 'venue-' . bin2hex(random_bytes(8)) . '.' . self::ALLOWED_MIMES[$mime];
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                throw new \Exception('Failed to save the uploaded image.');
            }

            return [
                'success' => true,
                'files' => [['url' => 'images/uploads/venues/' . $filename, 'filename' => $filename]],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function create(array $data): array
    {
        try {
            if (!AuthService::isAdmin()) {
                throw new \Exception("You don't have permission to do that.");
            }

            [$name, $address, $directions, $sport] = $this->validateFields($data);
            $image = $this->resolveUploadedFilename($data);

            $maxOrder = (int)(Venue::where('sport', $sport)->max('sort_order') ?? -1);
            $venue = Venue::create([
                'name' => $name,
                'address' => $address,
                'directions' => $directions,
                'sport' => $sport,
                'image' => $image,
                'sort_order' => $maxOrder + 1,
                'date_created' => date('Y-m-d'),
            ]);

            static::logActivity("Added venue \"{$name}\"", 'Locations');

            return ['success' => true, 'venue' => $this->present($venue), 'messages' => ['Venue added.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function update(string $encodedId, array $data): array
    {
        try {
            if (!AuthService::isAdmin()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $venue = $this->findOrFail($encodedId);
            [$name, $address, $directions, $sport] = $this->validateFields($data);

            $oldImagePath = $venue->image;
            $oldSport = $venue->sport;

            $newFilename = $this->resolveUploadedFilename($data);
            if ($newFilename) {
                $venue->image = $newFilename;
            } elseif (!empty($data['remove_image'])) {
                $venue->image = null;
            }

            $venue->name = $name;
            $venue->address = $address;
            $venue->directions = $directions;
            $venue->sport = $sport;

            // Moving a venue to the other sport group puts it at the end of
            // that group's order, rather than keeping a sort_order value
            // that was only ever meaningful within its old group.
            if ($sport !== $oldSport) {
                $venue->sort_order = (int)(Venue::where('sport', $sport)->max('sort_order') ?? -1) + 1;
            }

            $venue->save();

            // Only remove the old file once the new one is safely saved (or
            // explicitly cleared), and only if it's actually being replaced.
            if ($oldImagePath && $oldImagePath !== $venue->image) {
                $this->deleteImageFile($oldImagePath);
            }

            static::logActivity("Updated venue \"{$name}\"", 'Locations');

            return ['success' => true, 'venue' => $this->present($venue), 'messages' => ['Venue updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function delete(string $encodedId): array
    {
        try {
            if (!AuthService::isAdmin()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $venue = $this->findOrFail($encodedId);
            $name = $venue->name;
            $image = $venue->image;
            $venue->delete();

            if ($image) {
                $this->deleteImageFile($image);
            }

            static::logActivity("Removed venue \"{$name}\"", 'Locations');

            return ['success' => true, 'messages' => ['Venue removed.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * @param string[] $encodedIds Ordered ids within ONE sport group -- the
     *                             two groups reorder independently of each
     *                             other, so this never needs to know about
     *                             the other group's rows at all.
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
                    Venue::where('venue_id', $id)->update(['sort_order' => $position]);
                }
            }

            return ['success' => true, 'messages' => ['Order updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    private function present(Venue $v): array
    {
        return [
            'encoded_id' => IdEncoder::encode($v->venue_id),
            'name' => $v->name,
            'address' => $v->address,
            'directions' => $v->directions,
            'sport' => $v->sport,
            'image' => $v->image,
        ];
    }

    private function findOrFail(string $encodedId): Venue
    {
        $id = IdEncoder::decode($encodedId);
        $venue = $id ? Venue::find($id) : null;
        if (!$venue) {
            throw new \Exception('Venue not found.');
        }
        return $venue;
    }

    /**
     * @return array{0: string, 1: ?string, 2: ?string, 3: string} [name, address, directions, sport]
     */
    private function validateFields(array $data): array
    {
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            throw new \Exception('Venue name is required.');
        }

        $sport = (string)($data['sport'] ?? '');
        if (!in_array($sport, [Venue::SPORT_BALL, Venue::SPORT_ICE], true)) {
            throw new \Exception('Choose Ball Hockey or Ice Hockey.');
        }

        $address = trim((string)($data['address'] ?? ''));
        $directions = trim((string)($data['directions'] ?? ''));

        return [$name, $address !== '' ? $address : null, $directions !== '' ? $directions : null, $sport];
    }

    /**
     * `filename` in the request body is whatever uploadImage() already
     * returned for a freshly-picked photo -- basename() strips any path
     * component a tampered request might try to sneak in, and the
     * file-exists check confirms it's genuinely something uploadImage()
     * just placed there, not an arbitrary filename grabbing an unrelated
     * existing file (or one under a completely different folder).
     */
    private function resolveUploadedFilename(array $data): ?string
    {
        $filename = trim((string)($data['filename'] ?? ''));
        if ($filename === '') {
            return null;
        }

        $basename = basename($filename);
        $uploadDir = realpath(__DIR__ . '/../../public/images/uploads/venues');
        if (!$uploadDir || !file_exists($uploadDir . '/' . $basename)) {
            throw new \Exception('Upload the photo first.');
        }

        return 'images/uploads/venues/' . $basename;
    }

    /**
     * Only ever unlinks a file under images/uploads/venues/ -- the original
     * seeded photos live under images/locations/ (permanent, not admin
     * uploads) and are never deleted even if a venue referencing one is
     * edited or removed.
     */
    private function deleteImageFile(string $relativePath): void
    {
        if (!str_starts_with($relativePath, 'images/uploads/venues/')) {
            return;
        }

        $uploadsDir = realpath(__DIR__ . '/../../public/images/uploads/venues');
        if (!$uploadsDir) {
            return;
        }

        $path = realpath(__DIR__ . '/../../public/' . $relativePath);
        if ($path && str_starts_with($path, $uploadsDir) && file_exists($path)) {
            @unlink($path);
        }
    }
}
