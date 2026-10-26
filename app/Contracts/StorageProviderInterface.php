<?php

declare(strict_types=1);

namespace App\Contracts;

interface StorageProviderInterface
{
    /** Create a folder. Returns the folder ID. */
    public function createFolder(string $name, ?string $parentId = null): string;

    /** Upload a file. Returns the file ID. */
    public function uploadFile(string $localPath, string $filename, ?string $folderId = null, ?string $mimeType = null): array;

    /** List files in a folder. */
    public function listFiles(string $folderId): array;

    /** Get file metadata. */
    public function getFile(string $fileId): ?array;

    /** Get a shareable/viewable link. */
    public function getShareLink(string $fileId): string;

    /** Delete a file. */
    public function deleteFile(string $fileId): bool;

    /** Download a file to a local path. */
    public function downloadFile(string $fileId, string $localPath): bool;
}