<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Contracts\StorageProviderInterface;
use Illuminate\Support\Str;

class MockStorageProvider implements StorageProviderInterface
{
    public function createFolder(string $name, ?string $parentId = null): string
    {
        return 'mock_folder_' . Str::random(12);
    }

    public function uploadFile(string $localPath, string $filename, ?string $folderId = null, ?string $mimeType = null): array
    {
        return [
            'id'       => 'mock_file_' . Str::random(12),
            'name'     => $filename,
            'mimeType' => $mimeType ?? 'application/octet-stream',
            'size'     => file_exists($localPath) ? filesize($localPath) : 0,
            'webViewLink' => 'https://drive.google.com/file/d/mock/view',
        ];
    }

    public function listFiles(string $folderId): array
    {
        return [];
    }

    public function getFile(string $fileId): ?array
    {
        return ['id' => $fileId, 'name' => 'mock_file.mp4'];
    }

    public function getShareLink(string $fileId): string
    {
        return "https://drive.google.com/file/d/{$fileId}/view";
    }

    public function deleteFile(string $fileId): bool
    {
        return true;
    }

    public function downloadFile(string $fileId, string $localPath): bool
    {
        return true;
    }
}