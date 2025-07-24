<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileUploadHelper
{
    /**
     * Simpan file ke storage/public/{folder} dan return URL-nya.
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string URL ke file yang disimpan
     */
    public static function upload(UploadedFile $file, string $folder): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = uniqid() . '.' . $extension;
        $path = $file->storeAs($folder, $filename, 'public');
        return Storage::url($path); // hasil: /storage/{folder}/{filename}
    }

    /**
     * Hapus file berdasarkan URL `/storage/...`
     *
     * @param string $fileUrl
     * @return void
     */
    public static function delete(string $fileUrl): void
    {
        $relativePath = str_replace('/storage/', '', $fileUrl);
        Storage::disk('public')->delete($relativePath);
    }
}
