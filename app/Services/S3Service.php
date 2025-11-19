<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    protected string $disk = 's3';

    /**
     * Upload an uploaded file to S3 and return public URL.
     *
     * @param UploadedFile $file
     * @param string|null $folder
     * @param string $visibility 'public'|'private'
     * @return array ['url' => string, 'path' => string]  // path = key in bucket
     */
    public function upload(UploadedFile $file, ?string $folder = null, string $visibility = 'public'): array
    {
        $folder = $folder ? trim($folder, '/') . '/' : '';
        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $filename = Str::uuid() . '.' . $ext;
        $key = $folder . $filename;

        $stream = fopen($file->getRealPath(), 'r+');
        Storage::disk($this->disk)->put($key, $stream, ['visibility' => $visibility]);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $url = Storage::disk($this->disk)->url($key);

        return ['url' => $url, 'path' => $key];
    }

    /**
     * Upload from base64 string.
     *
     * @param string $base64
     * @param string|null $folder
     * @param string|null $filenameWithExt
     * @param string $visibility
     * @return array ['url','path']
     */
    public function uploadBase64(string $base64, ?string $folder = null, ?string $filenameWithExt = null, string $visibility = 'public'): array
    {
        if (preg_match('/^data:(.*?);base64,/', $base64, $m)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $mime = $m[1];
            $ext = explode('/', $mime)[1] ?? 'png';
        } else {
            $ext = pathinfo($filenameWithExt ?? '', PATHINFO_EXTENSION) ?: 'png';
        }

        $data = base64_decode($base64);
        $folder = $folder ? trim($folder, '/') . '/' : '';
        $filename = $filenameWithExt ?? (Str::uuid() . '.' . $ext);
        $key = $folder . $filename;

        Storage::disk($this->disk)->put($key, $data, ['visibility' => $visibility]);
        $url = Storage::disk($this->disk)->url($key);

        return ['url' => $url, 'path' => $key];
    }

    /**
     * Delete file by key (path).
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Get public url for a key.
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Get temporary signed url (for private files).
     */
    public function temporaryUrl(string $path, int $minutes = 60): string
    {
        return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes($minutes));
    }
}
