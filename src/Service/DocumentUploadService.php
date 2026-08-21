<?php
// /src/Service/DocumentUploadService.php

declare(strict_types=1);

namespace Src\Service;

/**
 * Handles a single required-document upload (image or PDF) for the rental
 * application wizard. Unlike ImageUploadService this doesn't resize/re-encode —
 * these are scans/photos of legal documents, so the original file is kept
 * byte-for-byte, just moved into place under a generated name.
 */
class DocumentUploadService
{
    private const ALLOWED_MIME_EXTENSIONS = [
        'image/jpeg'     => 'jpg',
        'image/png'      => 'png',
        'image/webp'     => 'webp',
        'image/gif'      => 'gif',
        'application/pdf' => 'pdf',
    ];

    protected string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';

        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true) && !is_dir($this->uploadDir)) {
                throw new \RuntimeException("Failed to create upload directory: {$this->uploadDir}");
            }
        }
        if (!is_writable($this->uploadDir)) {
            throw new \RuntimeException("Upload directory is not writable: {$this->uploadDir}");
        }
    }

    /**
     * Upload a single required document.
     *
     * @param array $file A single-file $_FILES['fieldName'] entry (tmp_name/name/type/error/size)
     * @return array{success: bool, message?: string, fileName?: string, url?: string}
     */
    public function upload(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            return ['success' => false, 'message' => 'No file was uploaded.'];
        }

        // Trust the file's actual content over the client-supplied MIME type, since
        // the latter is trivially spoofable and these are sensitive legal/financial documents.
        $realMime = function_exists('finfo_open')
            ? (finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']) ?: '')
            : ($file['type'] ?? '');

        $extension = self::ALLOWED_MIME_EXTENSIONS[$realMime] ?? null;

        if ($extension === null) {
            return ['success' => false, 'message' => 'Unsupported file type. Please upload a JPG, PNG, GIF, or PDF file.'];
        }

        if ($extension === 'pdf' && !$this->looksLikePdf($file['tmp_name'])) {
            return ['success' => false, 'message' => 'That file does not look like a valid PDF.'];
        }

        $fileName = uniqid() . '-' . time() . '.' . $extension;
        $destination = $this->uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'Failed to save the uploaded file.'];
        }

        return ['success' => true, 'fileName' => $fileName];
    }

    private function looksLikePdf(string $tmpPath): bool
    {
        $handle = fopen($tmpPath, 'rb');
        if (!$handle) return false;

        $header = fread($handle, 5);
        fclose($handle);

        return $header === '%PDF-';
    }
}
