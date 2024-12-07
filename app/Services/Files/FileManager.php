<?php

declare(strict_types=1);

namespace App\Services\Files;

use App\Models\File;
use App\Models\Image;
use App\Services\Files\Support\FileStr;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileManager
{
    /**
     * Used disk.
     *
     * @var Filesystem
     */
    private Filesystem $disk;

    /**
     * @param null|Filesystem $disk
     */
    public function __construct(?Filesystem $disk = null)
    {
        // Use injected disk or fallback to default 'public'
        $this->disk = $disk ?: Storage::disk('public');
    }

    /**
     * Get filesystem path to file.
     *
     * @param  string $filePath
     * @return string
     */
    public function getLocalFilePath(string $filePath): string
    {
        return $this->disk->path($filePath);
    }

    /**
     * Return file original name and file newly generated name as a key-value array.
     *
     * @param  string $path
     * @return array
     */
    public function getFileNameAndPath(string $path): array
    {
        return [
            'fileOriginalName' => $this->getFileOriginalName($path),
            'filePath'         => $this->getLocalFilePath($path),
        ];
    }

    /**
     * Download file for the client.
     *
     * @param  string             $path
     * @return BinaryFileResponse
     */
    public function downloadFile(string $path): BinaryFileResponse
    {
        $fileOriginalName = $this->getFileOriginalName($path);
        $filePath = $this->getLocalFilePath($path);

        return response()->download($filePath, $fileOriginalName);
    }

    /**
     * Delete file and database record by given path.
     *
     * @param  string                $path
     * @return array<string, string> an associative array of deleted file name and path
     * @throws Exception
     */
    public static function destroy(string $path): array
    {
        $file = File::where('path', $path)->first();

        if ($file) {
            return (new self())->delete($file);
        }

        throw new Exception(__('Unable to delete file.'));
    }

    /**
     * Delete file and database record by given file instance.
     *
     * @param  File|Image    $file
     * @return array<string> Deleted file
     * @throws Exception
     */
    public function delete(File|Image $file): array
    {
        if ($this->deleteFile($file->path) && $file->delete()) {
            return self::getFileNameAndPath($file->path);
        }

        throw new Exception(__('Unable to delete file.'));
    }

    /**
     * Deletes multiple files and tracks the deleted files.
     *
     * @param  array $filePaths
     * @return array
     */
    public static function deleteMultipleFiles(array $filePaths): array
    {
        $deletedFiles = [];
        foreach ($filePaths as $filePath) {
            if ((new self())->deleteFile($filePath)) {
                $deletedFiles[] = $filePath;
            }
        }

        return $deletedFiles;
    }

    /**
     * Copies a single file from source to destination.
     *
     * @param  string       $sourcePath
     * @param  string       $destinationPath
     * @return string|false
     */
    public function copyFile(string $sourcePath, string $destinationPath): string|false
    {
        return $this->disk->copy($sourcePath, $destinationPath) ? $destinationPath : false;
    }

    /**
     * Copies multiple files to a given directory.
     *
     * @param  array  $files
     * @param  string $destinationDirectory
     * @return array
     */
    public function copyMultipleFiles(array $files, string $destinationDirectory = ''): array
    {
        $copiedFiles = [];
        foreach ($files as $file) {
            $path = $this->copyFile($file['path'], $destinationDirectory . '/' . FileStr::generateUniqueFileName($file));
            if ($path) {
                $copiedFiles[] = ['original_name' => $file['original_name'], 'unique_name' => basename($path), 'path' => $path];
            }
        }

        return $copiedFiles;
    }

    /**
     * Update file order by given array with IDs in database.
     *
     * @param  array $orderArray
     * @return void
     */
    public static function updateOrder(array $orderArray): void
    {
        foreach ($orderArray as $index => $id) {
            File::where('id', $id)->update(['order' => $index]);
        }
    }

    /**
     * Private method to get the original name of the file from DB.
     *
     * @param  string      $path
     * @return null|string
     */
    private function getFileOriginalName(string $path): ?string
    {
        return File::where('path', $path)->value('original_name');
    }

    /**
     * Private method to delete a file from storage.
     *
     * @param  string $filePath
     * @return bool
     */
    private function deleteFile(string $filePath): bool
    {
        return $this->disk->delete($filePath);
    }
}
