<?php

declare(strict_types=1);

namespace App\Services\Files\Support;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileStr
{
    /**
     * Generates a unique file name for the uploaded file.
     *
     * @param  UploadedFile $file the uploaded file
     * @return string       the unique file name
     */
    public static function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $sanitizedFileName = preg_replace('/[^\w\-.]+/', '_', $file->getClientOriginalName());
        $filename = pathinfo(substr($sanitizedFileName, 0, 200), \PATHINFO_FILENAME);

        return $filename . '_' . time() . '_' . uniqid() . '.' . $extension;
    }
}
