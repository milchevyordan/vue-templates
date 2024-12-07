<?php

declare(strict_types=1);

namespace App\Services\Files;

use App\Models\File;
use App\Models\Image;
use App\Services\Files\Support\FileStr;
use App\Services\Images\Compressor\Compressor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Class UploadHelper.
 *
 * This class provides file uploading functionalities including image compression.
 */
class UploadHelper
{
    /**
     * @var string
     */
    private string $directory = '';

    private string $visibility = 'public';

    /**
     * Uploads multiple files to the specified directory.
     *
     * @param  null|array            $request
     * @param  string                $requestKey
     * @return null|Collection<File> An array of uploaded files, where each element contains the following information:
     *                               - 'originalName': The original name of the file.
     *                               - 'uniqueName': A unique name generated for the file.
     *                               - 'path': The path of the uploaded file.
     */
    public static function uploadMultipleFiles(null|array $request, string $requestKey): null|Collection
    {
        $self = new self();

        if (! isset($request[$requestKey])) {
            return null;
        }

        $files = $request[$requestKey];

        $uploadedFiles = new Collection();

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $uniqueName = FileStr::generateUniqueFileName($file);
            $path = $file->storeAs($self->directory, $uniqueName, $self->visibility);

            $uploadedFiles->push(
                new File([
                    'original_name' => $file->getClientOriginalName(),
                    'unique_name'   => $uniqueName,
                    'path'          => $path,
                    'size'          => $file->getSize(),
                ])
            );
        }

        return $uploadedFiles;
    }

    /**
     * Uploads multiple images to the specified directory.
     *
     * @param  null|array             $request
     * @param  string                 $requestKey
     * @param  ?Compressor            $compressor
     * @return null|Collection<Image>
     */
    public static function uploadMultipleImages(null|array $request, string $requestKey, ?Compressor $compressor = new Compressor()): null|Collection
    {
        $self = new self();

        if (! isset($request[$requestKey])) {
            return null;
        }

        $images = $request[$requestKey];

        $uploadedImages = new Collection();

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            if ($compressor) {
                $image = $compressor->compressAndResizeImage($image);
            }

            $uniqueName = FileStr::generateUniqueFileName($image);
            $path = $image->storeAs($self->directory, $uniqueName, $self->visibility);

            $uploadedImages->push(
                new Image([
                    'original_name' => $image->getClientOriginalName(),
                    'unique_name'   => $uniqueName,
                    'path'          => $path,
                    'size'          => $image->getSize(),
                ])
            );
        }

        return $uploadedImages;
    }

    /**
     * Save application generated file to directory.
     *
     * @param  string $fileOutput
     * @param         $originalName
     * @param  string $extention
     * @param         $dir
     * @return File
     */
    public static function uploadGeneratedFile(string $fileOutput, $originalName, string $extention, $dir = 'public'): File
    {
        $uniqueName = $originalName . '_' . time() . '_' . uniqid() . ".{$extention}";
        $path = "{$dir}/{$uniqueName}";

        Storage::put($path, $fileOutput);

        $filePath = Storage::path($path);

        $uploadedFile = new UploadedFile(
            $filePath,
            basename($filePath),
            mime_content_type($filePath),
            null
        );

        return new File([
            'original_name' => $uploadedFile->getClientOriginalName(),
            'unique_name'   => $uniqueName,
            'path'          => $uniqueName,
            'size'          => $uploadedFile->getSize(),
        ]);
    }

    /**
     * Upload and save files in one function.
     *
     * @param  Model $model
     * @param        $validatedRequest
     * @param        $fileTypes
     * @return void
     */
    public static function handleFileUploads(Model $model, $validatedRequest, $fileTypes): void
    {
        foreach ($fileTypes as $fileType) {
            $file = $validatedRequest[$fileType] ?? null;

            if ($file === null) {
                continue;
            }

            $uploadedFiles[$fileType] = self::uploadMultipleFiles($validatedRequest, $fileType);
            $model->saveWithFiles($uploadedFiles[$fileType], $fileType);
        }
    }
}
