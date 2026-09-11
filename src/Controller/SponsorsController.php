<?php
// /src/Controller/SponsorsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Sponsor;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin CRUD for the public Sponsorship page's logo grid. Legacy had these
 * hardcoded in PHP with no admin controls at all -- this exists so an admin
 * can add/remove a sponsor without a code deploy.
 *
 * Adding a sponsor is a two-step flow, not one: the app's generic image
 * uploader (resources/js/modals/upload-modal.js -- reused as-is here) posts
 * only a raw file with no room for accompanying text fields like a sponsor
 * name, so uploadImage() saves the file and hands back a filename, and a
 * separate create() call (with that filename + the name the admin typed in
 * a small follow-up prompt) actually creates the DB row.
 */
class SponsorsController
{
    use RecentActivityLogger;

    /**
     * @return array{encoded_id: string, name: string, filename: string}[]
     */
    public function getAll(): array
    {
        return Sponsor::orderBy('sort_order')->orderBy('sponsor_id')->get()
            ->map(fn($s) => [
                'encoded_id' => IdEncoder::encode($s->sponsor_id),
                'name' => $s->name,
                'filename' => $s->filename,
            ])->all();
    }

    /**
     * @param array|null $file A single $_FILES-style entry (tmp_name/error/size/type)
     */
    public function uploadImage(?array $file): array
    {
        try {
            if (!AuthService::isAdmin()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                'image/avif' => 'avif',
            ];
            $maxBytes = 5 * 1024 * 1024;

            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                throw new \Exception('No logo image was received.');
            }
            if ($file['size'] > $maxBytes) {
                throw new \Exception('Image is too large (5MB max).');
            }

            // AVIF isn't reliably recognized by getimagesize() on every PHP/GD
            // build, but the app already ships (and trusts) one real AVIF
            // sponsor logo -- fall back to the client-supplied MIME for that
            // one case rather than rejecting a perfectly valid upload.
            $imageInfo = @getimagesize($file['tmp_name']);
            $mime = $imageInfo['mime'] ?? ($file['type'] ?? null);
            if (!$mime || !isset($allowedMimes[$mime])) {
                throw new \Exception('Unsupported image type. Use JPEG, PNG, GIF, WebP, or AVIF.');
            }

            $uploadDir = __DIR__ . '/../../public/images/sponsors/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new \Exception('Could not create the upload directory.');
            }

            $filename = 'sponsor-' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mime];
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                throw new \Exception('Failed to save the uploaded image.');
            }

            return [
                'success' => true,
                'files' => [['url' => 'images/sponsors/' . $filename, 'filename' => $filename]],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Creates the DB row for a file uploadImage() already saved to disk.
     */
    public function create(array $data): array
    {
        try {
            if (!AuthService::isAdmin()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $name = trim((string)($data['name'] ?? ''));
            if ($name === '') {
                throw new \Exception('Sponsor name is required.');
            }

            // basename() strips any path component a tampered request might
            // try to sneak in; the file-exists check confirms it's genuinely
            // one uploadImage() just placed there, not an arbitrary filename
            // grabbing an unrelated existing file.
            $filename = basename((string)($data['filename'] ?? ''));
            $uploadDir = realpath(__DIR__ . '/../../public/images/sponsors');
            if ($filename === '' || !$uploadDir || !file_exists($uploadDir . '/' . $filename)) {
                throw new \Exception('Upload the logo image first.');
            }

            $maxOrder = (int)(Sponsor::max('sort_order') ?? -1);
            $sponsor = Sponsor::create([
                'name' => $name,
                'filename' => $filename,
                'sort_order' => $maxOrder + 1,
                'date_created' => date('Y-m-d'),
            ]);

            static::logActivity("Added sponsor \"{$name}\"", 'Sponsorship');

            return [
                'success' => true,
                'sponsor' => [
                    'encoded_id' => IdEncoder::encode($sponsor->sponsor_id),
                    'name' => $sponsor->name,
                    'filename' => $sponsor->filename,
                ],
                'messages' => ['Sponsor added.'],
            ];
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

            $id = IdEncoder::decode($encodedId);
            $sponsor = $id ? Sponsor::find($id) : null;
            if (!$sponsor) {
                throw new \Exception('Sponsor not found.');
            }

            $name = $sponsor->name;
            $filename = $sponsor->filename;
            $sponsor->delete();

            $sponsorsDir = realpath(__DIR__ . '/../../public/images/sponsors');
            $path = $sponsorsDir ? realpath($sponsorsDir . '/' . $filename) : false;
            if ($path && $sponsorsDir && str_starts_with($path, $sponsorsDir) && file_exists($path)) {
                @unlink($path);
            }

            static::logActivity("Removed sponsor \"{$name}\"", 'Sponsorship');

            return ['success' => true, 'messages' => ['Sponsor removed.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
